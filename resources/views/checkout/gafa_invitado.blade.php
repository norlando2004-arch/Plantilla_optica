<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout — {{ $producto->nombre }} — Óptica</title>

    @php
        $viteHot = public_path('hot');
        $viteManifest = public_path('build/manifest.json');
        $hasViteAssets = file_exists($viteHot) || file_exists($viteManifest);
    @endphp

    @if($hasViteAssets)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @if(!$hasViteAssets || app()->isLocal())
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        .landing-global-loading {
            position: fixed;
            inset: 0;
            z-index: 130;
            display: none;
            align-items: center;
            justify-content: center;
            background: transparent;
            pointer-events: all;
            touch-action: none;
        }

        .landing-global-loading.is-visible {
            display: flex;
        }

        .landing-global-loading-content {
            width: min(92vw, 22rem);
            text-align: center;
        }

        .landing-global-loading-gif {
            width: 7.1rem;
            height: 7.1rem;
            object-fit: contain;
            filter: drop-shadow(0 8px 18px rgba(28, 18, 8, 0.22));
        }

        .landing-global-loading-label {
            margin-top: 0.7rem;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: 0.01em;
            color: #17382f;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .landing-global-loading-dots {
            display: inline-flex;
            align-items: flex-end;
            gap: 0.3rem;
            margin-left: 0.15rem;
        }

        .landing-global-loading-dot {
            width: 0.36rem;
            height: 0.36rem;
            border-radius: 9999px;
            background: #3f7f5f;
            opacity: 0.2;
            transform: translateY(0);
            animation: landing-loading-dot-bounce 1s infinite ease-in-out;
        }

        .landing-global-loading-dot:nth-child(2) {
            animation-delay: 0.18s;
        }

        .landing-global-loading-dot:nth-child(3) {
            animation-delay: 0.36s;
        }

        @keyframes landing-loading-dot-bounce {
            0%,
            80%,
            100% {
                opacity: 0.2;
                transform: translateY(0);
            }

            40% {
                opacity: 1;
                transform: translateY(-3px);
            }
        }

        @media (max-width: 640px) {
            .landing-global-loading-content {
                width: min(92vw, 18rem);
            }

            .landing-global-loading-gif {
                width: 6rem;
                height: 6rem;
            }

            .landing-global-loading-label {
                font-size: 0.98rem;
            }
        }
    </style>
</head>
<body class="bg-[linear-gradient(180deg,#eef7f8_0%,#e3f1f2_24%,#d5e9eb_60%,#f7fbfb_100%)] text-zinc-900 antialiased font-sans">
<div id="landingGlobalLoading"
     class="landing-global-loading"
     aria-hidden="true"
     role="status"
     aria-live="polite"
     data-loading-overlay>
    <div class="landing-global-loading-content">
        <img
            src="https://media0.giphy.com/media/v1.Y2lkPTZjMDliOTUya2QyMm9qaHh4NmdiajViZjZ1OTF2NHVwcXhjdXQ0aWU0cnk3N2gzYiZlcD12MV9zdGlja2Vyc19zZWFyY2gmY3Q9cw/5j5ZLtybC9q9avWqX8/giphy.gif"
            alt="Cargando"
            class="landing-global-loading-gif mx-auto"
            loading="eager"
        >
        <p class="landing-global-loading-label">
            Cargando
            <span class="landing-global-loading-dots" aria-hidden="true">
                <span class="landing-global-loading-dot"></span>
                <span class="landing-global-loading-dot"></span>
                <span class="landing-global-loading-dot"></span>
            </span>
        </p>
    </div>
</div>
@php
    $showStoreBanner = false;
@endphp
@include('partials.store-navbar')

@php
    $checkoutCaracteristicas = is_array($producto->caracteristicas) ? $producto->caracteristicas : [];
    $polyEnabled = \App\Services\Gafas\GafaLensPricing::usesPolyForCharacteristics($checkoutCaracteristicas);
    $allowedLensDesigns = \App\Services\Gafas\GafaLensPricing::allowedLensDesignsForCharacteristics($checkoutCaracteristicas);
    $naraOptions = \App\Services\Gafas\GafaLensPricing::naraLevelOptions();
    $checkoutMetaFormula = is_array($producto->meta) ? $producto->meta : [];
    $forcedNoPrescription = array_key_exists('formula_permitida', $checkoutMetaFormula)
        ? !((bool) $checkoutMetaFormula['formula_permitida'])
        : false;
    $noPrescription = $forcedNoPrescription || (bool) old('no_prescription', request('no_prescription'));
    $noFormulaSimple = (bool) old('no_formula_simple', request('no_formula_simple'));
    $planoNeutro = (bool) old('plano_neutro', request('plano_neutro'));
    $tipoLenteNecesitas = \App\Services\Gafas\GafaLensPricing::sanitizeLensDesignForCharacteristics(
        $checkoutCaracteristicas,
        (string) old('tipo_lente_necesitas', $tipo_lente_necesitas ?? request('tipo_lente_necesitas', \App\Services\Gafas\GafaLensPricing::defaultLensDesignForCharacteristics($checkoutCaracteristicas)))
    );
    $mode = $tipoLenteNecesitas === \App\Services\Gafas\GafaLensPricing::TIPO_LENTE_MONOFOCAL ? 'monofocal' : ($tipoLenteNecesitas === \App\Services\Gafas\GafaLensPricing::TIPO_LENTE_BIFOCAL ? 'bifocal' : ($tipoLenteNecesitas === \App\Services\Gafas\GafaLensPricing::TIPO_LENTE_OCUPACIONAL ? 'ocupacional' : 'progresivos'));
    $lensTypeOptions = $mode === 'monofocal' ? \App\Services\Gafas\GafaLensPricing::lensTypeOptionsForMonofocal($polyEnabled) : ($mode === 'bifocal' ? \App\Services\Gafas\GafaLensPricing::lensTypeOptionsForBifocal($polyEnabled) : ($mode === 'ocupacional' ? \App\Services\Gafas\GafaLensPricing::lensTypeOptionsForOcupacional($polyEnabled) : \App\Services\Gafas\GafaLensPricing::lensTypeOptionsForProgresivos($polyEnabled)));
    if ($polyEnabled && $mode === 'progresivos') {
        $naraOptions = array_intersect_key($naraOptions, array_flip(['basica', 'media', 'alta']));
    }
    if ($noFormulaSimple) {
        $noFormulaLabels = \App\Services\Gafas\GafaLensPricing::lensTypeLabelsForNoFormula($polyEnabled);
        foreach ($lensTypeOptions as $k => &$v) {
            if (isset($noFormulaLabels[$k])) {
                $v = $noFormulaLabels[$k];
            }
        }
        unset($v);
    }
    $lensTypeCandidate = (string) old('lens_type', request('lens_type'));
    $selectedLensType = $noPrescription ? '' : (array_key_exists($lensTypeCandidate, $lensTypeOptions) ? $lensTypeCandidate : \App\Services\Gafas\GafaLensPricing::defaultLensTypeFor($tipoLenteNecesitas));
    $selectedNaraLevel = ($noPrescription || $mode !== 'progresivos') ? '' : (array_key_exists((string) old('nara_level', request('nara_level')), $naraOptions) ? (string) old('nara_level', request('nara_level')) : 'basica');
    $selectedLensColor = $noPrescription ? '' : (string) old('lens_color', request('lens_color'));
@endphp
@php
    $checkoutMeta = $checkoutMetaFormula;
    $checkoutMedidas = is_array($checkoutCaracteristicas['medidas'] ?? null) ? $checkoutCaracteristicas['medidas'] : [];
    $checkoutGallery = array_values(array_unique(array_filter([
        trim((string) ($checkoutMeta['imagen_url'] ?? '')),
        trim((string) ($checkoutMeta['imagen_url_2'] ?? '')),
        ...array_values(array_filter(is_array($checkoutMeta['imagenes'] ?? null) ? $checkoutMeta['imagenes'] : [], static fn ($item) => is_string($item) && trim($item) !== '')),
    ], static fn ($item) => is_string($item) && $item !== '')));
    $checkoutPrimaryImg = $checkoutGallery[0] ?? '';
    $formatCheckoutCm = static function ($value): ?string {
        if ($value === null || $value === '') {
            return null;
        }

        return rtrim(rtrim(number_format((float) $value, 1, '.', ''), '0'), '.') . ' cm';
    };
    $textFallback = 'No especificado';
    $boolToText = static function (mixed $value, string $default = 'No'): string {
        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'si', 'sí', 'true'], true)) {
                return 'Sí';
            }
            if (in_array($normalized, ['0', 'no', 'false'], true)) {
                return 'No';
            }

            return trim($value) !== '' ? trim($value) : $default;
        }

        if (is_bool($value) || is_numeric($value)) {
            return (bool) $value ? 'Sí' : 'No';
        }

        return $default;
    };
    $checkoutGenero = match ((string) $producto->genero_objetivo) {
        'female' => 'Mujer',
        'male' => 'Hombre',
        'ninos' => 'Niños',
        'ninas' => 'Niñas',
        'gafas_polarizadas' => 'Polarizadas',
        'descanso' => 'Deportivas',
        'unisex' => 'Unisex',
        default => 'Óptica',
    };
    $frameColorSummary = trim((string) old('frame_color', request('frame_color', $checkoutMeta['color'] ?? ($checkoutMeta['color_nombre'] ?? ($producto->color ?? '')))));
    $recomendadoParaSummary = trim((string) ($checkoutCaracteristicas['recomendado_para'] ?? $checkoutMeta['recomendado_para'] ?? ''));
    $incluyeSummary = trim((string) ($checkoutCaracteristicas['incluye'] ?? $checkoutMeta['incluye'] ?? ''));
    $clipOnSummary = $boolToText($checkoutCaracteristicas['clip_on_compatible'] ?? $checkoutMeta['clip_on_compatible'] ?? null, 'No');
    $compatProgresivosSummary = $boolToText($checkoutCaracteristicas['progresivos'] ?? null, 'No');
    $compatTipoFormulaSummary = trim((string) ($checkoutCaracteristicas['tipo_formula'] ?? ''));
    $frameWidthSummary = $formatCheckoutCm($checkoutMedidas['ancho_total_montura_cm'] ?? null);
    $lensWidthSummary = $formatCheckoutCm($checkoutMedidas['ancho_lente_cm'] ?? null);
    $lensHeightSummary = $formatCheckoutCm($checkoutMedidas['alto_lente_cm'] ?? null);
    $bridgeSummary = $formatCheckoutCm($checkoutMedidas['puente_cm'] ?? null);
    $templeSummary = $formatCheckoutCm($checkoutMedidas['largo_patillas_cm'] ?? null);
    $lensModeLabel = $noPrescription
        ? 'Solo montura'
        : ($noFormulaSimple
            ? 'Sin fórmula'
        : ($planoNeutro
            ? 'Lente monofocal plano'
            : ($mode === 'monofocal'
                ? 'Lentes monofocales'
                : ($mode === 'bifocal'
                    ? 'Lentes bifocales'
                    : ($mode === 'ocupacional' ? 'Lentes ocupacionales' : 'Lentes progresivos digitales')))));
    $selectedLensTypeLabel = $noPrescription ? 'No aplica' : ((string) ($lensTypeOptions[$selectedLensType] ?? 'Por confirmar'));
    $selectedNaraLabel = $noPrescription || $mode !== 'progresivos'
        ? 'No aplica'
        : (string) ($naraOptions[$selectedNaraLevel] ?? 'Por confirmar');
    $selectedLensColorSummary = $noPrescription ? 'No aplica' : ($selectedLensColor !== '' ? $selectedLensColor : 'Sin color');
    $summaryTitle = trim((string) $producto->nombre) !== '' ? (string) $producto->nombre : 'Montura infantil';
    $summaryColorLabel = $frameColorSummary !== '' ? $frameColorSummary : 'Gris';
    $displayTitle = $summaryTitle . ' (' . $summaryColorLabel . ')';
    $frameSpecsParts = array_values(array_filter([
        $producto->marca ? 'Marca: ' . $producto->marca : null,
        $producto->material_montura ? 'Material: ' . $producto->material_montura : null,
        $lensWidthSummary ? 'Lente: ' . $lensWidthSummary : null,
        $bridgeSummary ? 'Puente: ' . $bridgeSummary : null,
        $templeSummary ? 'Patillas: ' . $templeSummary : null,
    ]));
    $frameSpecsSummary = count($frameSpecsParts) ? implode(' • ', $frameSpecsParts) : 'Especificaciones del modelo disponibles al momento de tu compra.';
    $checkoutSpecs = [
        ['label' => 'Recomendado para', 'value' => $recomendadoParaSummary !== '' ? $recomendadoParaSummary : $textFallback],
        ['label' => 'Material', 'value' => $producto->material_montura ?: $textFallback],
        ['label' => 'Clip-on compatible', 'value' => $clipOnSummary],
        ['label' => 'Ancho total', 'value' => $frameWidthSummary ?? $textFallback],
        ['label' => 'Ancho del lente', 'value' => $lensWidthSummary ?? $textFallback],
        ['label' => 'Alto del lente', 'value' => $lensHeightSummary ?? $textFallback],
        ['label' => 'Puente', 'value' => $bridgeSummary ?? $textFallback],
        ['label' => 'Patillas', 'value' => $templeSummary ?? $textFallback],
        ['label' => 'Incluye', 'value' => $incluyeSummary !== '' ? $incluyeSummary : $textFallback],
    ];
    $quickSpecs = [
        ['label' => 'Lente', 'value' => $lensWidthSummary ?? $textFallback],
        ['label' => 'Puente', 'value' => $bridgeSummary ?? $textFallback],
        ['label' => 'Material', 'value' => $producto->material_montura ?: $textFallback],
        ['label' => 'Patillas', 'value' => $templeSummary ?? $textFallback],
    ];
@endphp
@php($rxSphereMax = (float) ($rx_sphere_max ?? 0))
@php($rxCylMax = (float) ($rx_cyl_max ?? 0))
@php($precioBase = (float) ($producto->precio_oferta ?? $producto->precio ?? 0))
@php($precioLentes = $noPrescription ? 0 : ($planoNeutro ? \App\Services\Gafas\GafaLensPricing::noFormulaLensPrice((string) $selectedLensType, $polyEnabled) : ($mode === 'monofocal' ? \App\Services\Gafas\GafaLensPricing::monofocalLensPrice((string) $selectedLensType, $rxSphereMax, $rxCylMax, null, $polyEnabled) : ($mode === 'bifocal' ? \App\Services\Gafas\GafaLensPricing::bifocalLensPrice((string) $selectedLensType, $rxSphereMax, $rxCylMax, $polyEnabled) : ($mode === 'ocupacional' ? \App\Services\Gafas\GafaLensPricing::ocupacionalLensPrice((string) $selectedLensType, $polyEnabled) : \App\Services\Gafas\GafaLensPricing::lensPrice((string) $selectedLensType, (string) $selectedNaraLevel, $polyEnabled))))) )
@php($precioTotal = $precioBase + (float) $precioLentes)

<main class="relative mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
    <div class="pointer-events-none absolute -left-12 top-10 h-40 w-40 rounded-full bg-amber-200/40 blur-3xl"></div>
    <div class="pointer-events-none absolute right-0 top-24 h-48 w-48 rounded-full bg-sky-200/35 blur-3xl"></div>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold">Óptica</p>
            <h2 class="mt-0.5 text-lg font-semibold">Datos de envío</h2>
        </div>
        <a href="{{ route('gafas.show', ['producto' => $producto->slug]) }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-2.5 text-center text-sm font-semibold text-zinc-700 shadow-sm hover:bg-zinc-50 sm:w-auto">Volver</a>
    </div>

    @if(session('status'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
            <p class="font-semibold">Revisa los campos:</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-12 lg:items-start">
        <section class="lg:col-span-4 lg:sticky lg:top-24">
            <div id="lensBreakdown" class="overflow-hidden rounded-[2rem] bg-white/42 shadow-[0_26px_80px_-48px_rgba(15,23,42,0.45)] ring-1 ring-white/60 backdrop-blur-xl" data-currency="{{ $producto->moneda ?? 'COP' }}" data-base="{{ (float) $precioBase }}">
                <div class="bg-[linear-gradient(145deg,rgba(255,255,255,0.88),rgba(59,124,130,0.12))] px-5 py-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">Order Summary</p>
                    <p class="mt-1 text-xl font-semibold text-zinc-950">{{ $displayTitle }}</p>
                </div>

                <div class="space-y-5 px-5 pb-5">
                    <div class="rounded-[1.6rem] bg-white/70 p-4 ring-1 ring-white/80">
                        <div class="flex items-center gap-4">
                            <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200/70">
                                @if($checkoutPrimaryImg !== '')
                                    <img src="{{ $checkoutPrimaryImg }}" alt="{{ $producto->nombre }}" class="h-full w-full object-contain p-2" />
                                @else
                                    <span class="px-2 text-center text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Sin imagen</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-zinc-900">{{ $displayTitle }}</p>
                                <p class="mt-1 text-xs text-zinc-500">{{ $checkoutGenero }}{{ $producto->marca ? ' · ' . $producto->marca : '' }}</p>
                                <p class="mt-2 text-xs leading-5 text-zinc-600" data-frame-color-badge>
                                    Color: <span class="font-semibold text-zinc-900">{{ $frameColorSummary !== '' ? $frameColorSummary : 'Por confirmar' }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        @foreach($quickSpecs as $spec)
                            <div class="rounded-2xl bg-white/72 p-3 ring-1 ring-white/90">
                                <div class="mb-2 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 12h18"></path>
                                        <path d="M12 3v18"></path>
                                    </svg>
                                </div>
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">{{ $spec['label'] }}</p>
                                <p class="mt-1 text-sm font-semibold text-zinc-900">{{ $spec['value'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="rounded-[1.4rem] bg-white/76 p-4 ring-1 ring-white/90">
                        <div class="space-y-2 text-sm text-zinc-700">
                            <div class="flex items-center justify-between gap-4">
                                <span>Montura</span>
                                <span class="font-semibold text-zinc-900" data-price-base>{{ number_format((float) $precioBase, 0, ',', '.') }} {{ $producto->moneda }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <span>Lentes</span>
                                <span class="font-semibold text-zinc-900" data-price-lens>{{ number_format((float) $precioLentes, 0, ',', '.') }} {{ $producto->moneda }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-4 border-t border-zinc-200 pt-3">
                                <span class="font-semibold text-zinc-900">Total</span>
                                <span class="text-2xl font-bold text-sky-600" data-price-total>{{ number_format((float) $precioTotal, 0, ',', '.') }} {{ $producto->moneda }}</span>
                            </div>
                        </div>

                    </div>

                    <div class="hidden">
                        <span data-summary-lens-family>{{ $lensModeLabel }}</span>
                        <span data-summary-lens-type>{{ $selectedLensTypeLabel }}</span>
                        <span data-summary-nara>{{ $selectedNaraLabel }}</span>
                        <span data-summary-lens-color>{{ $selectedLensColorSummary }}</span>
                        <span data-frame-color>{{ $frameColorSummary !== '' ? $frameColorSummary : 'Por confirmar' }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="lg:col-span-8">
            <div class="rounded-[2.1rem] bg-white/92 p-4 shadow-[0_24px_70px_-40px_rgba(15,23,42,0.3)] ring-1 ring-zinc-200/80 backdrop-blur sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-zinc-200 pb-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500">Paso final</p>
                    <p class="mt-1 text-xl font-semibold text-zinc-900">Información del comprador</p>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-zinc-500">Estos datos se usarán para procesar tu pago, confirmar la compra y coordinar el envío de la montura con la configuración elegida.</p>
                </div>
                <div class="flex flex-wrap gap-2 text-xs font-semibold text-zinc-600">
                    <div class="rounded-full bg-amber-50 px-4 py-2 text-center ring-1 ring-amber-200">Pago</div>
                    <div class="rounded-full bg-sky-50 px-4 py-2 text-center ring-1 ring-sky-200">Envío</div>
                </div>
            </div>

            <form class="mt-5" action="{{ route('pagos.startGuest', ['producto' => $producto->slug]) }}" method="POST">
                @csrf

                <input type="hidden" name="no_prescription" value="{{ $noPrescription ? '1' : '0' }}" />
                <input type="hidden" name="no_formula_simple" value="{{ $noFormulaSimple ? '1' : '0' }}" />
                <input type="hidden" name="plano_neutro" value="{{ $planoNeutro ? '1' : '0' }}" />
                <input type="hidden" name="plano_rx_sphere_max" value="{{ $planoNeutro ? $rxSphereMax : '' }}" />
                <input type="hidden" name="plano_rx_cyl_max" value="{{ $planoNeutro ? $rxCylMax : '' }}" />
                <input type="hidden" name="frame_color" value="{{ e($frameColorSummary) }}" />
                @unless($noPrescription)
                    <input type="hidden" name="tipo_lente_necesitas" value="{{ e($tipoLenteNecesitas) }}" />
                    <input type="hidden" name="prescription_id" value="{{ e((string) old('prescription_id', request('prescription_id'))) }}" />
                @endunless

                 <div class="mb-6 rounded-[1.9rem] bg-[linear-gradient(180deg,#f2f8f8_0%,#ffffff_100%)] p-4 ring-1 ring-zinc-200/80 sm:p-5 {{ $noPrescription ? 'hidden' : '' }}" id="lensConfigurator"
                     data-mode="{{ $mode }}"
                     data-poly-enabled="{{ $polyEnabled ? '1' : '0' }}"
                     data-no-formula-simple="{{ $noFormulaSimple ? '1' : '0' }}"
                     data-lens-matrix='@json(\App\Services\Gafas\GafaLensPricing::matrixForCheckout($polyEnabled))'
                     data-mono-pricing='@json(\App\Services\Gafas\GafaLensPricing::monofocalClientPricing($polyEnabled))'
                     data-bifocal-pricing='@json(\App\Services\Gafas\GafaLensPricing::bifocalClientPricing($polyEnabled))'
                     data-ocupacional-pricing='@json(\App\Services\Gafas\GafaLensPricing::ocupacionalClientPricing($polyEnabled))'
                     data-rx-sphere-max="{{ $rxSphereMax }}"
                     data-rx-cyl-max="{{ $rxCylMax }}">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500">Ajuste de lentes</p>
                    <p class="mt-1 text-lg font-semibold text-zinc-900">
                        {{ $mode === 'monofocal' ? 'Lentes monofocales' : ($mode === 'bifocal' ? 'Lentes bifocales' : ($mode === 'ocupacional' ? 'Lentes ocupacionales' : 'Lentes progresivos digitales')) }}
                    </p>
                    <p class="mt-1 text-sm text-zinc-500">Esta selección se muestra para revisión, pero no se puede modificar en el checkout.</p>

                    <div class="mt-4 grid gap-4">
                        @if($mode === 'progresivos')
                        <div>
                            <p class="text-xs font-semibold text-zinc-700">Categoría NARA</p>
                            <input type="hidden" name="nara_level" value="{{ e((string) $selectedNaraLevel) }}" />
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                @foreach($naraOptions as $key => $label)
                                    <label class="flex items-center justify-between gap-3 rounded-2xl border border-zinc-200/70 bg-zinc-50 px-4 py-3 text-sm font-semibold text-zinc-800">
                                        <span class="min-w-0 truncate">{{ $label }}</span>
                                        <input type="radio" name="nara_level_locked" value="{{ $key }}" class="h-4 w-4 rounded border-zinc-300" {{ (string) $selectedNaraLevel === (string) $key ? 'checked' : '' }} data-nara-radio disabled />
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div>
                            <label class="text-xs font-semibold text-zinc-700" for="lens_type">Tipo de lente</label>
                            <input type="hidden" name="lens_type" value="{{ e((string) $selectedLensType) }}" />
                            <select id="lens_type" name="lens_type_locked" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-700 outline-none" data-lens-select disabled>
                                @foreach($lensTypeOptions as $key => $label)
                                    <option value="{{ $key }}" {{ (string) $selectedLensType === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="{{ (string) $selectedLensType === '159_transitions_gens' ? '' : 'hidden' }}" data-lens-color>
                            <label class="text-xs font-semibold text-zinc-700" for="lens_color">Color</label>
                            <input type="hidden" name="lens_color" value="{{ e((string) $selectedLensColor) }}" />
                            <select id="lens_color" name="lens_color_locked" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-700 outline-none" disabled>
                                <option value="">Selecciona</option>
                                @foreach(['Gris', 'Marrón', 'Verde Grafito', 'Verde esmeralda', 'Zafiro', 'Amatista', 'Ambar', 'Rubi'] as $color)
                                    <option value="{{ $color }}" {{ (string) $selectedLensColor === (string) $color ? 'selected' : '' }}>{{ $color }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-[1.9rem] bg-[linear-gradient(180deg,#ffffff_0%,#f1f8f8_100%)] p-4 ring-1 ring-zinc-200/80 sm:p-5">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-zinc-500">Tus datos</p>
                        <p class="mt-1 text-lg font-semibold text-zinc-900">Completa la información de entrega</p>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-zinc-700">Correo para enviar factura (opcional)</label>
                        <input type="email" name="correo" value="{{ old('correo') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-zinc-700">Nombres</label>
                        <input name="nombres" value="{{ old('nombres') }}" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-zinc-700">Apellidos</label>
                        <input name="apellidos" value="{{ old('apellidos') }}" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-zinc-700">Tipo documento</label>
                        @php($tipoDoc = old('tipo_documento'))
                        <select name="tipo_documento" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">
                            <option value="">Selecciona</option>
                            <option value="CC" {{ $tipoDoc === 'CC' ? 'selected' : '' }}>CC</option>
                            <option value="CE" {{ $tipoDoc === 'CE' ? 'selected' : '' }}>CE</option>
                            <option value="NIT" {{ $tipoDoc === 'NIT' ? 'selected' : '' }}>NIT</option>
                            <option value="PAS" {{ $tipoDoc === 'PAS' ? 'selected' : '' }}>Pasaporte</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-zinc-700">Número documento</label>
                        <input name="numero_documento" value="{{ old('numero_documento') }}" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-zinc-700">Teléfono</label>
                        <input name="telefono" value="{{ old('telefono') }}" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-zinc-700">Ciudad</label>
                        <input name="ciudad" value="{{ old('ciudad') }}" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-zinc-700">Dirección</label>
                        <textarea name="direccion" rows="2" required class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">{{ old('direccion') }}</textarea>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-zinc-700">Fecha nacimiento (opcional)</label>
                        <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-zinc-700">Género (opcional)</label>
                        <select name="genero" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">
                            @php($gen = old('genero'))
                            <option value="">Selecciona</option>
                            <option value="female" {{ $gen === 'female' ? 'selected' : '' }}>Mujer</option>
                            <option value="male" {{ $gen === 'male' ? 'selected' : '' }}>Hombre</option>
                            <option value="other" {{ $gen === 'other' ? 'selected' : '' }}>Otro</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-zinc-700">Notas (opcional)</label>
                        <textarea name="notas" rows="2" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">{{ old('notas') }}</textarea>
                    </div>
                </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 border-t border-zinc-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-zinc-500">Al continuar, pasarás a la plataforma de pago con esta misma configuración.</p>
                    <button id="checkoutSubmitButton" type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-zinc-950 px-6 py-3.5 text-sm font-semibold text-white hover:bg-zinc-900 sm:w-auto">
                        Continuar a pagar
                    </button>
                </div>
            </form>
            </div>
        </section>
    </div>
</main>

<script>
    (() => {
        const overlay = document.getElementById('landingGlobalLoading');
        if (!overlay) return;

        const state = { visible: false };

        const showLoading = () => {
            if (state.visible) return;
            state.visible = true;
            overlay.classList.add('is-visible');
            overlay.setAttribute('aria-hidden', 'false');
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
        };

        const hideLoading = () => {
            state.visible = false;
            overlay.classList.remove('is-visible');
            overlay.setAttribute('aria-hidden', 'true');
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
        };

        showLoading();

        window.addEventListener('load', () => {
            window.setTimeout(hideLoading, 120);
        });

        window.addEventListener('pageshow', hideLoading);

        document.addEventListener('click', (event) => {
            if (state.visible) {
                event.preventDefault();
                event.stopPropagation();
                return;
            }

            if (!(event.target instanceof Element)) return;
            if (event.target.closest('.gafa-color-swatch')) return;

            const anchor = event.target.closest('a[href]');
            if (!anchor) return;
            if (anchor.hasAttribute('data-no-global-loader')) return;
            if (anchor.closest('[data-no-global-loader]')) return;

            const href = anchor.getAttribute('href') || '';
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

            const target = (anchor.getAttribute('target') || '').toLowerCase();
            if (target === '_blank' || anchor.hasAttribute('download')) return;

            showLoading();
        }, true);

        document.addEventListener('submit', (event) => {
            if (state.visible || event.defaultPrevented) return;
            showLoading();
        });

        window.addEventListener('beforeunload', showLoading);
    })();
</script>

<script>
    (() => {
        const configurator = document.getElementById('lensConfigurator');
        const breakdown = document.getElementById('lensBreakdown');
        if (!configurator || !breakdown) return;

        const matrixRaw = configurator.getAttribute('data-lens-matrix') || '{}';
        let matrix = {};
        try {
            matrix = JSON.parse(matrixRaw);
        } catch {
            matrix = {};
        }

        const mode = String(configurator.getAttribute('data-mode') || 'progresivos');

        const monoRaw = configurator.getAttribute('data-mono-pricing') || '{}';
        let monoPricing = {};
        try {
            monoPricing = JSON.parse(monoRaw);
        } catch {
            monoPricing = {};
        }

        const bifocalRaw = configurator.getAttribute('data-bifocal-pricing') || '{}';
        let bifocalPricing = {};
        try {
            bifocalPricing = JSON.parse(bifocalRaw);
        } catch {
            bifocalPricing = {};
        }

        const ocupacionalRaw = configurator.getAttribute('data-ocupacional-pricing') || '{}';
        let ocupacionalPricing = {};
        try {
            ocupacionalPricing = JSON.parse(ocupacionalRaw);
        } catch {
            ocupacionalPricing = {};
        }

        const rxSphereMax = Number.parseFloat(String(configurator.getAttribute('data-rx-sphere-max') || '0')) || 0;
        const rxCylMax = Number.parseFloat(String(configurator.getAttribute('data-rx-cyl-max') || '0')) || 0;
        const isPolyEnabled = String(configurator.getAttribute('data-poly-enabled') || '0') === '1';
        const noPrescriptionInput = configurator.closest('form') ? configurator.closest('form').querySelector('input[name="no_prescription"]') : null;
        const planoNeutroInput = configurator.closest('form') ? configurator.closest('form').querySelector('input[name="plano_neutro"]') : null;
        const isNoPrescription = !!(noPrescriptionInput && String(noPrescriptionInput.value || '0') === '1');
        const isPlanoNeutro = !!(planoNeutroInput && String(planoNeutroInput.value || '0') === '1');
        const isNoFormulaSimple = String(configurator.getAttribute('data-no-formula-simple') || '0') === '1';

        const lensSelect = configurator.querySelector('[data-lens-select]');
        const colorWrap = configurator.querySelector('[data-lens-color]');
        const colorSelect = colorWrap ? colorWrap.querySelector('select') : null;
        const naraRadios = Array.from(configurator.querySelectorAll('[data-nara-radio]'));
        const basePrice = Number.parseFloat(breakdown.getAttribute('data-base') || '0') || 0;
        const currency = breakdown.getAttribute('data-currency') || 'COP';

        const elLens = breakdown.querySelector('[data-price-lens]');
        const elTotal = breakdown.querySelector('[data-price-total]');
        const elLensFamily = breakdown.querySelector('[data-summary-lens-family]');
        const elLensType = breakdown.querySelector('[data-summary-lens-type]');
        const elNara = breakdown.querySelector('[data-summary-nara]');
        const elLensColor = breakdown.querySelector('[data-summary-lens-color]');
        const fmt = new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 });

        const getNaraValue = () => {
            if (!naraRadios.length) return '';
            const checked = naraRadios.find((r) => r.checked);
            return checked ? String(checked.value || '') : '';
        };

        const getLensValue = () => (lensSelect ? String(lensSelect.value || '') : '');

        const getModeLabel = () => {
            if (isNoFormulaSimple) return 'Sin fórmula';
            if (mode === 'monofocal') return 'Lentes monofocales';
            if (mode === 'bifocal') return 'Lentes bifocales';
            if (mode === 'ocupacional') return 'Lentes ocupacionales';
            return 'Lentes progresivos digitales';
        };

        const getLensLabel = () => {
            if (isNoPrescription) return 'No aplica';
            if (!lensSelect) return 'No aplica';
            const option = lensSelect.options[lensSelect.selectedIndex];
            return option ? String(option.textContent || '').trim() : 'Por confirmar';
        };

        const getNaraLabel = () => {
            if (isNoPrescription) return 'No aplica';
            if (!naraRadios.length) return 'No aplica';
            const checked = naraRadios.find((radio) => radio.checked);
            if (!checked) return 'Por confirmar';
            const wrapper = checked.closest('label');
            const text = wrapper ? wrapper.querySelector('span') : null;
            return text ? String(text.textContent || '').trim() : String(checked.value || 'Por confirmar');
        };

        const syncColor = () => {
            if (!colorWrap || !colorSelect) return;
            if (isNoPrescription) {
                colorWrap.classList.add('hidden');
                colorSelect.disabled = true;
                colorSelect.required = false;
                colorSelect.value = '';
                return;
            }
            const isTransitions = getLensValue() === '159_transitions_gens';
            colorWrap.classList.toggle('hidden', !isTransitions);
            colorSelect.disabled = !isTransitions;
            colorSelect.required = isTransitions;
            if (!isTransitions) colorSelect.value = '';
        };

        const getMonofocalTier = (lensType) => {
            const polyRules = (monoPricing && monoPricing.rules && monoPricing.rules.poly_156) ? monoPricing.rules.poly_156 : null;
            const isPoly156 = isPolyEnabled
                && polyRules
                && !!polyRules.enabled
                && String(lensType || '').startsWith('156_');

            if (isPoly156) {
                if (rxSphereMax <= 3 && rxCylMax <= 2) return 1;
                if (rxSphereMax <= 4 && rxCylMax <= 4) return 2;
                if (rxSphereMax <= 9 && rxCylMax <= 9) return 3;
                return 4;
            }

            const is160 = lensType === '160_premium';
            if (is160) {
                return (rxSphereMax <= 4 && rxCylMax <= 4) ? 1 : 2;
            }
            if (rxSphereMax <= 3 && rxCylMax <= 3) return 1;
            if (rxSphereMax <= 4 && rxCylMax <= 4) return 2;
            return 3;
        };

        const getLensPrice = (lensType, naraLevel) => {
            if (!lensType) return 0;

            if (mode === 'monofocal') {
                const fixed = (monoPricing && monoPricing.fixed) ? monoPricing.fixed : {};
                const tiered = (monoPricing && monoPricing.tiered) ? monoPricing.tiered : {};
                const noFormula = (monoPricing && monoPricing.no_formula) ? monoPricing.no_formula : {};

                if (isPlanoNeutro) {
                    const vNoFormula = noFormula[lensType];
                    const nNoFormula = Number.parseFloat(String(vNoFormula || '0'));
                    return Number.isFinite(nNoFormula) ? nNoFormula : 0;
                }

                if (lensType === '159_transitions_gens') {
                    const rules = (monoPricing && monoPricing.rules && monoPricing.rules.transitions) ? monoPricing.rules.transitions : null;
                    const max = rules ? Number.parseFloat(String(rules.tier1_max ?? '0')) : 2;
                    const p1 = rules ? Number.parseFloat(String(rules.price_tier1 ?? '0')) : 475000;
                    const p2 = rules ? Number.parseFloat(String(rules.price_tier2 ?? '0')) : 499000;
                    const pColor = rules ? Number.parseFloat(String(rules.price_with_color ?? '0')) : 520000;
                    const colorVal = colorSelect ? String(colorSelect.value || '').trim() : '';
                    if (colorVal !== '') return Number.isFinite(pColor) ? pColor : 0;
                    const useTier1 = rxSphereMax <= max && rxCylMax <= max;
                    return useTier1 ? (Number.isFinite(p1) ? p1 : 0) : (Number.isFinite(p2) ? p2 : 0);
                }

                const fixedVal = fixed[lensType];
                if (fixedVal !== undefined && fixedVal !== null) {
                    const n = Number.parseFloat(String(fixedVal || '0'));
                    return Number.isFinite(n) ? n : 0;
                }

                const tiers = tiered[lensType];
                if (!tiers) return 0;
                const tier = getMonofocalTier(lensType);
                const v = tiers[String(tier)] ?? tiers[tier];
                const n = Number.parseFloat(String(v || '0'));
                return Number.isFinite(n) ? n : 0;
            }

            if (mode === 'bifocal') {
                const fixed = (bifocalPricing && bifocalPricing.fixed) ? bifocalPricing.fixed : {};
                const tiered = (bifocalPricing && bifocalPricing.tiered) ? bifocalPricing.tiered : {};

                if (isPolyEnabled && String(lensType || '').startsWith('156_')) {
                    const tiers = tiered[lensType];
                    if (tiers) {
                        const tier = getMonofocalTier(lensType);
                        const vTier = tiers[String(tier)] ?? tiers[tier];
                        const nTier = Number.parseFloat(String(vTier || '0'));
                        return Number.isFinite(nTier) ? nTier : 0;
                    }
                }

                const v = fixed[lensType];
                const n = Number.parseFloat(String(v || '0'));
                return Number.isFinite(n) ? n : 0;
            }

            if (mode === 'ocupacional') {
                const fixed = (ocupacionalPricing && ocupacionalPricing.fixed) ? ocupacionalPricing.fixed : {};
                const v = fixed[lensType];
                const n = Number.parseFloat(String(v || '0'));
                return Number.isFinite(n) ? n : 0;
            }

            if (!naraLevel) return 0;
            const row = matrix[lensType];
            if (!row) return 0;
            const v = row[naraLevel];
            const n = Number.parseFloat(String(v || '0'));
            return Number.isFinite(n) ? n : 0;
        };

        const render = () => {
            syncColor();
            if (isNoPrescription) {
                if (elLens) elLens.textContent = `${fmt.format(0)} ${currency}`;
                if (elTotal) elTotal.textContent = `${fmt.format(basePrice)} ${currency}`;
                if (elLensFamily) elLensFamily.textContent = 'Solo montura';
                if (elLensType) elLensType.textContent = 'No aplica';
                if (elNara) elNara.textContent = 'No aplica';
                if (elLensColor) elLensColor.textContent = 'No aplica';
                return;
            }
            const lensType = getLensValue();
            const naraLevel = getNaraValue();
            const lensPrice = getLensPrice(lensType, naraLevel);
            const total = basePrice + lensPrice;
            const lensColor = colorSelect && !colorSelect.disabled ? String(colorSelect.value || '').trim() : '';

            if (elLens) elLens.textContent = `${fmt.format(lensPrice)} ${currency}`;
            if (elTotal) elTotal.textContent = `${fmt.format(total)} ${currency}`;
            if (elLensFamily) elLensFamily.textContent = getModeLabel();
            if (elLensType) elLensType.textContent = getLensLabel();
            if (elNara) elNara.textContent = getNaraLabel();
            if (elLensColor) elLensColor.textContent = lensColor !== '' ? lensColor : 'Sin color';
        };

        if (lensSelect) lensSelect.addEventListener('change', render);
        if (colorSelect) colorSelect.addEventListener('change', render);
        naraRadios.forEach((r) => r.addEventListener('change', render));

        render();
    })();
</script>
</body>
</html>
