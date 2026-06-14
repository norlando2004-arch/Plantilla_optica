<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Código de verificación</title>
</head>
<body style="margin:0;padding:0;background-color:#020617;font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#e5f0ff;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:radial-gradient(600px circle at 0% 0%,rgba(56,189,248,0.28),transparent 60%),radial-gradient(700px circle at 100% 100%,rgba(45,212,191,0.22),transparent 60%),#020617;padding:32px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;border-radius:20px;border:1px solid rgba(148,163,184,0.45);background-color:#020617;box-shadow:0 24px 60px rgba(15,23,42,0.9);overflow:hidden;">
                <tr>
                    <td style="padding:24px 28px 16px 28px;border-bottom:1px solid rgba(148,163,184,0.25);">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:#a5b4fc;font-weight:600;">Óptica digital</td>
                                <td align="right">
                                    <span style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:#9ca3af;">
                                        <span style="width:18px;height:18px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;background:radial-gradient(circle at 20% 0%,#e0f2fe,transparent 55%),radial-gradient(circle at 120% 120%,#22c55e,transparent 55%),#020617;border:1px solid rgba(56,189,248,0.9);">
                                            👁️
                                        </span>
                                        Panel paciente
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px 28px 8px 28px;">
                        <p style="margin:0 0 8px 0;font-size:14px;color:#e5e7eb;">Hola {{ $nombre }},</p>
                        <p style="margin:0 0 16px 0;font-size:14px;color:#9ca3af;line-height:1.6;">
                            Para finalizar la creación de tu cuenta en <strong style="color:#e5e7eb;">Óptica digital</strong>, ingresa el siguiente código de verificación en la ventana que tienes abierta.
                        </p>
                        <div style="margin:20px 0 16px 0;text-align:center;">
                            <div style="display:inline-block;padding:12px 26px;border-radius:999px;background:rgba(15,23,42,0.9);border:1px solid rgba(56,189,248,0.6);box-shadow:0 0 0 1px rgba(15,23,42,0.9);">
                                <span style="font-size:26px;letter-spacing:0.35em;font-weight:600;color:#e5f0ff;">
                                    @php
                                        $chunks = str_split($code, 3);
                                    @endphp
                                    {{ implode(' ', $chunks) }}
                                </span>
                            </div>
                        </div>
                        <p style="margin:0 0 10px 0;font-size:12px;color:#9ca3af;line-height:1.6;">
                            Este código es válido por <strong>15 minutos</strong>. Si no solicitaste crear una cuenta, puedes ignorar este correo.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 28px 22px 28px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="font-size:11px;color:#6b7280;">
                            <tr>
                                <td>
                                    <p style="margin:0 0 4px 0;">Gracias por confiar en nosotros para cuidar tu visión.</p>
                                    <p style="margin:0;">Equipo de Óptica digital</p>
                                </td>
                                <td align="right" style="white-space:nowrap;">
                                    <span style="display:inline-flex;gap:4px;">
                                        <span style="width:6px;height:6px;border-radius:999px;background:linear-gradient(to bottom right,#22d3ee,#38bdf8);"></span>
                                        <span style="width:6px;height:6px;border-radius:999px;background:linear-gradient(to bottom right,#22c55e,#4ade80);"></span>
                                        <span style="width:6px;height:6px;border-radius:999px;background:linear-gradient(to bottom right,#a855f7,#ec4899);"></span>
                                    </span>
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
