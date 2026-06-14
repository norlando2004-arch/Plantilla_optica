<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu pedido ya fue enviado</title>
</head>
<body style="margin:0; padding:0; background:#f5f7fb; font-family:Arial, Helvetica, sans-serif; color:#14213d;">
@php($metaPago = is_array($pago->meta) ? $pago->meta : [])
@php($metaCarrito = is_array($pago->carrito?->meta) ? $pago->carrito->meta : [])
@php($guest = (array) ($metaPago['guest'] ?? $metaPago['cliente'] ?? $metaCarrito['cliente'] ?? []))
@php($nombresMeta = trim((string) ($guest['nombres'] ?? '')))
@php($apellidosMeta = trim((string) ($guest['apellidos'] ?? '')))
@php($nombreCompletoMeta = trim($nombresMeta . ' ' . $apellidosMeta))
@php($clienteNombre = $nombreCompletoMeta !== '' ? $nombreCompletoMeta : trim((string) ($guest['nombre'] ?? '')))
@php($clienteNombre = $clienteNombre !== '' ? $clienteNombre : 'Cliente')
@php($clienteCorreo = trim((string) ($guest['correo'] ?? ($metaCarrito['cliente']['correo'] ?? ($pago->carrito?->usuario?->correo ?? '')))))
@php($clienteDocumento = trim((string) ($guest['tipo_documento'] ?? '')) . ' ' . trim((string) ($guest['numero_documento'] ?? '')))
@php($clienteDocumento = trim($clienteDocumento))
@php($clienteTelefono = trim((string) ($guest['telefono'] ?? ($metaCarrito['cliente']['telefono'] ?? ''))))
@php($clienteCiudad = trim((string) ($guest['ciudad'] ?? ($metaCarrito['cliente']['ciudad'] ?? ($pago->carrito?->usuario?->ciudad ?? '')))))
@php($clienteDireccion = trim((string) ($guest['direccion'] ?? ($metaCarrito['cliente']['direccion'] ?? ($pago->carrito?->usuario?->direccion ?? '')))))
@php($seguimientoUrl = 'https://optica.com/seguimiento-pedido')

<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f5f7fb; padding:24px 10px;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:660px; background:#ffffff; border:1px solid #dbe4f2; border-radius:24px; overflow:hidden; box-shadow:0 20px 40px rgba(20, 33, 61, 0.08);">
                <tr>
                    <td style="background:linear-gradient(120deg, #0f172a 0%, #0b3a5b 55%, #0a6c83 100%); padding:20px 24px; text-align:center;">
                        <p style="margin:0; font-size:20px; font-weight:700; letter-spacing:.4px; color:#ffffff;">Optica</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px 24px 8px 24px; text-align:center;">
                        <div style="display:inline-block; background:#e8f6ef; color:#0d7a47; font-size:12px; font-weight:700; letter-spacing:.6px; border-radius:999px; padding:7px 14px; text-transform:uppercase;">Pedido enviado</div>
                        <h1 style="margin:14px 0 8px 0; font-size:34px; line-height:1.1; color:#0f172a;">Tu pedido ya va en camino</h1>
                        <p style="margin:0; font-size:16px; line-height:1.6; color:#475569;">Hola {{ $clienteNombre }}, tu pedido ya fue enviado a tu direccion.</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 24px 0 24px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f8fbff; border:1px solid #d9e6f8; border-radius:16px;">
                            <tr>
                                <td style="padding:16px 16px 14px 16px;">
                                    <p style="margin:0 0 8px 0; font-size:13px; font-weight:700; letter-spacing:.4px; color:#2563eb; text-transform:uppercase;">Detalle de envio</p>
                                    <p style="margin:0 0 6px 0; font-size:15px; color:#1e293b;"><strong>Referencia:</strong> {{ (string) $pago->referencia }}</p>
                                    <p style="margin:0 0 6px 0; font-size:15px; color:#1e293b;"><strong>Fecha de envio:</strong> {{ optional($pago->envio_marcado_en ?? now())->format('Y-m-d H:i') }}</p>
                                    <p style="margin:0; font-size:15px; color:#1e293b;"><strong>Estado:</strong> Enviado</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 24px 0 24px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px;">
                            <tr>
                                <td style="padding:16px;">
                                    <p style="margin:0 0 12px 0; font-size:14px; font-weight:700; color:#0f172a;">Datos del cliente</p>
                                    <p style="margin:0 0 6px 0; font-size:14px; color:#334155;"><strong>Nombre:</strong> {{ $clienteNombre }}</p>
                                    @if($clienteCorreo !== '')
                                        <p style="margin:0 0 6px 0; font-size:14px; color:#334155;"><strong>Correo:</strong> {{ $clienteCorreo }}</p>
                                    @endif
                                    @if($clienteDocumento !== '')
                                        <p style="margin:0 0 6px 0; font-size:14px; color:#334155;"><strong>Documento:</strong> {{ $clienteDocumento }}</p>
                                    @endif
                                    @if($clienteTelefono !== '')
                                        <p style="margin:0 0 6px 0; font-size:14px; color:#334155;"><strong>Telefono:</strong> {{ $clienteTelefono }}</p>
                                    @endif
                                    @if($clienteCiudad !== '')
                                        <p style="margin:0 0 6px 0; font-size:14px; color:#334155;"><strong>Ciudad:</strong> {{ $clienteCiudad }}</p>
                                    @endif
                                    @if($clienteDireccion !== '')
                                        <p style="margin:0; font-size:14px; color:#334155;"><strong>Direccion:</strong> {{ $clienteDireccion }}</p>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:20px 24px 0 24px; text-align:center;">
                        <p style="margin:0 0 12px 0; font-size:16px; line-height:1.6; color:#334155;">
                            Para hacer seguimiento de tu pedido, ingresa aqui:
                        </p>
                        <a href="{{ $seguimientoUrl }}" style="display:inline-block; background:linear-gradient(135deg, #0b7a75 0%, #0a9e8d 100%); color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; letter-spacing:.2px; border-radius:14px; padding:13px 22px;">
                            Ver seguimiento de pedido
                        </a>
                        <p style="margin:12px 0 0 0; font-size:12px; color:#64748b; word-break:break-all;">{{ $seguimientoUrl }}</p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:24px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f8fafc; border-radius:14px; border:1px dashed #cbd5e1;">
                            <tr>
                                <td style="padding:14px; text-align:center; font-size:13px; line-height:1.6; color:#64748b;">
                                    Gracias por confiar en Optica.<br>
                                    Estamos pendientes de que recibas tu pedido correctamente.
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
