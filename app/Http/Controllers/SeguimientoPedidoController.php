<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\PerfilCliente;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeguimientoPedidoController extends Controller
{
    public function __invoke(Request $request): View
    {
        [$trackingResults, $trackingError] = $this->resolveTrackingLookup($request);

        return view('pedidos.tracking', [
            'trackingResults' => $trackingResults,
            'trackingError' => $trackingError,
            'trackingInputCedula' => trim((string) $request->query('cedula', '')),
        ]);
    }

    private function resolveTrackingLookup(Request $request): array
    {
        $cedulaRaw = trim((string) $request->query('cedula', ''));

        if ($cedulaRaw === '') {
            return [collect(), null];
        }

        $cedula = $this->normalizeDocument($cedulaRaw);
        if ($cedula === '') {
            return [collect(), 'Escribe una cédula válida para consultar tu pedido.'];
        }

        $trackingResults = Pago::query()
            ->with(['carrito.usuario'])
            ->where('estado', 'aprobado')
            ->latest('id')
            ->limit(150)
            ->get()
            ->filter(function (Pago $pago) use ($cedula) {
                $buyer = $this->extractBuyerDetails($pago);
                $documentoGuardado = $this->normalizeDocument((string) ($buyer['numero_documento'] ?? ''));

                return $documentoGuardado !== '' && $documentoGuardado === $cedula;
            })
            ->map(function (Pago $pago) {
                $buyer = $this->extractBuyerDetails($pago);
                $estadoPagoRaw = trim((string) ($pago->estado ?: 'pendiente'));
                $envioEstadoRaw = trim((string) ($pago->envio_estado ?: 'pendiente'));
                $estaEnviado = mb_strtolower($envioEstadoRaw) === 'enviado';
                $meta = is_array($pago->meta) ? $pago->meta : [];
                $shipping = $meta['shipping_file'] ?? null;

                $shippingUrl = null;
                $shippingType = null;
                $shippingName = null;
                if ($shipping && isset($shipping['disk'], $shipping['path'])) {
                    $shippingUrl = route('pagos.shipping-file', $pago);
                    $shippingType = $shipping['type'] ?? null; // 'pdf' or 'image'
                    $shippingName = $shipping['original_name'] ?? basename($shipping['path'] ?? '');
                }

                return [
                    'referencia' => (string) $pago->referencia,
                    'cliente' => (string) ($buyer['nombre'] ?? 'Cliente'),
                    'numero_documento' => (string) ($buyer['numero_documento'] ?? ''),
                    'estado_pago' => $estadoPagoRaw !== '' ? ucfirst($estadoPagoRaw) : 'Pendiente',
                    'estado_envio' => $estaEnviado ? 'Enviado' : 'Pendiente de envío',
                    'esta_enviado' => $estaEnviado,
                    'fecha_pedido' => $this->formatDateForBogota($pago->created_at),
                    'fecha_envio' => $this->formatDateForBogota($pago->envio_marcado_en),
                    'monto' => (float) $pago->monto,
                    'moneda' => (string) ($pago->moneda ?: 'COP'),
                    'shipping_file' => $shippingUrl,
                    'shipping_file_type' => $shippingType,
                    'shipping_file_name' => $shippingName,
                ];
            })
            ->values();

        if ($trackingResults->isEmpty()) {
            return [collect(), 'No encontramos pedidos asociados a esa cédula.'];
        }

        return [$trackingResults, null];
    }

    private function extractBuyerDetails(Pago $pago): array
    {
        $meta = is_array($pago->meta) ? $pago->meta : [];
        $carritoMeta = is_array($pago->carrito?->meta) ? $pago->carrito->meta : [];

        $cliente = is_array($meta['cliente'] ?? null) ? $meta['cliente'] : [];
        $guest = is_array($meta['guest'] ?? null) ? $meta['guest'] : [];

        if (empty($cliente) && is_array($carritoMeta['cliente'] ?? null)) {
            $cliente = $carritoMeta['cliente'];
        }

        if (empty($guest) && is_array($carritoMeta['guest'] ?? null)) {
            $guest = $carritoMeta['guest'];
        }

        $usuario = $pago->carrito?->usuario;
        $perfil = $usuario
            ? PerfilCliente::query()->where('usuario_id', (int) $usuario->id)->latest('id')->first()
            : null;

        return [
            'nombre' => trim((string) ($cliente['nombre'] ?? ($guest['nombre'] ?? ($usuario?->nombre ?? 'Cliente')))),
            'numero_documento' => trim((string) ($cliente['numero_documento'] ?? ($guest['numero_documento'] ?? ($perfil?->numero_documento ?? '')))),
        ];
    }

    private function normalizeDocument(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?: '';
    }

    private function formatDateForBogota(?CarbonInterface $date): ?string
    {
        return $date?->copy()->setTimezone('America/Bogota')->format('d/m/Y h:i A');
    }
}
