<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de pago</title>
</head>
<body style="margin:0; padding:0; background:#f1f4f7; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
@php($metaPago = is_array($pago->meta) ? $pago->meta : [])
@php($metaCarrito = is_array($pago->carrito?->meta) ? $pago->carrito->meta : [])
@php($guest = (array) ($metaPago['guest'] ?? $metaPago['cliente'] ?? $metaCarrito['cliente'] ?? []))
@php($nombresMeta = trim((string) ($guest['nombres'] ?? '')))
@php($apellidosMeta = trim((string) ($guest['apellidos'] ?? '')))
@php($nombreCompletoMeta = trim($nombresMeta . ' ' . $apellidosMeta))
@php($clienteNombre = $nombreCompletoMeta !== '' ? $nombreCompletoMeta : trim((string) ($guest['nombre'] ?? '')))
@php($clienteNombre = $clienteNombre !== '' ? $clienteNombre : 'Cliente')
@php($clienteCedula = trim((string) ($guest['numero_documento'] ?? '')))
@php($direccionEnvio = trim((string) ($guest['direccion'] ?? '')))
@php($ciudadEnvio = trim((string) ($guest['ciudad'] ?? '')))
@php($destinoEnvio = trim($direccionEnvio . ($direccionEnvio !== '' && $ciudadEnvio !== '' ? ', ' : '') . $ciudadEnvio))
@php($logoPath = public_path('images/optica.png'))
@php($logoSrc = (isset($message) && file_exists($logoPath)) ? $message->embed($logoPath) : url('/images/optica.png'))

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
                        <p style="margin:0; font-size:26px; line-height:1.1; font-weight:700; color:#111827;">Hola, {{ $clienteNombre }}.</p>
                        <h1 style="margin:8px 0 0 0; font-size:30px; line-height:1.08; color:#101828;">Confirmación de pago exitoso</h1>
                        <p style="margin:10px 0 0 0; font-size:16px; color:#475467;">Tu pago fue confirmado. Aquí tienes el resumen de tu compra.</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:8px 24px 0 24px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#eef7f0; border:1px solid #b9e5c2; border-radius:12px;">
                            <tr>
                                <td style="padding:14px 14px 12px 14px;">
                                    <p style="margin:0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#137a35;">Pago aprobado</p>
                                    <p style="margin:8px 0 0 0; font-size:15px; color:#1f2937;"><strong>Referencia:</strong> {{ (string) $pago->referencia }}</p>
                                    <p style="margin:4px 0 0 0; font-size:15px; color:#1f2937;"><strong>Fecha:</strong> {{ optional($pago->updated_at ?? $pago->created_at)->format('Y-m-d H:i') }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 24px 0 24px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f8fafc; border:1px solid #dde3ea; border-radius:12px; margin-bottom:12px;">
                            <tr>
                                <td style="padding:12px 14px;">
                                    <p style="margin:0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#667085;">Datos de envío</p>
                                    <p style="margin:8px 0 0 0; font-size:15px; color:#1f2937;"><strong>Cédula:</strong> {{ $clienteCedula !== '' ? $clienteCedula : 'No registrada' }}</p>
                                    <p style="margin:4px 0 0 0; font-size:15px; color:#1f2937;"><strong>Envío a:</strong> {{ $destinoEnvio !== '' ? $destinoEnvio : 'No registrada' }}</p>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f8fafc; border:1px solid #dde3ea; border-radius:12px;">
                            <tr>
                                <td style="padding:12px 14px;">
                                    <p style="margin:0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#667085;">Resumen de productos</p>
                                    <ul style="margin:10px 0 0 18px; padding:0; color:#1f2937; font-size:15px; line-height:1.45;">
                                        @forelse($itemsSummary as $item)
                                            <li style="margin:0 0 6px 0;">
                                                {{ $item['nombre'] }} - cantidad: {{ $item['cantidad'] }}
                                                @if(!empty($item['color']))
                                                    , color: {{ $item['color'] }}
                                                @endif
                                            </li>
                                        @empty
                                            <li style="margin:0;">Tu pedido fue registrado correctamente.</li>
                                        @endforelse
                                    </ul>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

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
