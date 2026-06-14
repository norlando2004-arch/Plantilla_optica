<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo envío por preparar</title>
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 6mm;
            }

            html,
            body {
                background: #ffffff !important;
                color: #111827 !important;
                font-size: 10.5px !important;
                line-height: 1.18 !important;
            }

            .print-shell {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .print-card {
                max-width: 100% !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            .print-card td {
                padding-top: 4px !important;
                padding-bottom: 4px !important;
                font-size: 10px !important;
                line-height: 1.15 !important;
            }

            .print-card p {
                margin-top: 0 !important;
                margin-bottom: 2px !important;
                font-size: 10px !important;
                line-height: 1.15 !important;
            }

            .print-card h1 {
                margin: 0 0 2px 0 !important;
                font-size: 16px !important;
                line-height: 1.05 !important;
            }

            .print-logo {
                width: 70px !important;
                height: auto !important;
            }

            .print-big-number {
                font-size: 15px !important;
                line-height: 1.05 !important;
            }

            .print-hide {
                display: none !important;
            }
        }
    </style>
</head>
<body style="margin:0; padding:0; background:#f1f4f7; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
@php
    $meta = is_array($pago->meta) ? $pago->meta : [];
    $metaCarrito = is_array($pago->carrito?->meta) ? $pago->carrito->meta : [];
    $guest = is_array($meta['guest'] ?? null) ? $meta['guest'] : [];
    $buyer = is_array($meta['cliente'] ?? null) ? $meta['cliente'] : (is_array($metaCarrito['cliente'] ?? null) ? $metaCarrito['cliente'] : $guest);
    $lentes = is_array($meta['lentes'] ?? null) ? $meta['lentes'] : (is_array($metaCarrito['lentes'] ?? null) ? $metaCarrito['lentes'] : []);
    $frame = is_array($meta['montura'] ?? null) ? $meta['montura'] : (is_array($metaCarrito['montura'] ?? null) ? $metaCarrito['montura'] : []);
    $frameFeatures = is_array($frame['caracteristicas'] ?? null) ? $frame['caracteristicas'] : [];
    $frameMeasures = is_array($frameFeatures['medidas'] ?? null) ? $frameFeatures['medidas'] : [];
    $prescriptionId = (int) ($meta['prescription_id'] ?? $metaCarrito['prescription_id'] ?? 0);
    $textFallback = 'No especificado';

    $boolToText = static function ($value, string $default = 'No especificado'): string {
        if ($value === null || $value === '') {
            return $default;
        }

        return (bool) $value ? 'Sí' : 'No';
    };

    $formatCm = static function ($value): ?string {
        if ($value === null || $value === '') {
            return null;
        }

        return rtrim(rtrim(number_format((float) $value, 1, '.', ''), '0'), '.') . ' cm';
    };

    $nombres = trim((string) ($buyer['nombres'] ?? ($buyer['nombre'] ?? '')));
    $apellidos = trim((string) ($buyer['apellidos'] ?? ''));
    $clienteNombre = trim($nombres . ' ' . $apellidos);
    if ($clienteNombre === '') {
        $clienteNombre = 'Cliente';
    }

    $clienteCorreo = trim((string) ($buyer['correo'] ?? $guest['correo'] ?? ''));
    $clienteTelefono = trim((string) ($buyer['telefono'] ?? ''));
    $clienteDocumento = trim((string) ($buyer['numero_documento'] ?? ''));
    $clienteCiudad = trim((string) ($buyer['ciudad'] ?? ''));
    $clienteDireccion = trim((string) ($buyer['direccion'] ?? ''));
    $clienteNotas = trim((string) ($buyer['notas'] ?? ''));

    $productTitle = (string) ($frame['nombre'] ?? 'Montura solicitada');
    $frameColor = trim((string) ($frame['color'] ?? ''));
    $frameMaterial = trim((string) ($frame['material_montura'] ?? ''));
    $recomendadoPara = trim((string) ($frameFeatures['recomendado_para'] ?? ''));
    $incluye = trim((string) ($frameFeatures['incluye'] ?? ''));
    $tipoFormula = trim((string) ($frameFeatures['tipo_formula'] ?? ''));
    $clipOn = $boolToText($frameFeatures['clip_on_compatible'] ?? null, 'No');
    $compatProgresivos = $boolToText($frameFeatures['progresivos'] ?? null, 'No');

    $frameDetails = [
        ['label' => 'Recomendado para', 'value' => $recomendadoPara !== '' ? $recomendadoPara : $textFallback],
        ['label' => 'Material', 'value' => $frameMaterial !== '' ? $frameMaterial : $textFallback],
        ['label' => 'Clip-on compatible', 'value' => $clipOn],
        ['label' => 'Ancho total de la montura', 'value' => $formatCm($frameMeasures['ancho_total_montura_cm'] ?? $frameMeasures['ancho_total_cm'] ?? null) ?? $textFallback],
        ['label' => 'Ancho del lente', 'value' => $formatCm($frameMeasures['ancho_lente_cm'] ?? null) ?? $textFallback],
        ['label' => 'Alto del lente', 'value' => $formatCm($frameMeasures['alto_lente_cm'] ?? null) ?? $textFallback],
        ['label' => 'Puente', 'value' => $formatCm($frameMeasures['puente_cm'] ?? null) ?? $textFallback],
        ['label' => 'Largo de patillas', 'value' => $formatCm($frameMeasures['largo_patillas_cm'] ?? null) ?? $textFallback],
        ['label' => 'Incluye', 'value' => $incluye !== '' ? $incluye : $textFallback],
    ];

    $lensDetails = [];
    $showLensConfiguration = false;

    if (!empty($lentes)) {
        $tipoLente = (string) ($lentes['tipo_lente_necesitas'] ?? '');
        $lensType = (string) ($lentes['lens_type'] ?? '');
        $naraLevel = (string) ($lentes['nara_level'] ?? '');
        $lensColor = trim((string) ($lentes['color'] ?? ''));
        $rxSphereMax = $lentes['rx_sphere_max'] ?? null;
        $rxCylMax = $lentes['rx_cyl_max'] ?? null;

        $lensTypeLabel = $lensType !== ''
            ? (\App\Services\Gafas\GafaLensPricing::lensTypeOptions()[$lensType] ?? $lensType)
            : $textFallback;

        $tipoLenteLabel = match ($tipoLente) {
            'con_aumento_monofocal' => 'Monofocal',
            'progresivos' => 'Progresivos',
            'bifocal' => 'Bifocal',
            'ocupacional' => 'Ocupacional',
            default => $tipoLente !== '' ? $tipoLente : $textFallback,
        };

        $naraLabel = $naraLevel !== ''
            ? (\App\Services\Gafas\GafaLensPricing::naraLevelOptions()[$naraLevel] ?? strtoupper($naraLevel))
            : $textFallback;

        $showLensConfiguration = true;

        if ($prescriptionId === 0) {
            $lensDetails = [
                ['label' => 'Diseño', 'value' => $lensTypeLabel],
            ];
        } else {
            $lensDetails = [
                ['label' => 'Tipo de lente', 'value' => $tipoLenteLabel],
                ['label' => 'Diseño', 'value' => $lensTypeLabel],
            ];

            if ($naraLevel !== '') {
                $lensDetails[] = ['label' => 'Nivel NARA', 'value' => $naraLabel];
            }

            if ($lensColor !== '') {
                $lensDetails[] = ['label' => 'Color de lente', 'value' => $lensColor];
            }

            if ($rxSphereMax !== null || $rxCylMax !== null) {
                $lensDetails[] = ['label' => 'RX máximo', 'value' => 'Esfera ' . ($rxSphereMax ?? '—') . ' / Cilindro ' . ($rxCylMax ?? '—')];
            }
        }
    }

    $logoPath = public_path('images/optica.png');
    $logoSrc = (isset($message) && file_exists($logoPath)) ? $message->embed($logoPath) : url('/images/optica.png');

    // Variables para la sección de fórmula
    $showFormulaSection = false;
    $prescription = null;
    $prescriptionData = null;
    $sinFormulaMedicaRequerida = false;
    $adicionAmbos = '';
    $costoMontura = null;
    $costoLentes = null;
    
    if ($prescriptionId > 0) {
        $prescription = \App\Models\GafaPrescription::query()->whereKey($prescriptionId)->first();
        if ($prescription && is_array($prescription->analysis)) {
            // Verificar si es una gafa sin formula medica requerida
            $sinFormulaMedicaRequerida = (bool) ($frameFeatures['sin_formula_medica'] ?? false);
            
            if (!$sinFormulaMedicaRequerida) {
                $showFormulaSection = true;
                $analysis = (array) $prescription->analysis;
                
                // Priorizar manual_input sobre datos extraídos del PDF
                $prescriptionData = [
                    'od' => [
                        'esfera' => (string) ($analysis['manual_input']['od']['esfera'] ?? $analysis['od']['sph'] ?? ''),
                        'cilindro' => (string) ($analysis['manual_input']['od']['cilindro'] ?? $analysis['od']['cyl'] ?? ''),
                        'eje' => (string) ($analysis['manual_input']['od']['eje'] ?? $analysis['od']['axis'] ?? ''),
                        'adicion' => (string) ($analysis['manual_input']['od']['adicion'] ?? $analysis['od']['add'] ?? ''),
                    ],
                    'oi' => [
                        'esfera' => (string) ($analysis['manual_input']['oi']['esfera'] ?? $analysis['oi']['sph'] ?? ''),
                        'cilindro' => (string) ($analysis['manual_input']['oi']['cilindro'] ?? $analysis['oi']['cyl'] ?? ''),
                        'eje' => (string) ($analysis['manual_input']['oi']['eje'] ?? $analysis['oi']['axis'] ?? ''),
                        'adicion' => (string) ($analysis['manual_input']['oi']['adicion'] ?? $analysis['oi']['add'] ?? ''),
                    ],
                    'dnp' => $analysis['manual_input']['distancia_pupilar'] ?? $analysis['dp']['binocular'] ?? null,
                    'ano_nacimiento' => $analysis['manual_input']['ano_nacimiento'] ?? null,
                ];

                $adicionOd = (string) ($prescriptionData['od']['adicion'] ?? '');
                $adicionOi = (string) ($prescriptionData['oi']['adicion'] ?? '');
                $adicionAmbos = $adicionOd !== '' ? $adicionOd : $adicionOi;
            }
        }
    }
    
    // Obtener costos del meta de lentes
    if (!empty($lentes)) {
        $costoMontura = $lentes['precio_montura'] ?? null;
        $costoLentes = $lentes['precio_lentes'] ?? null;
    }
@endphp

<table role="presentation" cellpadding="0" cellspacing="0" width="100%" class="print-shell" style="background:#f1f4f7; padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" class="print-card" style="max-width:680px; background:#ffffff; border:1px solid #dde3ea; border-radius:16px; overflow:hidden;">
                <tr>
                    <td style="padding:20px 20px 8px 20px; text-align:center;">
                        <img src="{{ $logoSrc }}" alt="Optica" width="100" class="print-logo" style="display:inline-block; border:0; outline:none; text-decoration:none;">
                    </td>
                </tr>
                <tr>
                    <td style="padding:4px 20px 8px 20px; text-align:center;">
                        <h1 style="margin:0; font-size:26px; line-height:1.1; color:#101828;">Nuevo envío por preparar</h1>
                        <p class="print-hide" style="margin:8px 0 0 0; font-size:15px; color:#4b5563;">Hola, tienes un nuevo envío.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:12px 24px 0 24px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#eef4ff; border:1px solid #cad8ee; border-radius:12px;">
                            <tr>
                                <td style="padding:14px 16px; width:50%; border-right:1px solid #d9e5f5;">
                                    <p style="margin:0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#556987;">Referencia</p>
                                    <p class="print-big-number" style="margin:6px 0 0 0; font-size:24px; font-weight:800; line-height:1.1; color:#102a43;">{{ (string) $pago->referencia }}</p>
                                </td>
                                <td style="padding:14px 16px; width:50%;">
                                    <p style="margin:0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#556987;">Total pagado</p>
                                    <p class="print-big-number" style="margin:6px 0 0 0; font-size:24px; font-weight:800; line-height:1.1; color:#102a43;">{{ number_format((float) $pago->monto, 0, ',', '.') }} {{ $pago->moneda }}</p>
                                    <p style="margin:6px 0 0 0; font-size:13px; color:#556987;">Fecha: {{ optional($pago->updated_at ?? $pago->created_at)->format('Y-m-d H:i') }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 24px 0 24px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f8fafc; border:1px solid #dde3ea; border-radius:12px;">
                            <tr>
                                <td style="padding:12px 14px; border-bottom:1px solid #e6ebf1;">
                                    <p style="margin:0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#667085;">Cliente</p>
                                    <p style="margin:8px 0 0 0; font-size:17px; font-weight:700; color:#111827;">{{ $clienteNombre }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:10px 14px 14px 14px;">
                                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="padding:4px 0; font-size:14px; color:#1f2937; width:50%;"><strong>Correo:</strong> {{ $clienteCorreo !== '' ? $clienteCorreo : $textFallback }}</td>
                                            <td style="padding:4px 0; font-size:14px; color:#1f2937; width:50%;"><strong>Teléfono:</strong> {{ $clienteTelefono !== '' ? $clienteTelefono : $textFallback }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:4px 0; font-size:14px; color:#1f2937;"><strong>Documento:</strong> {{ $clienteDocumento !== '' ? $clienteDocumento : $textFallback }}</td>
                                            <td style="padding:4px 0; font-size:14px; color:#1f2937;"><strong>Ciudad:</strong> {{ $clienteCiudad !== '' ? $clienteCiudad : $textFallback }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="padding:4px 0; font-size:14px; color:#1f2937;"><strong>Dirección:</strong> {{ $clienteDireccion !== '' ? $clienteDireccion : $textFallback }}</td>
                                        </tr>
                                        @if($clienteNotas !== '')
                                            <tr class="print-hide">
                                                <td colspan="2" style="padding:4px 0 0 0; font-size:14px; color:#1f2937;"><strong>Notas:</strong> {{ $clienteNotas }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 24px 0 24px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#fcfcfd; border:1px solid #dde3ea; border-radius:12px;">
                            <tr>
                                <td style="padding:12px 14px; border-bottom:1px solid #e6ebf1;">
                                    <p style="margin:0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#667085;">Montura solicitada</p>
                                    <p style="margin:8px 0 0 0; font-size:18px; font-weight:700; color:#111827;">{{ $productTitle }}</p>
                                    @if($frameColor !== '')
                                        <p style="margin:8px 0 0 0; font-size:14px; color:#4b5563;">
                                            <strong>Color:</strong> {{ $frameColor }}
                                        </p>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:12px 14px;">
                                    <p style="margin:0 0 10px 0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#667085;">Especificaciones de la gafa</p>
                                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
                                        @foreach($frameDetails as $detail)
                                            <tr>
                                                <td style="padding:8px 10px; border:1px solid #e6ebf1; background:#f8fafc; font-size:13px; font-weight:700; color:#344054; width:42%;">{{ $detail['label'] }}</td>
                                                <td style="padding:8px 10px; border:1px solid #e6ebf1; font-size:13px; color:#111827;">{{ $detail['value'] }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0 14px 14px 14px;">
                                    <p style="margin:0 0 10px 0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#667085;">Compatibilidad</p>
                                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
                                        <tr>
                                            <td style="padding:8px 10px; border:1px solid #e6ebf1; background:#f8fafc; font-size:13px; font-weight:700; color:#344054; width:42%;">Progresivos</td>
                                            <td style="padding:8px 10px; border:1px solid #e6ebf1; font-size:13px; color:#111827;">{{ $compatProgresivos }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:8px 10px; border:1px solid #e6ebf1; background:#f8fafc; font-size:13px; font-weight:700; color:#344054;">Tipo de fórmula</td>
                                            <td style="padding:8px 10px; border:1px solid #e6ebf1; font-size:13px; color:#111827;">{{ $tipoFormula !== '' ? $tipoFormula : $textFallback }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                @if($showLensConfiguration && !empty($lensDetails))
                    <tr>
                        <td style="padding:0 20px 20px 20px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#eef7f0; border:1px solid #b9e5c2; border-radius:12px; overflow:hidden;">
                                <tr>
                                    <td style="padding:12px 14px; border-bottom:1px solid #d7eddc;">
                                        <p style="margin:0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#137a35;">Configuración de lentes solicitada</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
                                            @foreach($lensDetails as $detail)
                                                <tr>
                                                    <td style="padding:8px 10px; border:1px solid #d7eddc; background:#f7fcf8; font-size:13px; font-weight:700; color:#166534; width:42%;">{{ $detail['label'] }}</td>
                                                    <td style="padding:8px 10px; border:1px solid #d7eddc; font-size:13px; color:#14532d;">{{ $detail['value'] }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                @if($showFormulaSection && $prescriptionData)
                    <tr>
                        <td style="padding:0 24px 24px 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#fef3c7; border:1px solid #fcd34d; border-radius:12px; overflow:hidden;">
                                <tr>
                                    <td style="padding:12px 14px; border-bottom:1px solid #f0d061;">
                                        <p style="margin:0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#92400e;">Fórmula</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px;">
                                        <!-- Ojo Derecho e Izquierdo lado a lado -->
                                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:14px;">
                                            <tr>
                                                <td style="padding:0 6px 0 0; width:50%;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; overflow:hidden;">
                                                        <tr>
                                                            <td style="padding:10px 12px; border-bottom:1px solid #fde68a; background:#fef3c7;">
                                                                <p style="margin:0; font-size:12px; font-weight:700; color:#92400e;">Ojo derecho (OD)</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding:8px 12px; font-size:12px; color:#78350f;">
                                                                <p style="margin:4px 0;"><strong>Esfera:</strong> {{ (string) $prescriptionData['od']['esfera'] !== '' ? $prescriptionData['od']['esfera'] : '—' }}</p>
                                                                <p style="margin:4px 0;"><strong>Cilindro:</strong> {{ (string) $prescriptionData['od']['cilindro'] !== '' ? $prescriptionData['od']['cilindro'] : '—' }}</p>
                                                                <p style="margin:4px 0;"><strong>Eje:</strong> {{ (string) $prescriptionData['od']['eje'] !== '' ? $prescriptionData['od']['eje'] : '—' }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td style="padding:0 0 0 6px; width:50%;">
                                                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; overflow:hidden;">
                                                        <tr>
                                                            <td style="padding:10px 12px; border-bottom:1px solid #fde68a; background:#fef3c7;">
                                                                <p style="margin:0; font-size:12px; font-weight:700; color:#92400e;">Ojo izquierdo (OI)</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding:8px 12px; font-size:12px; color:#78350f;">
                                                                <p style="margin:4px 0;"><strong>Esfera:</strong> {{ (string) $prescriptionData['oi']['esfera'] !== '' ? $prescriptionData['oi']['esfera'] : '—' }}</p>
                                                                <p style="margin:4px 0;"><strong>Cilindro:</strong> {{ (string) $prescriptionData['oi']['cilindro'] !== '' ? $prescriptionData['oi']['cilindro'] : '—' }}</p>
                                                                <p style="margin:4px 0;"><strong>Eje:</strong> {{ (string) $prescriptionData['oi']['eje'] !== '' ? $prescriptionData['oi']['eje'] : '—' }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- DNP y Año de nacimiento -->
                                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse; margin-bottom:14px;">
                                            <tr>
                                                <td style="padding:8px 10px; border:1px solid #fde68a; background:#fef3c7; font-size:12px; font-weight:700; color:#92400e; width:50%; border-right:none;">Adición (ambos ojos)</td>
                                                <td style="padding:8px 10px; border:1px solid #fde68a; font-size:12px; color:#78350f; width:50%;">{{ $adicionAmbos !== '' ? $adicionAmbos : '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 10px; border:1px solid #fde68a; background:#fef3c7; font-size:12px; font-weight:700; color:#92400e; width:50%; border-right:none;">DNP</td>
                                                <td style="padding:8px 10px; border:1px solid #fde68a; font-size:12px; color:#78350f; width:50%;">{{ $prescriptionData['dnp'] !== null ? (string) $prescriptionData['dnp'] : '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 10px; border:1px solid #fde68a; background:#fef3c7; font-size:12px; font-weight:700; color:#92400e; border-right:none;">Año de nacimiento</td>
                                                <td style="padding:8px 10px; border:1px solid #fde68a; font-size:12px; color:#78350f;">{{ $prescriptionData['ano_nacimiento'] !== null ? (string) $prescriptionData['ano_nacimiento'] : '—' }}</td>
                                            </tr>
                                        </table>

                                        <!-- Desglose de costos -->
                                        @if($costoMontura !== null || $costoLentes !== null)
                                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
                                                @if($costoMontura !== null)
                                                    <tr>
                                                        <td style="padding:8px 10px; border:1px solid #fde68a; background:#fef3c7; font-size:12px; font-weight:700; color:#92400e; width:50%; border-right:none;">Montura</td>
                                                        <td style="padding:8px 10px; border:1px solid #fde68a; font-size:12px; color:#78350f; width:50%;">{{ number_format((float) $costoMontura, 0, ',', '.') }} {{ $pago->moneda }}</td>
                                                    </tr>
                                                @endif
                                                @if($costoLentes !== null)
                                                    <tr>
                                                        <td style="padding:8px 10px; border:1px solid #fde68a; background:#fef3c7; font-size:12px; font-weight:700; color:#92400e; border-right:none;">Lentes</td>
                                                        <td style="padding:8px 10px; border:1px solid #fde68a; font-size:12px; color:#78350f;">{{ number_format((float) $costoLentes, 0, ',', '.') }} {{ $pago->moneda }}</td>
                                                    </tr>
                                                @endif
                                                @if($costoMontura !== null && $costoLentes !== null)
                                                    <tr>
                                                        <td style="padding:8px 10px; border:1px solid #fde68a; background:#fef3c7; font-size:12px; font-weight:700; color:#92400e; border-right:none;">Total</td>
                                                        <td style="padding:8px 10px; border:1px solid #fde68a; font-size:12px; font-weight:700; color:#78350f;">{{ number_format(((float) $costoMontura + (float) $costoLentes), 0, ',', '.') }} {{ $pago->moneda }}</td>
                                                    </tr>
                                                @endif
                                            </table>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                @if($prescriptionId > 0)
                    <tr>
                        <td style="padding:0 24px 24px 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#fffbeb; border:1px solid #fde68a; border-radius:12px; overflow:hidden;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <p style="margin:0 0 6px 0; font-size:12px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#92400e;">Fórmula óptica</p>
                                        <p style="margin:0 0 12px 0; font-size:13px; color:#78350f;">El PDF de la fórmula viene adjunto en este correo. También puedes verlo desde el panel:</p>
                                        <a href="{{ route('dashboard.pagos.formula', ['pago' => $pago->id]) }}" style="display:inline-block; background:#d97706; color:#ffffff; font-size:13px; font-weight:700; text-decoration:none; padding:9px 18px; border-radius:8px;">Ver fórmula PDF</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
</table>
</body>
</html>
