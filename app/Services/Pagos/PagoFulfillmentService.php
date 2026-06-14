<?php

namespace App\Services\Pagos;

use App\Models\Pago;
use App\Models\PerfilCliente;
use App\Models\Usuario;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PagoFulfillmentService
{
    /**
     * Idempotente: si ya fue procesado, no duplica.
     * Si $login=true y existe/crea usuario, lo autentica.
     * 
     * CRITICAL: Este método DEBE completarse exitosamente. Si falla, el pago queda en estado inconsistente.
     */
    public function handleApproved(Pago $pago, bool $login = false): ?Usuario
    {
        if ($pago->estado !== 'aprobado') {
            Log::info('PagoFulfillmentService::handleApproved skipped - pago no aprobado', [
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'estado' => $pago->estado,
            ]);
            return null;
        }

        Log::info('PagoFulfillmentService::handleApproved iniciando', [
            'pago_id' => $pago->id,
            'ref' => $pago->referencia,
            'login' => $login,
        ]);

        $this->resetAbortedTransactionStateIfAny();

        try {
            $result = $this->handleApprovedCore($pago, $login);
            Log::info('PagoFulfillmentService::handleApproved completado exitosamente', [
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'usuario_id' => $result?->id,
            ]);
            return $result;
        } catch (QueryException $e) {
            // En Neon/pooler (transaction pooling), a veces la conexión queda en estado "aborted" si hubo un error
            // previo y luego se intenta ejecutar otro query. Reintentamos una vez con una reconexión limpia.
            $msg = (string) $e->getMessage();
            if (str_contains($msg, 'SQLSTATE[25P02]')) {
                Log::warning('PagoFulfillmentService: BD en estado aborted (25P02), intentando recuperación', [
                    'pago_id' => $pago->id,
                    'ref' => $pago->referencia,
                    'error' => $msg,
                ]);

                try {
                    DB::purge();
                    DB::reconnect();
                } catch (\Throwable) {
                    // noop
                }

                $this->resetAbortedTransactionStateIfAny();

                try {
                    $result = $this->handleApprovedCore($pago, $login);
                    Log::info('PagoFulfillmentService::handleApproved recuperado después de 25P02', [
                        'pago_id' => $pago->id,
                        'ref' => $pago->referencia,
                    ]);
                    return $result;
                } catch (\Throwable $retryError) {
                    Log::error('PagoFulfillmentService: Falló incluso después de recuperar 25P02', [
                        'pago_id' => $pago->id,
                        'ref' => $pago->referencia,
                        'error' => $retryError->getMessage(),
                    ]);
                    throw $retryError;
                }
            }

            Log::error('PagoFulfillmentService::handleApproved falló con excepción', [
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            throw $e;
        } catch (\Throwable $e) {
            Log::error('PagoFulfillmentService::handleApproved falló con error inesperado', [
                'pago_id' => $pago->id,
                'ref' => $pago->referencia,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function resetAbortedTransactionStateIfAny(): void
    {
        // En algunos poolers, un backend puede quedar en estado "aborted" y eso rompe el siguiente query
        // con 25P02. Un ROLLBACK fuera de transacción es seguro (Postgres lo acepta) y limpia el estado.
        try {
            if (DB::transactionLevel() === 0) {
                DB::unprepared('ROLLBACK');
            }
        } catch (\Throwable) {
            // noop
        }
    }

    private function handleApprovedCore(Pago $pago, bool $login): ?Usuario
    {
        /** @var Pago $pago */
        $pago->refresh();
        $pago->loadMissing('carrito');

            $meta = is_array($pago->meta) ? $pago->meta : [];
            $fulfillment = is_array($meta['fulfillment'] ?? null) ? $meta['fulfillment'] : [];

            if (!empty($fulfillment['completed_at'])) {
                $userId = (int) ($meta['usuario_id'] ?? 0);
                $user = $userId ? Usuario::query()->find($userId) : null;

                if ($login && $user && !Auth::check()) {
                    Auth::login($user);
                    try {
                        $req = request();
                        if ($req && method_exists($req, 'hasSession') && $req->hasSession()) {
                            $req->session()->regenerate();
                        }
                    } catch (\Throwable) {
                        // noop
                    }
                }

                return $user;
            }

            $carrito = $pago->carrito;

            $usuarioId = (int) ($meta['usuario_id'] ?? 0);
            $perfilId = (int) ($meta['perfil_cliente_id'] ?? 0);

            $guest = is_array($meta['guest'] ?? null) ? $meta['guest'] : [];

            if (!$usuarioId && $carrito?->usuario_id) {
                $usuarioId = (int) $carrito->usuario_id;
            }

            $usuario = $usuarioId ? Usuario::query()->find($usuarioId) : null;

            if (!$usuario && !empty($guest['correo'])) {
                $usuario = Usuario::query()->where('correo', (string) $guest['correo'])->first();
            }

            if (!$usuario) {
                $randomPassword = Str::random(32);

                $usuario = Usuario::query()->create([
                    'rol_id' => 1,
                    'nombre' => (string) ($guest['nombre'] ?? 'Cliente'),
                    'correo' => (string) ($guest['correo'] ?? (Str::ulid()->toBase32().'@example.invalid')),
                    'contrasena' => $randomPassword,
                    'rol' => 'cliente',
                    'esta_activo' => true,
                ]);
            }

            if (!$perfilId) {
                $perfil = PerfilCliente::query()->create([
                    'usuario_id' => $usuario->id,
                    'tipo_documento' => $guest['tipo_documento'] ?? null,
                    'numero_documento' => $guest['numero_documento'] ?? null,
                    'telefono' => $guest['telefono'] ?? null,
                    'fecha_nacimiento' => $guest['fecha_nacimiento'] ?? null,
                    'genero' => $guest['genero'] ?? null,
                    'direccion' => $guest['direccion'] ?? null,
                    'ciudad' => $guest['ciudad'] ?? null,
                    'notas' => $guest['notas'] ?? null,
                    'preferencias' => null,
                ]);

                $perfilId = (int) $perfil->id;
            }

            if ($carrito && !$carrito->usuario_id) {
                $carritoMeta = is_array($carrito->meta) ? $carrito->meta : [];
                $carritoMeta['perfil_cliente_id'] = $carritoMeta['perfil_cliente_id'] ?? $perfilId;

                $carrito->update([
                    'usuario_id' => $usuario->id,
                    'meta' => $carritoMeta,
                ]);
            }

            $meta['usuario_id'] = $usuario->id;
            $meta['perfil_cliente_id'] = $meta['perfil_cliente_id'] ?? $perfilId;
            $meta['fulfillment'] = array_merge($fulfillment, [
                'completed_at' => now()->toISOString(),
            ]);

            $pago->update([
                'meta' => $meta,
            ]);

            if ($login && !Auth::check()) {
                Auth::login($usuario);
                try {
                    $req = request();
                    if ($req && method_exists($req, 'hasSession') && $req->hasSession()) {
                        $req->session()->regenerate();
                    }
                } catch (\Throwable) {
                    // noop
                }
            }

        return $usuario;
    }
}
