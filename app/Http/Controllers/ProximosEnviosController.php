<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\PerfilCliente;
use App\Models\GafaPrescription;
use App\Mail\PedidoEnviadoMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProximosEnviosController extends Controller
{
    public function index(Request $request): View
    {
        $view = (string) $request->query('view', 'pendientes');
        $showSent = $view === 'enviadas';
        $cedula = trim((string) $request->query('cedula', ''));

        $query = Pago::query()
            ->where('estado', 'aprobado')
            ->with(['carrito.usuario', 'carrito.items.producto']);

        if ($showSent) {
            $query->where('envio_estado', 'enviado');
        } else {
            $query->where(function ($q) {
                $q->whereNull('envio_estado')->orWhere('envio_estado', '!=', 'enviado');
            });
        }

        $pagos = $query
            ->latest('id')
            ->limit($showSent ? 200 : 30)
            ->get();

        $perfilIds = $pagos
            ->map(function (Pago $p) {
                $meta = is_array($p->meta) ? $p->meta : [];
                $carritoMeta = is_array($p->carrito?->meta) ? $p->carrito->meta : [];

                return (int) ($meta['perfil_cliente_id'] ?? $carritoMeta['perfil_cliente_id'] ?? 0);
            })
            ->filter()
            ->unique()
            ->values();

        $perfiles = $perfilIds->isNotEmpty()
            ? PerfilCliente::query()
                ->whereIn('id', $perfilIds->all())
                ->get()
                ->keyBy('id')
            : collect();

        // Cargar fórmulas asociadas (para mostrar fórmula manual y PDF)
        $prescriptionIds = $pagos
            ->map(function (Pago $p) {
                $meta = is_array($p->meta) ? $p->meta : [];
                $carritoMeta = is_array($p->carrito?->meta) ? $p->carrito->meta : [];

                return (int) ($meta['prescription_id'] ?? $carritoMeta['prescription_id'] ?? 0);
            })
            ->filter()
            ->unique()
            ->values();

        $prescriptions = $prescriptionIds->isNotEmpty()
            ? GafaPrescription::query()
                ->whereIn('id', $prescriptionIds->all())
                ->get()
                ->keyBy('id')
            : collect();

        // Filtro por cédula (solo en vista enviadas)
        if ($showSent && $cedula !== '') {
            $needle = mb_strtolower($cedula);
            $pagos = $pagos->filter(function (Pago $p) use ($perfiles, $needle) {
                $meta = is_array($p->meta) ? $p->meta : [];
                $carritoMeta = is_array($p->carrito?->meta) ? $p->carrito->meta : [];
                $perfilId = (int) ($meta['perfil_cliente_id'] ?? $carritoMeta['perfil_cliente_id'] ?? 0);
                $perfil = $perfilId ? ($perfiles[$perfilId] ?? null) : null;
                $guest = is_array($meta['guest'] ?? null) ? $meta['guest'] : [];

                $numero = (string) ($perfil->numero_documento ?? ($guest['numero_documento'] ?? ''));
                return $numero !== '' && str_contains(mb_strtolower($numero), $needle);
            });
        }

        return view('dashboard.proximos_envios', [
            'pagos' => $pagos,
            'perfiles' => $perfiles,
            'prescriptions' => $prescriptions,
            'view' => $showSent ? 'enviadas' : 'pendientes',
            'cedula' => $cedula,
            'autoRefreshToken' => $this->buildAutoRefreshToken(),
        ]);
    }

    public function pulse(): JsonResponse
    {
        return response()->json([
            'token' => $this->buildAutoRefreshToken(),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function markAsSent(Request $request, Pago $pago): RedirectResponse
    {
        $userId = (int) Auth::id();
        $pago->loadMissing('carrito');
        abort_unless($pago->carrito !== null, 404);

        $pago->envio_estado = 'enviado';
        $pago->envio_marcado_por = $userId;
        $pago->envio_marcado_en = now();
        $pago->save();

        // Enviar correo al usuario sin adjuntos.
        $this->dispatchPedidoEnviadoMail($pago);

        return redirect()
            ->route('dashboard.proximos-envios')
            ->with('status', 'Pedido marcado como enviado.');
    }

    public function uploadShipping(Request $request, Pago $pago): RedirectResponse
    {
        $userId = (int) Auth::id();
        $pago->loadMissing('carrito');
        abort_unless($pago->carrito !== null, 404);

        $validated = $request->validate([
            'shipping_file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpeg,png,jpg,gif,webp'],
        ]);

        $file = $request->file('shipping_file');
        if (!$file) {
            return redirect()->back()->with('error', 'No se subió ningún archivo.');
        }

        $meta = is_array($pago->meta) ? $pago->meta : [];
        $existing = $meta['shipping_file'] ?? null;
        $newType = $file->getClientOriginalExtension() === 'pdf' ? 'pdf' : 'image';

        if ($existing && isset($existing['type']) && $existing['type'] !== $newType) {
            return redirect()->back()->with('error', 'Ya existe un archivo de otro tipo. Solo se permite uno: PDF o foto.');
        }

        $disk = 'public';
        $path = Storage::disk($disk)->putFile('shipping_files', $file);

        $meta['shipping_file'] = [
            'disk' => $disk,
            'path' => $path,
            'type' => $newType,
            'original_name' => $file->getClientOriginalName(),
            'uploaded_by' => $userId,
            'uploaded_at' => now()->toISOString(),
        ];

        $pago->meta = $meta;
        $pago->envio_estado = 'enviado';
        $pago->envio_marcado_por = $userId;
        $pago->envio_marcado_en = now();
        $pago->save();

        // Enviar correo al usuario (reutiliza la lógica existente)
        $this->dispatchPedidoEnviadoMail($pago);

        return redirect()
            ->route('dashboard.proximos-envios')
            ->with('status', 'Archivo de envío subido y correo enviado al usuario.');
    }

    public function downloadShippingFile(Pago $pago)
    {
        $meta = is_array($pago->meta) ? $pago->meta : [];
        $shipping = $meta['shipping_file'] ?? null;
        if (!$shipping || !isset($shipping['disk'], $shipping['path'])) {
            abort(404);
        }

        $disk = (string) $shipping['disk'];
        $path = (string) $shipping['path'];
        $name = trim((string) ($shipping['original_name'] ?? basename($path)));

        if (!Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        return Storage::disk($disk)->download($path, $name);
    }

    private function dispatchPedidoEnviadoMail(Pago $pago): void
    {
        // Resolver correo del usuario (similar a PagoPostApprovalService::resolveInvoiceEmail)
        $invoiceEmail = null;
        $metaPago = is_array($pago->meta) ? $pago->meta : [];

        if (isset($metaPago['guest']) && is_array($metaPago['guest'])) {
            $invoiceEmail = $metaPago['guest']['correo'] ?? null;
        }

        if (!$invoiceEmail && isset($metaPago['cliente']) && is_array($metaPago['cliente'])) {
            $invoiceEmail = $metaPago['cliente']['correo'] ?? null;
        }

        if (!$invoiceEmail) {
            $invoiceEmail = $metaPago['correo'] ?? null;
        }

        if (!$invoiceEmail) {
            $metaCarrito = is_array($pago->carrito?->meta) ? $pago->carrito->meta : [];
            if (isset($metaCarrito['cliente']) && is_array($metaCarrito['cliente'])) {
                $invoiceEmail = $metaCarrito['cliente']['correo'] ?? null;
            }
        }

        if (!$invoiceEmail) {
            $invoiceEmail = $pago->carrito?->usuario?->correo;
        }

        if (is_string($invoiceEmail)) {
            $invoiceEmail = trim($invoiceEmail);
        }

        if (is_string($invoiceEmail) && $invoiceEmail !== '' && filter_var($invoiceEmail, FILTER_VALIDATE_EMAIL)) {
            $pagoId = $pago->id;

            dispatch(function () use ($pagoId, $invoiceEmail): void {
                try {
                    $freshPago = Pago::query()->find($pagoId);
                    if (!$freshPago) {
                        return;
                    }

                    Mail::to($invoiceEmail)->send(new PedidoEnviadoMail($freshPago));
                } catch (\Throwable $e) {
                    Log::warning('No se pudo enviar correo de pedido enviado al usuario', [
                        'pago_id' => $pagoId,
                        'recipient' => $invoiceEmail,
                        'error' => $e->getMessage(),
                    ]);
                }
            })->afterResponse();
        }
    }

    private function buildAutoRefreshToken(): string
    {
        $latestApprovedId = (int) (Pago::query()
            ->where('estado', 'aprobado')
            ->max('id') ?? 0);

        return (string) $latestApprovedId;
    }
}
