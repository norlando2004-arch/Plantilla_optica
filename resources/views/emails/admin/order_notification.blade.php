<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo pago aprobado</title>
</head>
<body style="margin:0; padding:0; background:#f1f4f7; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
@php($metaPago = is_array($pago->meta) ? $pago->meta : [])
@php($metaCarrito = is_array($pago->carrito?->meta) ? $pago->carrito->meta : [])
@php($guest = (array) ($metaPago['guest'] ?? $metaPago['cliente'] ?? $metaCarrito['cliente'] ?? []))
@php($clienteNombre = $pago->carrito?->usuario?->nombre ?? ($guest['nombre'] ?? 'Cliente'))
@php($logoPath = public_path('images/optica.png'))
@php($logoSrc = (isset($message) && file_exists($logoPath)) ? $message->embed($logoPath) : url('/images/optica.png'))
@php($lowStockColors = collect($itemsSummary)->filter(fn ($item) => ($item['existencias_restantes'] ?? null) !== null && (int) $item['existencias_restantes'] <= 5)->map(fn ($item) => $item['color'] ?? $item['nombre'] ?? 'Producto')->unique()->values()->all())

<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f1f4f7; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:640px; background:#ffffff; border:1px solid #dde3ea; border-radius:16px; overflow:hidden;">
                <tr>
                    <td style="padding:24px 24px 10px 24px; text-align:center;">
                        <img src="{{ $logoSrc }}" alt="Optica" width="110" style="display:inline-block; border:0; outline:none; text-decoration:none;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 24px 10px 24px; text-align:center;">
                        <p style="margin:0; font-size:28px; line-height:1.1; font-weight:700; color:#111827;">Hola, {{ $clienteNombre }}.</p>
                        <h1 style="margin:8px 0 0 0; font-size:34px; line-height:1.05; color:#101828;">Nuevo pago aprobado</h1>
                        <p style="margin:10px 0 0 0; font-size:16px; color:#475467;">Resumen simple de la compra confirmada en Optica.</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:8px 24px 0 24px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#eef7f0; border:1px solid #b9e5c2; border-radius:12px;">
                            <tr>
                                <td style="padding:14px 14px 12px 14px;">
                                    <p style="margin:0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#137a35;">Venta aprobada</p>
                                    <p style="margin:8px 0 0 0; font-size:15px; color:#1f2937;"><strong>Referencia:</strong> {{ (string) $pago->referencia }}</p>
                                    <p style="margin:4px 0 0 0; font-size:15px; color:#1f2937;"><strong>Monto:</strong> {{ number_format((float) $pago->monto, 0, ',', '.') }} {{ $pago->moneda }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 24px 0 24px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f8fafc; border:1px solid #dde3ea; border-radius:12px;">
                            <tr>
                                <td style="padding:12px 14px 10px 14px; border-bottom:1px solid #e6ebf1;">
                                    <p style="margin:0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#667085;">Cliente</p>
                                    <p style="margin:8px 0 0 0; font-size:16px; color:#111827;">{{ $clienteNombre }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 14px;">
                                    <p style="margin:0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#667085;">Resumen de productos</p>
                                    <ul style="margin:10px 0 0 18px; padding:0; color:#1f2937; font-size:15px; line-height:1.45;">
                                        @foreach($itemsSummary as $item)
                                            <li style="margin:0 0 6px 0;">
                                                {{ $item['nombre'] }} - cantidad: {{ $item['cantidad'] }}, existencias restantes: {{ $item['existencias_restantes'] ?? 'N/D' }}
                                                @if(!empty($item['color']))
                                                    , color: {{ $item['color'] }}
                                                @endif
                                                @if($item['existencias_restantes'] !== null && $item['existencias_restantes'] <= 5)
                                                    <strong>({{ !empty($item['color']) ? 'Te quedan pocas gafas del color ' . $item['color'] : 'Inventario bajo' }})</strong>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                @if($hasLowStock)
                    <tr>
                        <td style="padding:16px 24px 0 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#fff7ed; border:1px solid #fed7aa; border-radius:12px;">
                                <tr>
                                    <td style="padding:12px 14px; font-size:14px; color:#9a3412;">
                                        <strong>Alerta de inventario:</strong> {{ $lowStockColors !== [] ? 'te quedan pocas gafas en estos colores: ' . implode(', ', $lowStockColors) . '.' : 'uno o más productos quedaron con existencias bajas (5 o menos).' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                <tr>
                    <td style="padding:16px 24px 24px 24px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#eef4ff; border:1px solid #cad8ee; border-radius:12px;">
                            <tr>
                                <td style="padding:14px;">
                                    <p style="margin:0; font-size:13px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#556987;">Total pagado</p>
                                    <p style="margin:6px 0 0 0; font-size:34px; font-weight:800; line-height:1; color:#102a43;">{{ number_format((float) $pago->monto, 0, ',', '.') }} {{ $pago->moneda }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
