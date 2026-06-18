<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($producto->nombre); ?> — Optica S.A.S</title>

    <?php
$viteHot = public_path('hot')
?>
    <?php
$viteManifest = public_path('build/manifest.json')
?>
    <?php
$hasViteAssets = file_exists($viteHot) || file_exists($viteManifest)
?>

    <?php if($hasViteAssets): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php endif; ?>

    <?php if(!$hasViteAssets || app()->isLocal()): ?>
        <script src="https://cdn.tailwindcss.com"></script>
    <?php endif; ?>
</head>
<body class="min-h-dvh overflow-x-hidden bg-white text-zinc-900 antialiased">

<?php
$meta = is_array($producto->meta) ? $producto->meta : []
?>
<?php
$formulaPermitida = array_key_exists('formula_permitida', $meta)
    ? (bool) $meta['formula_permitida']
    : true
?>
<?php
$img = (string)($meta['imagen_url'] ?? '')
?>
<?php
$img2 = (string)($meta['imagen_url_2'] ?? '')
?>

<?php
$gallery = []
?>
<?php
$gallery[] = $img
?>
<?php
$gallery[] = $img2
?>
<?php
$extraGallery = $meta['imagenes'] ?? null
?>
<?php if(is_array($extraGallery)): ?>
    <?php $__currentLoopData = $extraGallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
$gallery[] = is_string($g) ? trim($g) : ''
?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php
$gallery = array_values(array_unique(array_filter($gallery, static fn ($u) => is_string($u) && $u !== '')))
?>
<?php
$primaryImg = $gallery[0] ?? ''
?>

<?php
$midZoom = 0.99
?>
<?php
$posX = (float) ($meta['image_pos_x'] ?? 50)
?>
<?php
$posY = (float) ($meta['image_pos_y'] ?? 50)
?>
<?php
$zoom = (float) ($meta['image_zoom'] ?? $midZoom)
?>
<?php
$zoom = max(0.5, min(1, $zoom))
?>
<?php
$t = max(0, min(1, ($zoom - 0.5) / 0.5))
?>
<?php
$posXEff = 50 + (($posX - 50) * $t)
?>
<?php
$posYEff = 50 + (($posY - 50) * $t)
?>

<?php
$genero =
    $producto->genero_objetivo === 'female' ? 'Mujer' :
    ($producto->genero_objetivo === 'male' ? 'Hombre' :
    ($producto->genero_objetivo === 'ninos' ? 'Niños' :
    ($producto->genero_objetivo === 'ninas' ? 'Niñas' :
    ($producto->genero_objetivo === 'gafas_polarizadas' ? 'Polarizadas' :
    ($producto->genero_objetivo === 'unisex' ? 'Todos' : '')))))
?>
<?php
$loginUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login')
?>
<?php
$viewer = auth()->user()
?>
<?php
$viewerRoleId = (int) ($viewer?->rol_id ?? 0)
?>
<?php
$hideLoveCta = \App\Services\ProductDetailSettings::hideLoveCtaForClients() && in_array($viewerRoleId, [0, 1], true)
?>

<?php
$caracteristicas = is_array($producto->caracteristicas) ? $producto->caracteristicas : []
?>
<?php
$polyEnabled = \App\Services\Gafas\GafaLensPricing::usesPolyForCharacteristics($caracteristicas)
?>
<?php
$medidas = is_array($caracteristicas['medidas'] ?? null) ? $caracteristicas['medidas'] : []
?>

<?php
$tipoLenteOld = (string) old('tipo_lente_necesitas', request('tipo_lente_necesitas'))
?>
<?php
$lensTypeOptionsProgresivos = \App\Services\Gafas\GafaLensPricing::lensTypeOptionsForProgresivos($polyEnabled)
?>
<?php
$lensTypeOptionsMonofocal = \App\Services\Gafas\GafaLensPricing::lensTypeOptionsForMonofocal($polyEnabled)
?>
<?php
$lensTypeOptionsBifocal = \App\Services\Gafas\GafaLensPricing::lensTypeOptionsForBifocal($polyEnabled)
?>
<?php
$lensTypeOptionsOcupacional = \App\Services\Gafas\GafaLensPricing::lensTypeOptionsForOcupacional($polyEnabled)
?>
<?php
$lensTypeOptions = $tipoLenteOld === \App\Services\Gafas\GafaLensPricing::TIPO_LENTE_MONOFOCAL ? $lensTypeOptionsMonofocal : ($tipoLenteOld === \App\Services\Gafas\GafaLensPricing::TIPO_LENTE_BIFOCAL ? $lensTypeOptionsBifocal : ($tipoLenteOld === \App\Services\Gafas\GafaLensPricing::TIPO_LENTE_OCUPACIONAL ? $lensTypeOptionsOcupacional : $lensTypeOptionsProgresivos))
?>
<?php
$naraOptions = \App\Services\Gafas\GafaLensPricing::naraLevelOptions()
?>
<?php
if ($polyEnabled) {
    $naraOptions = array_intersect_key($naraOptions, array_flip(['basica', 'media', 'alta']));
}
?>
<?php
$selectedLensTypeRaw = (string) old('lens_type', request('lens_type'))
?>
<?php
$selectedLensType = array_key_exists($selectedLensTypeRaw, \App\Services\Gafas\GafaLensPricing::lensTypeOptions())
    ? $selectedLensTypeRaw
    : ''
?>
<?php
$selectedNaraLevelRaw = (string) old('nara_level', request('nara_level'))
?>
<?php
$selectedNaraLevel = array_key_exists($selectedNaraLevelRaw, $naraOptions) ? $selectedNaraLevelRaw : ''
?>
<?php
$planoNeutralSphereOptions = collect(range(-1500, 800, 25))->map(function (int $value): array {
    $number = $value / 100;
    $formatted = number_format($number, 2, '.', '');

    return [
        'value' => $formatted,
        'label' => $number > 0 ? '+' . $formatted : $formatted,
    ];
})->values()->all()
?>
<?php
$planoNeutralCylinderOptions = collect(range(-600, 0, 25))->map(function (int $value): array {
    $number = $value / 100;
    $formatted = number_format($number, 2, '.', '');

    return [
        'value' => $formatted,
        'label' => $formatted,
    ];
})->values()->all()
?>
<?php
$manualSphereOptions = collect(range(-1500, 800, 25))->map(function (int $value): array {
    $number = $value / 100;
    $formatted = number_format($number, 2, '.', '');

    return [
        'value' => $formatted,
        'label' => $number > 0 ? '+' . $formatted : $formatted,
    ];
})->values()->all()
?>
<?php
$manualCylinderOptions = collect(range(-1500, 0, 25))->map(function (int $value): array {
    $number = $value / 100;
    $formatted = number_format($number, 2, '.', '');

    return [
        'value' => $formatted,
        'label' => $formatted,
    ];
})->values()->all()
?>
<?php
$manualAxisOptions = collect(range(0, 180))->map(fn (int $value): array => ['value' => (string) $value, 'label' => str_pad((string) $value, 3, '0', STR_PAD_LEFT)])->values()->all()
?>
<?php
$manualAdditionOptions = collect(range(75, 450, 25))->map(function (int $value): array {
    $number = $value / 100;
    $formatted = number_format($number, 2, '.', '');

    return [
        'value' => $formatted,
        'label' => '+' . $formatted,
    ];
})->values()->all()
?>
<?php
$precioBase = (float) ($producto->precio_oferta ?? $producto->precio ?? 0)
?>
<?php
$precioLentes = \App\Services\Gafas\GafaLensPricing::lensPrice((string) $selectedLensType, (string) $selectedNaraLevel, $polyEnabled)
?>
<?php
$precioTotal = $precioBase + (float) $precioLentes
?>

<?php
$fmtCm = function ($v) {
    if ($v === null || $v === '') return null;
    $v = (float) $v;
    return rtrim(rtrim(number_format($v, 1, '.', ''), '0'), '.');
}
?>

<?php
$precioBase = (float) ($producto->precio_oferta ?? $producto->precio ?? 0)
?>
<?php
$moneda = (string) ($producto->moneda ?? 'COP')
?>
<?php echo $__env->make('partials.store-navbar', ['showStoreBanner' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<main class="mx-auto max-w-7xl px-3 py-6 sm:px-4 sm:py-10">
    <div class="grid gap-6 sm:gap-8 lg:grid-cols-12">
        <section class="lg:col-span-7">
            <div class="px-2 pt-2">
                <div class="mx-auto w-full max-w-[42rem]">
                    <div id="gafaZoomArea" class="relative aspect-[4/3] w-full select-none overflow-hidden sm:aspect-[16/9]">
                        <?php if($primaryImg): ?>
                            <img
                                id="gafaPrimaryImg"
                                src="<?php echo e($primaryImg); ?>"
                                alt="<?php echo e(e($producto->nombre)); ?>"
                                data-base-scale="<?php echo e(number_format($zoom, 2, '.', '')); ?>"
                                data-default-origin="<?php echo e((int) round($posXEff)); ?>% <?php echo e((int) round($posYEff)); ?>%"
                                class="absolute inset-0 h-full w-full object-contain"
                                loading="eager"
                                fetchpriority="high"
                                decoding="async"
                                style="object-position: <?php echo e((int) round($posXEff)); ?>% <?php echo e((int) round($posYEff)); ?>%; transform-origin: <?php echo e((int) round($posXEff)); ?>% <?php echo e((int) round($posYEff)); ?>%; transform: scale(<?php echo e(number_format($zoom, 2, '.', '')); ?>); transition: transform 120ms ease-out, transform-origin 120ms ease-out;"
                            />
                            <button
                                type="button"
                                id="gafaPrevImage"
                                class="absolute bottom-3 left-3 z-10 hidden h-11 w-11 items-center justify-center rounded-full shadow-md transition hover:brightness-105 disabled:cursor-not-allowed disabled:opacity-45 sm:bottom-auto sm:top-1/2 sm:-translate-y-1/2"
                                style="background-color: var(--gafa-nav-bg, rgba(255, 255, 255, 0.92)); color: var(--gafa-nav-fg, #27272a); box-shadow: 0 18px 36px -22px var(--gafa-nav-shadow, rgba(15, 23, 42, 0.5));"
                                aria-label="Imagen anterior"
                            >
                                <span class="text-2xl leading-none">&#8249;</span>
                            </button>
                            <button
                                type="button"
                                id="gafaNextImage"
                                class="absolute bottom-3 right-3 z-10 hidden h-11 w-11 items-center justify-center rounded-full shadow-md transition hover:brightness-105 disabled:cursor-not-allowed disabled:opacity-45 sm:bottom-auto sm:top-1/2 sm:-translate-y-1/2"
                                style="background-color: var(--gafa-nav-bg, rgba(255, 255, 255, 0.92)); color: var(--gafa-nav-fg, #27272a); box-shadow: 0 18px 36px -22px var(--gafa-nav-shadow, rgba(15, 23, 42, 0.5));"
                                aria-label="Imagen siguiente"
                            >
                                <span class="text-2xl leading-none">&#8250;</span>
                            </button>
                        <?php else: ?>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <p class="text-sm font-semibold text-zinc-500">Sin imagen</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div id="gafaThumbsWrap" class="hidden" hidden aria-hidden="true">
                        <div id="gafaThumbs" class="contents">
                        <?php $__currentLoopData = $gallery; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button
                                type="button"
                                class="group w-16 shrink-0 overflow-hidden rounded-2xl border border-zinc-200/70 bg-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-zinc-500 sm:w-auto"
                                data-gafa-thumb="<?php echo e($g); ?>"
                                aria-label="Ver imagen"
                            >
                                <div class="relative aspect-square w-full bg-gradient-to-br from-zinc-50 to-white">
                                    <img src="<?php echo e($g); ?>" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition duration-200 group-hover:scale-[1.10] group-hover:saturate-110" />
                                </div>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div id="gafaMobileColorSection" class="mt-4 lg:hidden">
                        <p class="text-sm text-zinc-800"><span class="font-semibold">Color:</span> <span id="gafaColorLabelValueMobile"><?php echo e($colorLabel ?? ($meta['color'] ?? ($meta['color_nombre'] ?? ($producto->color ?? '')))); ?></span></p>
                        <div id="gafaMobileColorSwatches" class="mt-2 flex flex-wrap gap-2"></div>
                    </div>

                <?php
                    $textoPendiente = 'No especificado';
                    $recomendadoPara = trim((string) ($caracteristicas['recomendado_para'] ?? $meta['recomendado_para'] ?? ''));
                    $incluye = trim((string) ($caracteristicas['incluye'] ?? $meta['incluye'] ?? ''));
                    $clipOnRaw = $caracteristicas['clip_on_compatible'] ?? $meta['clip_on_compatible'] ?? null;
                    if (is_string($clipOnRaw)) {
                        $clipOnNormalizado = strtolower(trim($clipOnRaw));
                        if (in_array($clipOnNormalizado, ['1', 'si', 'sí', 'true'], true)) {
                            $clipOnCompatible = 'Sí';
                        } elseif (in_array($clipOnNormalizado, ['0', 'no', 'false'], true)) {
                            $clipOnCompatible = 'No';
                        } else {
                            $clipOnCompatible = trim($clipOnRaw) !== '' ? trim($clipOnRaw) : $textoPendiente;
                        }
                    } elseif (is_bool($clipOnRaw) || is_numeric($clipOnRaw)) {
                        $clipOnCompatible = (bool) $clipOnRaw ? 'Sí' : 'No';
                    } else {
                        $clipOnCompatible = 'No';
                    }

                    $vAt = $fmtCm($medidas['ancho_total_montura_cm'] ?? null);
                    $vAl = $fmtCm($medidas['ancho_lente_cm'] ?? null);
                    $vAlt = $fmtCm($medidas['alto_lente_cm'] ?? null);
                    $vP = $fmtCm($medidas['puente_cm'] ?? null);
                    $vLp = $fmtCm($medidas['largo_patillas_cm'] ?? null);
                    $filas = [
                        ['label' => 'Recomendado para:', 'value' => $recomendadoPara !== '' ? $recomendadoPara : $textoPendiente],
                        ['label' => 'Material', 'value' => $producto->material_montura ?: $textoPendiente],
                        ['label' => 'Clip-on compatible', 'value' => $clipOnCompatible],
                        ['label' => 'Ancho total de la montura', 'value' => $vAt !== null ? $vAt . ' cm' : $textoPendiente],
                        ['label' => 'Ancho del lente', 'value' => $vAl !== null ? $vAl . ' cm' : $textoPendiente],
                        ['label' => 'Alto del lente', 'value' => $vAlt !== null ? $vAlt . ' cm' : $textoPendiente],
                        ['label' => 'Puente', 'value' => $vP !== null ? $vP . ' cm' : $textoPendiente],
                        ['label' => 'Largo de patillas', 'value' => $vLp !== null ? $vLp . ' cm' : $textoPendiente],
                        ['label' => 'Incluye', 'value' => $incluye !== '' ? $incluye : $textoPendiente],
                    ];

                    if ($producto->descripcion) {
                        $filas[] = ['label' => 'Descripción', 'value' => $producto->descripcion];
                    }

                    $compatProgresivos = isset($caracteristicas['progresivos'])
                        ? ($caracteristicas['progresivos'] ? 'Sí' : 'No')
                        : 'No';
                    $compatTipoFormula = isset($caracteristicas['tipo_formula']) && trim((string) $caracteristicas['tipo_formula']) !== ''
                        ? (string) $caracteristicas['tipo_formula']
                        : 'Bajas';
                    $allowedLensDesigns = \App\Services\Gafas\GafaLensPricing::allowedLensDesignsForCharacteristics($caracteristicas);
                ?>

                
                <div class="mt-6 space-y-5">

                    
                    <div>
                        <p class="mb-3 px-1 text-xs font-bold uppercase tracking-widest text-zinc-400">Características</p>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <?php
                                $generalCards = [
                                    ['icon' => '👤', 'label' => 'Para', 'value' => $recomendadoPara !== '' ? $recomendadoPara : $textoPendiente],
                                    ['icon' => '🔩', 'label' => 'Material', 'value' => $producto->material_montura ?: $textoPendiente],
                                    ['icon' => '📎', 'label' => 'Clip-on', 'value' => $clipOnCompatible],
                                    ['icon' => '📦', 'label' => 'Incluye', 'value' => $incluye !== '' ? $incluye : $textoPendiente],
                                ];
                            ?>
                            <?php $__currentLoopData = $generalCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                                <span class="text-lg"><?php echo e($card['icon']); ?></span>
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-zinc-400"><?php echo e($card['label']); ?></p>
                                <p class="mt-0.5 text-sm font-semibold text-zinc-900"><?php echo e($card['value']); ?></p>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    
                    <?php if($vAt !== null || $vAl !== null || $vAlt !== null || $vP !== null || $vLp !== null): ?>
                    <div>
                        <p class="mb-3 px-1 text-xs font-bold uppercase tracking-widest text-zinc-400">Medidas</p>
                        <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                            <?php
                                $medidaCards = [
                                    ['label' => 'Total', 'value' => $vAt !== null ? $vAt.' cm' : '—'],
                                    ['label' => 'Lente (w)', 'value' => $vAl !== null ? $vAl.' cm' : '—'],
                                    ['label' => 'Lente (h)', 'value' => $vAlt !== null ? $vAlt.' cm' : '—'],
                                    ['label' => 'Puente', 'value' => $vP !== null ? $vP.' cm' : '—'],
                                    ['label' => 'Patillas', 'value' => $vLp !== null ? $vLp.' cm' : '—'],
                                ];
                            ?>
                            <?php $__currentLoopData = $medidaCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-2xl border border-zinc-200 bg-white px-3 py-3 text-center">
                                <p class="text-base font-black text-zinc-900"><?php echo e($m['value']); ?></p>
                                <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-zinc-400"><?php echo e($m['label']); ?></p>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    
                    <?php if($producto->descripcion): ?>
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-zinc-400">Descripción</p>
                        <p class="mt-1 text-sm text-zinc-700 leading-relaxed"><?php echo e($producto->descripcion); ?></p>
                    </div>
                    <?php endif; ?>

                    
                    <div>
                        <p class="mb-3 px-1 text-xs font-bold uppercase tracking-widest text-zinc-400">Compatibilidad</p>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Progresivos</p>
                                <p class="mt-0.5 text-sm font-bold <?php echo e($compatProgresivos === 'Sí' ? 'text-emerald-600' : 'text-zinc-900'); ?>"><?php echo e($compatProgresivos); ?></p>
                            </div>
                            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Tipo de fórmula</p>
                                <p class="mt-0.5 text-sm font-bold text-zinc-900"><?php echo e($compatTipoFormula); ?></p>
                            </div>
                            <?php if(array_key_exists('formula_permitida', $meta)): ?>
                            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-400">Fórmula médica</p>
                                <p class="mt-0.5 text-sm font-bold <?php echo e($formulaPermitida ? 'text-emerald-600' : 'text-zinc-900'); ?>"><?php echo e($formulaPermitida ? 'Sí' : 'No'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                </div>
            </div>
        </section>

        <section class="lg:col-span-5" data-gafa-sidebar>
            <?php
$stock = $producto->existencias
?>
            <?php
$agotado = $stock !== null && $stock <= 0
?>

            <div class="sticky top-24 py-4">

                
                <h2 class="text-2xl font-bold leading-tight tracking-tight text-zinc-900"><?php echo e($producto->nombre); ?></h2>

                
                <div class="mt-2">
                    <?php if($producto->precio_oferta): ?>
                        <div class="flex flex-wrap items-end gap-x-3 gap-y-1">
                            <p class="text-[3rem] font-bold leading-none text-zinc-900">$ <?php echo e(number_format((float) $producto->precio_oferta, 0, ',', '.')); ?></p>
                            <?php if($producto->precio !== null): ?>
                                <p class="pb-1 text-lg font-semibold text-zinc-400 line-through">$ <?php echo e(number_format((float) $producto->precio, 0, ',', '.')); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-[3rem] font-bold leading-none text-zinc-900"><?php echo e($producto->precio !== null ? '$ ' . number_format((float) $producto->precio, 0, ',', '.') : '—'); ?></p>
                    <?php endif; ?>
                </div>

                
                <?php
                    $colorLabel = (string) ($meta['color'] ?? ($meta['color_nombre'] ?? ($producto->color ?? '')));
                    $rawSwatches = $meta['colores'] ?? ($meta['color_variants'] ?? null);
                    $cameraRawSwatches = is_array($meta['camera_color_variants'] ?? null) ? $meta['camera_color_variants'] : [];
                    $detailSwatches = [];
                    $namedColorHex = [
                        'gris' => '#cec9bc',
                        'rosa' => '#e6c0c3',
                        'negro' => '#2a2020',
                        'azul' => '#b6d6e7',
                        'marron' => '#6f4e37',
                        'carey' => '#7b5a46',
                        'transparente' => '#e5e7eb',
                        'rojo' => '#9f2b2b',
                        'verde' => '#466b48',
                        'morado' => '#6d4c8b',
                        'dorado' => '#c6a75a',
                        'plateado' => '#b5bcc9',
                        'nude' => '#d7b39d',
                        'blanco' => '#f5f5f4',
                    ];

                    $pushDetailSwatch = static function (array $swatch) use (&$detailSwatches, $namedColorHex): void {
                        $name = trim((string) ($swatch['name'] ?? ''));
                        $hex = trim((string) ($swatch['hex'] ?? ''));
                        $image = trim((string) ($swatch['image'] ?? ''));
                        $stock = array_key_exists('stock', $swatch) && $swatch['stock'] !== null && $swatch['stock'] !== ''
                            ? max(0, (int) $swatch['stock'])
                            : null;
                        $images = [];

                        foreach ((array) ($swatch['images'] ?? []) as $candidate) {
                            $candidate = trim((string) $candidate);
                            if ($candidate !== '' && !in_array($candidate, $images, true)) {
                                $images[] = $candidate;
                            }
                        }

                        if ($image !== '' && !in_array($image, $images, true)) {
                            array_unshift($images, $image);
                        }

                        if ($name === '' && $hex === '') {
                            return;
                        }

                        if ($name === '') {
                            $name = 'Color';
                        }

                        if ($hex === '') {
                            $hex = $namedColorHex[strtolower($name)] ?? '#d9d3c5';
                        }

                        if (!str_starts_with($hex, '#')) {
                            $hex = '#' . ltrim($hex, '#');
                        }

                        $keyBase = strtolower(trim($name));
                        $keyImage = strtolower(trim($image !== '' ? $image : ($images[0] ?? '')));
                        $key = $keyImage !== '' ? ($keyBase . '||' . $keyImage) : $keyBase;
                        if (!isset($detailSwatches[$key])) {
                            $detailSwatches[$key] = [
                                'name' => $name,
                                'hex' => $hex,
                                'image' => $image !== '' ? $image : ($images[0] ?? ''),
                                'images' => $images,
                                'stock' => $stock,
                            ];

                            return;
                        }

                        $existingImages = is_array($detailSwatches[$key]['images'] ?? null) ? $detailSwatches[$key]['images'] : [];
                        foreach ($images as $candidate) {
                            if (!in_array($candidate, $existingImages, true)) {
                                $existingImages[] = $candidate;
                            }
                        }

                        $detailSwatches[$key]['images'] = $existingImages;
                        if (trim((string) ($detailSwatches[$key]['image'] ?? '')) === '' && $existingImages !== []) {
                            $detailSwatches[$key]['image'] = $existingImages[0];
                        }
                        if (($detailSwatches[$key]['stock'] ?? null) === null && $stock !== null) {
                            $detailSwatches[$key]['stock'] = $stock;
                        }
                    };

                    $hasStructuredVariants = is_array($rawSwatches)
                        && collect($rawSwatches)->contains(fn ($variant) => is_array($variant));

                    // Agregar color principal solo cuando no existan variantes estructuradas
                    $primaryColor = trim((string) ($meta['color'] ?? ($producto->color ?? '')));
                    if (! $hasStructuredVariants && $primaryColor !== '') {
                        $primaryHex = $namedColorHex[strtolower($primaryColor)] ?? '#cec9bc';
                        $primaryImage = trim((string) ($meta['imagen_url'] ?? ''));
                        $pushDetailSwatch([
                            'name' => $primaryColor,
                            'hex' => $primaryHex,
                            'image' => $primaryImage,
                            'images' => $primaryImage !== '' ? [$primaryImage] : [],
                        ]);
                    }

                    // Luego agregar variantes estructuradas desde la fuente activa de swatches
                    if (is_array($rawSwatches)) {
                        foreach ($rawSwatches as $variant) {
                            if (!is_array($variant)) {
                                continue;
                            }

                            $name = trim((string) ($variant['name'] ?? ''));
                            $hex = trim((string) ($variant['hex'] ?? ''));
                            $image = trim((string) ($variant['image'] ?? ''));
                            $images = [];

                            foreach ((array) ($variant['images'] ?? []) as $candidate) {
                                $candidate = trim((string) $candidate);
                                if ($candidate !== '' && !in_array($candidate, $images, true)) {
                                    $images[] = $candidate;
                                }
                            }

                            if ($name === '' && $hex === '') {
                                continue;
                            }

                            $variantNames = preg_split('/\s*,\s*/', $name) ?: [];
                            $variantNames = array_values(array_filter(array_map(static fn ($part) => trim((string) $part), $variantNames), static fn ($part) => $part !== ''));

                            if ($variantNames === []) {
                                $variantNames = [$name !== '' ? $name : 'Color'];
                            }

                            $isMultiNameVariant = count($variantNames) > 1;

                            foreach ($variantNames as $variantName) {
                                // En variantes combinadas, resolver el hex por nombre para pintar cada segmento correctamente.
                                $resolvedHex = $isMultiNameVariant
                                    ? ($namedColorHex[strtolower($variantName)] ?? ($hex !== '' ? $hex : '#d9d3c5'))
                                    : ($hex !== '' ? $hex : ($namedColorHex[strtolower($variantName)] ?? '#d9d3c5'));

                                $pushDetailSwatch([
                                    'name' => $variantName,
                                    'hex' => $resolvedHex,
                                    'image' => $image,
                                    'images' => $images,
                                    'stock' => $variant['stock'] ?? null,
                                ]);
                            }
                        }
                    } elseif (is_string($rawSwatches) && trim($rawSwatches) !== '') {
                        $parts = preg_split('/[,\n|]+/', $rawSwatches) ?: [];
                        foreach ($parts as $part) {
                            $c = ltrim(trim($part), '#');
                            if ($c !== '') {
                                $pushDetailSwatch([
                                    'name' => 'Color',
                                    'hex' => '#' . $c,
                                    'image' => '',
                                    'images' => [],
                                ]);
                            }
                        }
                    } elseif (is_array($rawSwatches)) {
                        foreach ($rawSwatches as $part) {
                            $c = ltrim(trim((string) $part), '#');
                            if ($c !== '') {
                                $pushDetailSwatch([
                                    'name' => 'Color',
                                    'hex' => '#' . $c,
                                    'image' => '',
                                    'images' => [],
                                ]);
                            }
                        }
                    }

                    $detailSwatches = array_slice(array_values($detailSwatches), 0, 20);

                    if (count($detailSwatches) === 0) {
                        $fallbackName = trim($colorLabel) !== '' ? trim($colorLabel) : 'Gris';
                        $fallbackHex = $namedColorHex[strtolower($fallbackName)] ?? '#cec9bc';
                        $detailSwatches = [[
                            'name' => $fallbackName,
                            'hex' => $fallbackHex,
                            'image' => '',
                            'images' => [],
                        ]];
                    }
                    if ($colorLabel === '') {
                        $colorLabel = (string) ($detailSwatches[0]['name'] ?? 'Gris');
                    }

                    $detailSwatches = array_map(function (array $sw) use ($producto) {
                        $images = [];
                        foreach ((array) ($sw['images'] ?? []) as $candidate) {
                            $candidate = trim((string) $candidate);
                            if ($candidate !== '' && !in_array($candidate, $images, true)) {
                                $images[] = $candidate;
                            }
                        }

                        if ($images === [] && trim((string) ($sw['image'] ?? '')) !== '') {
                            $images[] = trim((string) ($sw['image'] ?? ''));
                        }

                        $sw['images'] = $images;
                        if (trim((string) ($sw['image'] ?? '')) === '' && $images !== []) {
                            $sw['image'] = $images[0];
                        }
                        if (!array_key_exists('stock', $sw) || $sw['stock'] === null || $sw['stock'] === '') {
                            $sw['stock'] = $producto->stockDisponibleParaColor((string) ($sw['name'] ?? ''));
                        } else {
                            $sw['stock'] = max(0, (int) $sw['stock']);
                        }
                        return $sw;
                    }, $detailSwatches);

                    // Agrupar swatches por imagen: colores que comparten imagen son una combinación visual
                    $detailGroups = [];
                    foreach ($detailSwatches as $sw) {
                        $imgKey = $sw['image'] !== '' ? $sw['image'] : ('__solo__' . $sw['hex']);
                        if (!isset($detailGroups[$imgKey])) {
                            $detailGroups[$imgKey] = ['colors' => [], 'image' => $sw['image'], 'images' => $sw['images'] ?? []];
                        }
                        $detailGroups[$imgKey]['colors'][] = $sw;
                        foreach (($sw['images'] ?? []) as $candidate) {
                            if (!in_array($candidate, $detailGroups[$imgKey]['images'], true)) {
                                $detailGroups[$imgKey]['images'][] = $candidate;
                            }
                        }
                    }
                    $detailGroups = array_values($detailGroups);

                    // Label inicial = todos los colores del primer grupo
                    $firstGroup = $detailGroups[0] ?? null;
                    $colorLabel = $firstGroup
                        ? implode(', ', array_column($firstGroup['colors'], 'name'))
                        : ($colorLabel !== '' ? $colorLabel : 'Gris');

                    $stock = $producto->stockDisponibleParaColor(
                        (string) ($firstGroup['colors'][0]['name'] ?? $colorLabel)
                    );
                    $agotado = $stock !== null && $stock <= 0;
                    $stockLabel = $stock === null ? 'Disponible' : (max(0, (int) $stock) . ' disponibles');
                    $stockLabelClass = $agotado ? 'text-rose-700' : 'text-emerald-700';

                    // Grupos exclusivos para la cámara virtual (separados de los swatches normales)
                    $cameraGroups = [];
                    foreach ($cameraRawSwatches as $cameraVariant) {
                        if (!is_array($cameraVariant)) {
                            continue;
                        }

                        $cameraName = trim((string) ($cameraVariant['name'] ?? ''));
                        $cameraImages = [];
                        foreach ((array) ($cameraVariant['images'] ?? []) as $cameraCandidate) {
                            $cameraCandidate = trim((string) $cameraCandidate);
                            if ($cameraCandidate !== '' && !in_array($cameraCandidate, $cameraImages, true)) {
                                $cameraImages[] = $cameraCandidate;
                            }
                        }

                        if ($cameraImages === []) {
                            $singleCameraImage = trim((string) ($cameraVariant['image'] ?? ''));
                            if ($singleCameraImage !== '') {
                                $cameraImages[] = $singleCameraImage;
                            }
                        }

                        $cameraNames = preg_split('/\s*,\s*/', $cameraName) ?: [];
                        $cameraNames = array_values(array_filter(array_map(static fn ($part) => trim((string) $part), $cameraNames), static fn ($part) => $part !== ''));
                        if ($cameraNames === []) {
                            $cameraNames = ['Gris'];
                        }

                        $cameraColors = [];
                        foreach ($cameraNames as $cameraColorName) {
                            $cameraColors[] = [
                                'name' => $cameraColorName,
                                'hex' => $namedColorHex[strtolower($cameraColorName)] ?? '#cec9bc',
                            ];
                        }

                        $cameraGroups[] = [
                            'colors' => $cameraColors,
                            'image' => $cameraImages[0] ?? '',
                            'images' => $cameraImages,
                        ];
                    }

                    $hasCameraVirtual = $cameraGroups !== [];
                ?>

                <div class="mt-3">
                    <p class="text-sm text-zinc-800"><span class="font-semibold">Color:</span> <span id="gafaColorLabelValue"><?php echo e($colorLabel); ?></span></p>
                    <p class="mt-1 text-sm text-zinc-800"><span class="font-semibold">Stock:</span> <span id="gafaColorStockValue" class="font-semibold <?php echo e($stockLabelClass); ?>"><?php echo e($stockLabel); ?></span></p>
                    <div class="mt-2 flex flex-wrap gap-2" data-gafa-color-source="sidebar">
                        <?php $__currentLoopData = $detailGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $groupColors  = $group['colors'];
                                $groupImage   = $group['image'];
                                $groupImages  = $group['images'];
                                $groupName    = implode(', ', array_column($groupColors, 'name'));
                                $firstHex     = $groupColors[0]['hex'];
                                $groupStock   = $groupColors[0]['stock'] ?? null;
                                $isMulti      = count($groupColors) > 1;
                                $isCurrent    = collect($groupColors)->contains(fn($s) => strtolower($s['name']) === strtolower($colorLabel))
                                                || ($i === 0 && $colorLabel === '');
                                $isSoldOut    = $groupStock !== null && (int) $groupStock <= 0;
                                if ($isMulti) {
                                    $hexes = array_column($groupColors, 'hex');
                                    $n = count($hexes); $sz = 24;
                                    $svgPaths = '';
                                    for ($ci = 0; $ci < $n; $ci++) {
                                        $sa = 360/$n * $ci - 90; $ea = 360/$n * ($ci+1) - 90;
                                        $x1 = round($sz/2 + ($sz/2)*cos(M_PI*$sa/180), 3);
                                        $y1 = round($sz/2 + ($sz/2)*sin(M_PI*$sa/180), 3);
                                        $x2 = round($sz/2 + ($sz/2)*cos(M_PI*$ea/180), 3);
                                        $y2 = round($sz/2 + ($sz/2)*sin(M_PI*$ea/180), 3);
                                        $la = (360/$n) > 180 ? 1 : 0;
                                        $svgPaths .= '<path d="M'.($sz/2).','.($sz/2).' L'.$x1.','.$y1.' A'.($sz/2).','.($sz/2).' 0 '.$la.' 1 '.$x2.','.$y2.' Z" fill="'.e($hexes[$ci]).'" />';
                                    }
                                }
                            ?>
                            <button
                                type="button"
                                class="inline-block h-6 w-6 overflow-hidden rounded-full border-2 border-white shadow-[0_0_0_1.5px_rgba(0,0,0,0.18)] transition data-[active=true]:scale-110 data-[active=true]:shadow-[0_0_0_2px_rgba(24,24,27,0.85)] <?php echo e($isSoldOut ? 'opacity-45' : ''); ?>"
                                style="<?php echo e(!$isMulti ? 'background-color: ' . $firstHex . ';' : ''); ?>"
                                data-gafa-color-swatch
                                data-color-name="<?php echo e($groupName); ?>"
                                data-color-image="<?php echo e($groupImage); ?>"
                                data-color-images='<?php echo json_encode($groupImages, 15, 512) ?>'
                                data-color-stock="<?php echo e($groupStock !== null ? (int) $groupStock : ''); ?>"
                                data-active="<?php echo e($isCurrent ? 'true' : 'false'); ?>"
                                aria-label="Color <?php echo e($groupName); ?>"
                                aria-pressed="<?php echo e($isCurrent ? 'true' : 'false'); ?>"
                            ><?php if($isMulti): ?><svg width="100%" height="100%" viewBox="0 0 <?php echo e($sz); ?> <?php echo e($sz); ?>" style="display:block;"><?php echo $svgPaths; ?></svg><?php endif; ?></button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                <hr class="mt-4 border-zinc-200">

                
                <ul class="mt-3 space-y-2 text-sm text-zinc-600">
                    <li class="flex items-center gap-2.5">
                        <span class="text-base leading-none">🚚</span>
                        <span>Envío GRATIS entrega de 6 a 9 días hábiles</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="text-base leading-none">🛡️</span>
                        <span>30 días para cambios y devoluciones</span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="text-base leading-none">🏪</span>
                        <span>Pruébatelos en nuestras sedes</span>
                    </li>
                </ul>

                
                <div class="mt-5">
                    <div class="flex items-center gap-3">
                        <?php if (! ($hideLoveCta)): ?>
                            <div class="min-w-0 flex-1">
                                <button
                                    type="button"
                                    <?php if($formulaPermitida): ?> data-open-buy-panel <?php else: ?> data-direct-polarizada-buy <?php endif; ?>
                                    class="inline-flex w-full items-center justify-center rounded-2xl px-6 py-3.5 text-base font-black transition focus:outline-none focus:ring-2 bg-[#111827] text-white hover:bg-[#1f2937] focus:ring-zinc-500/60 <?php echo e($agotado ? 'cursor-not-allowed opacity-50' : ''); ?>"
                                    <?php echo e($agotado ? 'disabled' : ''); ?>

                                >
                                    <?php echo e($agotado ? 'Color agotado' : 'Adquiere aquí'); ?>

                                </button>
                            </div>
                        <?php endif; ?>
                        <img src="<?php echo e(asset('images/enamorado.png')); ?>" alt="" loading="lazy" class="h-20 w-auto shrink-0 object-contain" />
                    </div>

                    <?php if($hasCameraVirtual): ?>
                        <div class="mt-3">
                            <button
                                type="button"
                                id="btnAbrirCamara"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-zinc-900 px-6 py-3.5 text-base font-black text-white transition hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-900/60"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                                </svg>
                                Cámara
                            </button>
                        </div>

                        
                        <div id="modalCamara" class="fixed inset-0 z-[2147483647] hidden items-center justify-center bg-black/30 p-1 sm:p-4">
                            <div class="relative w-full max-w-[96vw] overflow-hidden rounded-3xl bg-black shadow-2xl sm:max-w-3xl lg:max-w-5xl">
                                <button
                                    type="button"
                                    id="btnCerrarCamara"
                                    class="absolute right-3 top-3 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white hover:bg-white/40 transition"
                                    aria-label="Cerrar cámara"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                                <div class="gafa-safari-soft-surface absolute left-3 right-16 top-3 z-10 flex items-center gap-2 overflow-x-auto rounded-2xl bg-black/35 px-2 py-2 backdrop-blur-sm">
                                    <?php $__currentLoopData = $cameraGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $camGroupColors = $group['colors'];
                                            $camGroupName   = implode(', ', array_column($camGroupColors, 'name'));
                                            $camGroupImg    = $group['image'] !== '' ? $group['image'] : $primaryImg;
                                            $camFirstHex    = $camGroupColors[0]['hex'];
                                            $camIsMulti     = count($camGroupColors) > 1;
                                            $camActive      = ($i === 0);
                                            if ($camIsMulti) {
                                                $camHexes = array_column($camGroupColors, 'hex');
                                                $camN = count($camHexes); $camSz = 28;
                                                $camPaths = '';
                                                for ($ci = 0; $ci < $camN; $ci++) {
                                                    $sa = 360/$camN * $ci - 90; $ea = 360/$camN * ($ci+1) - 90;
                                                    $x1 = round($camSz/2 + ($camSz/2)*cos(M_PI*$sa/180), 3);
                                                    $y1 = round($camSz/2 + ($camSz/2)*sin(M_PI*$sa/180), 3);
                                                    $x2 = round($camSz/2 + ($camSz/2)*cos(M_PI*$ea/180), 3);
                                                    $y2 = round($camSz/2 + ($camSz/2)*sin(M_PI*$ea/180), 3);
                                                    $la = (360/$camN) > 180 ? 1 : 0;
                                                    $camPaths .= '<path d="M'.($camSz/2).','.($camSz/2).' L'.$x1.','.$y1.' A'.($camSz/2).','.($camSz/2).' 0 '.$la.' 1 '.$x2.','.$y2.' Z" fill="'.e($camHexes[$ci]).'" />';
                                                }
                                            }
                                        ?>
                                        <button
                                            type="button"
                                            class="inline-block h-7 w-7 shrink-0 overflow-hidden rounded-full border-2 border-white/90 shadow-[0_0_0_1.5px_rgba(0,0,0,0.35)] transition data-[active=true]:scale-110 data-[active=true]:shadow-[0_0_0_2px_rgba(93,232,220,0.95)]"
                                            style="<?php echo e(!$camIsMulti ? 'background-color: ' . $camFirstHex . ';' : ''); ?>"
                                            data-cam-color-swatch
                                            data-cam-color-name="<?php echo e($camGroupName); ?>"
                                            data-cam-color-image="<?php echo e($camGroupImg); ?>"
                                            data-active="<?php echo e($camActive ? 'true' : 'false'); ?>"
                                            aria-label="Color cámara <?php echo e($camGroupName); ?>"
                                            aria-pressed="<?php echo e($camActive ? 'true' : 'false'); ?>"
                                        ><?php if($camIsMulti): ?><svg width="100%" height="100%" viewBox="0 0 <?php echo e($camSz); ?> <?php echo e($camSz); ?>" style="display:block;"><?php echo $camPaths; ?></svg><?php endif; ?></button>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <div id="camaraViewport" class="relative w-full h-[calc(100dvh-9.5rem)] min-h-[28rem] max-h-[92dvh] sm:aspect-[16/9] sm:h-auto sm:min-h-0 sm:max-h-none">
                                    <video id="camaraVideo" autoplay playsinline muted class="absolute inset-0 h-full w-full" style="object-fit:contain;"></video>
                                    <canvas id="camaraOverlay" class="absolute inset-0 h-full w-full pointer-events-none"></canvas>
                                </div>
                                <div id="camaraStatus" class="hidden items-center justify-center gap-2 p-3 text-sm text-zinc-400">
                                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                    <span id="camaraStatusText">Cargando detección facial…</span>
                                </div>
                                <p id="camaraNoFace" class="hidden px-4 pb-2 text-center text-sm font-semibold text-amber-300">No se detecta la cara. Ponte en una zona con buena luz y mira al frente.</p>
                                <p id="camaraError" class="hidden p-4 text-center text-sm text-red-400"></p>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </section>
    </div>

    <?php if(!$formulaPermitida): ?>
    
    <form id="directPolarizadaBuyForm" action="<?php echo e(route('checkout.gafa', ['producto' => $producto->slug])); ?>" method="GET" class="hidden">
        <input type="hidden" name="no_prescription" value="1">
        <input type="hidden" name="frame_color" value="<?php echo e(e((string)($colorLabel ?? ($producto->color ?? '')))); ?>" data-direct-frame-color>
    </form>
    <?php endif; ?>

        
        <?php
            $editorialGenero = match($producto->genero_objetivo ?? '') {
                'female'           => 'mujer',
                'male'             => 'hombre',
                'ninos', 'ninas'   => 'niños',
                'gafas_polarizadas'=> 'sol',
                default            => 'todos',
            };
            $editorialForma = trim((string) ($caracteristicas['forma'] ?? $meta['forma'] ?? ''));
            if ($editorialForma === '') {
                $editorialForma = match($producto->material_montura ?? '') {
                    'metal'   => 'Metálica',
                    'plastico', 'plástico' => 'Plástica',
                    'acetato' => 'Acetato',
                    default   => 'Clásica',
                };
            }
            $editorialDesc = trim((string) ($producto->descripcion ?? ''));
            if ($editorialDesc === '') {
                $editorialDesc = 'Un diseño que fusiona estética contemporánea con comodidad de uso diario.';
            }
            $hasAnyMedida = ($vAl !== null || $vP !== null || $vLp !== null || $vAt !== null);
        ?>
        <section class="mt-20 border-t border-zinc-100 py-16" style="background:#fff;">
            <div class="mx-auto max-w-5xl px-4">
                
                <h2 style="font-family: Georgia,'Times New Roman',serif; font-size: clamp(2rem,5vw,3.8rem); font-weight: 400; color: #111827; line-height: 1.15; margin: 0 0 2.5rem; max-width: 700px;">
                    <?php echo e($editorialDesc); ?>

                </h2>

                <?php if($hasAnyMedida): ?>
                <div class="mt-6">
                    <p class="mb-6 text-[11px] font-bold uppercase tracking-[0.2em] text-zinc-400">FORMA: <?php echo e($editorialForma); ?></p>

                    <div class="flex flex-col items-start gap-10 sm:flex-row sm:items-end sm:gap-16">

                        
                        <div class="flex flex-col items-center gap-2">
                            <svg viewBox="0 0 220 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 220px; height: auto;" stroke="#1a1a1a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                
                                <rect x="8" y="22" width="86" height="56" rx="16"/>
                                
                                <rect x="126" y="22" width="86" height="56" rx="16"/>
                                
                                <path d="M94 50 Q110 42 126 50"/>
                                
                                <path d="M8 50 L0 46"/>
                                
                                <path d="M212 50 L220 46"/>
                                <?php if($vAl !== null): ?>
                                    
                                    <line x1="8" y1="16" x2="94" y2="16" stroke="#9ca3af" stroke-width="1"/>
                                    <line x1="8" y1="13" x2="8" y2="19" stroke="#9ca3af" stroke-width="1"/>
                                    <line x1="94" y1="13" x2="94" y2="19" stroke="#9ca3af" stroke-width="1"/>
                                    <text x="51" y="11" text-anchor="middle" font-size="9" fill="#9ca3af" font-family="sans-serif"><?php echo e($vAl); ?> cm</text>
                                <?php endif; ?>
                            </svg>
                            <p class="text-[11px] text-zinc-400 font-medium uppercase tracking-wider">Vista frontal</p>
                        </div>

                        
                        <div class="flex flex-col items-center gap-2">
                            <svg viewBox="0 0 200 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 180px; height: auto;" stroke="#1a1a1a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                
                                <path d="M20 60 Q20 30 50 28 L90 26 Q110 26 115 36 L190 36 Q196 36 196 44 L180 52 Q160 56 140 54 L110 50 Q90 50 60 56 Z"/>
                                <?php if($vLp !== null): ?>
                                    
                                    <line x1="10" y1="90" x2="196" y2="90" stroke="#9ca3af" stroke-width="1"/>
                                    <line x1="10" y1="87" x2="10" y2="93" stroke="#9ca3af" stroke-width="1"/>
                                    <line x1="196" y1="87" x2="196" y2="93" stroke="#9ca3af" stroke-width="1"/>
                                    <text x="103" y="99" text-anchor="middle" font-size="9" fill="#9ca3af" font-family="sans-serif"><?php echo e($vLp); ?> cm</text>
                                <?php endif; ?>
                            </svg>
                            <p class="text-[11px] text-zinc-400 font-medium uppercase tracking-wider">Vista lateral</p>
                        </div>

                        
                        <div class="flex flex-col gap-3">
                            <?php if($vAl !== null): ?>
                            <div><p class="text-[10px] font-bold uppercase tracking-widest text-zinc-300">Lente</p><p class="text-xl font-black text-zinc-900"><?php echo e($vAl); ?> cm</p></div>
                            <?php endif; ?>
                            <?php if($vP !== null): ?>
                            <div><p class="text-[10px] font-bold uppercase tracking-widest text-zinc-300">Puente</p><p class="text-xl font-black text-zinc-900"><?php echo e($vP); ?> cm</p></div>
                            <?php endif; ?>
                            <?php if($vLp !== null): ?>
                            <div><p class="text-[10px] font-bold uppercase tracking-widest text-zinc-300">Patillas</p><p class="text-xl font-black text-zinc-900"><?php echo e($vLp); ?> cm</p></div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="mt-16">
            <h2 class="text-center text-[2.2rem] font-black tracking-tight text-zinc-900">Otras del mismo estilo!</h2>

            <style>
                @keyframes related-minimal-scroll {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-50%); }
                }
                .related-minimal-track {
                    animation: related-minimal-scroll 40s linear infinite;
                    width: max-content;
                }

                .gafa-required-underline {
                    text-decoration-line: underline;
                    text-decoration-color: #e11d48;
                    text-decoration-thickness: 2px;
                    text-underline-offset: 4px;
                }

                .gafa-required-border {
                    border-color: #f43f5e !important;
                }

                .gafa-safari-soft-surface {
                    -webkit-backdrop-filter: blur(12px);
                    backdrop-filter: blur(12px);
                }

                @media (hover: hover) and (pointer: fine) {
                    .related-minimal-wrap:hover .related-minimal-track {
                        animation-play-state: paused;
                    }
                }

                @media (hover: none), (pointer: coarse) {
                    .related-minimal-track {
                        animation-duration: 72s;
                        will-change: transform;
                    }

                    .gafa-safari-soft-surface {
                        -webkit-backdrop-filter: none;
                        backdrop-filter: none;
                    }
                }
            </style>

            <?php
                $placeholderSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 520"><rect width="1000" height="520" fill="transparent"/><g fill="none" stroke="#9ca3af" stroke-width="22"><ellipse cx="300" cy="260" rx="170" ry="115"/><ellipse cx="700" cy="260" rx="170" ry="115"/><path d="M470 248c18-18 42-18 60 0"/><path d="M96 252c58-26 92-34 126-36"/><path d="M904 252c-58-26-92-34-126-36"/></g></svg>';
                $placeholderImage = 'data:image/svg+xml;utf8,' . rawurlencode($placeholderSvg);

                $rawRelated = ($relatedProducts ?? collect())->values();
                $displayItems = $rawRelated->map(function ($relatedProduct) {
                    $relatedMeta = is_array($relatedProduct->meta) ? $relatedProduct->meta : [];
                    return [
                        'name' => (string) $relatedProduct->nombre,
                        'image' => (string) ($relatedMeta['imagen_url'] ?? $relatedMeta['imagen_url_2'] ?? ''),
                        'href' => route('gafas.show', ['producto' => $relatedProduct->slug]),
                    ];
                })->values();

                while ($displayItems->count() < 3) {
                    $displayItems->push([
                        'name' => ['Aldous', 'Marcela (Grande)', 'Wilder'][$displayItems->count()] ?? 'Modelo',
                        'image' => $placeholderImage,
                        'href' => '#',
                    ]);
                }
            ?>

            <div class="related-minimal-wrap relative mx-auto mt-5 w-full max-w-6xl overflow-hidden px-4 md:px-14" data-related-carousel>
                <div class="mb-4 flex items-center justify-center gap-3 md:mb-5">
                    <button type="button" data-related-prev aria-label="Anterior" class="inline-flex h-9 w-9 select-none items-center justify-center rounded-full bg-[#111827] text-white shadow md:h-10 md:w-10" style="-webkit-tap-highlight-color:transparent;-webkit-user-select:none;user-select:none;">
                        <span class="text-2xl leading-none">‹</span>
                    </button>
                    <button type="button" data-related-next aria-label="Siguiente" class="inline-flex h-9 w-9 select-none items-center justify-center rounded-full bg-[#111827] text-white shadow md:h-10 md:w-10" style="-webkit-tap-highlight-color:transparent;-webkit-user-select:none;user-select:none;">
                        <span class="text-2xl leading-none">›</span>
                    </button>
                </div>

                <div class="related-minimal-track flex items-start gap-6 md:gap-8" data-related-track>
                    <?php $__currentLoopData = [0, 1]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loopIndex): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $displayItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <article class="w-[calc((100vw-7rem)/1.2)] shrink-0 text-center sm:w-[40vw] lg:w-[18.7rem]">
                                <a href="<?php echo e($item['href']); ?>" class="block <?php echo e($item['href'] === '#' ? 'cursor-default' : ''); ?>">
                                    <div class="aspect-[16/7] overflow-hidden">
                                        <img src="<?php echo e($item['image'] ?: $placeholderImage); ?>" alt="<?php echo e(e($item['name'])); ?>" loading="lazy" class="h-full w-full object-contain" />
                                    </div>
                                    <h3 class="mt-4 text-[2rem] font-semibold leading-tight text-zinc-800 sm:text-[1.95rem]"><?php echo e($item['name']); ?></h3>
                                </a>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <script>
                (() => {
                    const carousel = document.querySelector('[data-related-carousel]');
                    if (!carousel) return;

                    const track = carousel.querySelector('[data-related-track]');
                    const prevBtn = carousel.querySelector('[data-related-prev]');
                    const nextBtn = carousel.querySelector('[data-related-next]');
                    if (!track || !prevBtn || !nextBtn) return;

                    const getTrackAnimation = () => {
                        const animations = track.getAnimations();
                        const animation = animations[0];
                        if (!animation || animation.effect === null) return null;

                        const timing = animation.effect.getTiming();
                        const duration = Number(timing.duration || 0);
                        if (!Number.isFinite(duration) || duration <= 0) return null;

                        return { animation, duration };
                    };

                    let nudgeRafId = null;
                    let holdRafId = null;
                    let holdStartTimerId = null;
                    let holdDirection = 0;
                    let holdLastTs = 0;
                    let isHolding = false;
                    let activePressDirection = 0;
                    let suppressClick = false;

                    const HOLD_DELAY_MS = 170;
                    const HOLD_SPEED_FACTOR = 0.0002;

                    const nudge = (direction) => {
                        const data = getTrackAnimation();
                        if (!data) return;
                        const { animation, duration } = data;

                        const step = duration * 0.06;
                        const from = Number(animation.currentTime || 0);
                        const to = from + (direction * step);
                        const smoothMs = 230;
                        const startedAt = performance.now();

                        if (nudgeRafId !== null) {
                            cancelAnimationFrame(nudgeRafId);
                            nudgeRafId = null;
                        }

                        const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);

                        const tick = (now) => {
                            const elapsed = now - startedAt;
                            const progress = Math.min(1, elapsed / smoothMs);
                            const eased = easeOutCubic(progress);
                            animation.currentTime = from + ((to - from) * eased);

                            if (progress < 1) {
                                nudgeRafId = requestAnimationFrame(tick);
                            } else {
                                nudgeRafId = null;
                            }
                        };

                        nudgeRafId = requestAnimationFrame(tick);
                    };

                    const stopHoldLoop = () => {
                        isHolding = false;
                        holdDirection = 0;
                        holdLastTs = 0;
                        if (holdRafId !== null) {
                            cancelAnimationFrame(holdRafId);
                            holdRafId = null;
                        }
                    };

                    const holdTick = (now) => {
                        if (!isHolding || holdDirection === 0) {
                            holdRafId = null;
                            return;
                        }

                        const data = getTrackAnimation();
                        if (!data) {
                            holdRafId = requestAnimationFrame(holdTick);
                            return;
                        }

                        const { animation, duration } = data;
                        if (!holdLastTs) {
                            holdLastTs = now;
                        }

                        const deltaMs = now - holdLastTs;
                        holdLastTs = now;

                        const current = Number(animation.currentTime || 0);
                        const step = duration * HOLD_SPEED_FACTOR * deltaMs;
                        animation.currentTime = current + (holdDirection * step);
                        holdRafId = requestAnimationFrame(holdTick);
                    };

                    const beginHold = (direction) => {
                        if (nudgeRafId !== null) {
                            cancelAnimationFrame(nudgeRafId);
                            nudgeRafId = null;
                        }
                        isHolding = true;
                        holdDirection = direction;
                        holdLastTs = 0;
                        if (holdRafId !== null) {
                            cancelAnimationFrame(holdRafId);
                            holdRafId = null;
                        }
                        holdRafId = requestAnimationFrame(holdTick);
                    };

                    const startPress = (event, direction) => {
                        event.preventDefault();
                        event.stopPropagation();

                        activePressDirection = direction;
                        suppressClick = true;

                        if (holdStartTimerId !== null) {
                            clearTimeout(holdStartTimerId);
                            holdStartTimerId = null;
                        }

                        holdStartTimerId = window.setTimeout(() => {
                            holdStartTimerId = null;
                            beginHold(direction);
                        }, HOLD_DELAY_MS);
                    };

                    const endPress = (event) => {
                        if (event) {
                            event.preventDefault();
                            event.stopPropagation();
                        }

                        const direction = activePressDirection;
                        const held = isHolding;

                        if (holdStartTimerId !== null) {
                            clearTimeout(holdStartTimerId);
                            holdStartTimerId = null;
                        }

                        stopHoldLoop();
                        activePressDirection = 0;

                        if (!held && direction !== 0) {
                            nudge(direction);
                        }

                        window.setTimeout(() => {
                            suppressClick = false;
                        }, 0);
                    };

                    const handle = (event, direction) => {
                        event.preventDefault();
                        event.stopPropagation();
                        nudge(direction);
                    };

                    prevBtn.addEventListener('pointerdown', (event) => startPress(event, -1));
                    nextBtn.addEventListener('pointerdown', (event) => startPress(event, 1));

                    window.addEventListener('pointerup', (event) => {
                        if (activePressDirection === 0) return;
                        endPress(event);
                    });
                    window.addEventListener('pointercancel', (event) => {
                        if (activePressDirection === 0) return;
                        endPress(event);
                    });

                    prevBtn.addEventListener('click', (event) => {
                        if (suppressClick) {
                            event.preventDefault();
                            event.stopPropagation();
                            return;
                        }
                        handle(event, -1);
                    });
                    nextBtn.addEventListener('click', (event) => {
                        if (suppressClick) {
                            event.preventDefault();
                            event.stopPropagation();
                            return;
                        }
                        handle(event, 1);
                    });
                })();
            </script>
        </section>
</main>

<div id="buyPanelBackdrop" class="fixed inset-0 z-40 hidden bg-black/25"></div>
<?php
$checkoutActionPanel = route('checkout.gafa', ['producto' => $producto->slug]);
?>
<div id="buyPanel"
     data-base-price="<?php echo e((float) $precioBase); ?>"
     data-currency-label="<?php echo e($moneda); ?>"
     data-checkout-action="<?php echo e($checkoutActionPanel); ?>"
    data-lens-matrix='<?php echo json_encode(\App\Services\Gafas\GafaLensPricing::matrixForCheckout($polyEnabled), 15, 512) ?>'
    data-mono-pricing='<?php echo json_encode(\App\Services\Gafas\GafaLensPricing::monofocalClientPricing($polyEnabled), 15, 512) ?>'
    data-bifocal-pricing='<?php echo json_encode(\App\Services\Gafas\GafaLensPricing::bifocalClientPricing($polyEnabled), 15, 512) ?>'
    data-poly-enabled="<?php echo e($polyEnabled ? '1' : '0'); ?>"
    data-ocupacional-pricing='<?php echo json_encode(\App\Services\Gafas\GafaLensPricing::ocupacionalClientPricing($polyEnabled), 15, 512) ?>'
    data-lens-options-progresivos='<?php echo json_encode($lensTypeOptionsProgresivos, 15, 512) ?>'
    data-lens-options-monofocal='<?php echo json_encode($lensTypeOptionsMonofocal, 15, 512) ?>'
    data-lens-options-bifocal='<?php echo json_encode($lensTypeOptionsBifocal, 15, 512) ?>'
    data-lens-options-ocupacional='<?php echo json_encode($lensTypeOptionsOcupacional, 15, 512) ?>'
     data-selected-lens="<?php echo e(e((string) $selectedLensType)); ?>"
     data-selected-nara="<?php echo e(e((string) $selectedNaraLevel)); ?>"
    data-open-on-load="<?php echo e($errors->any() ? '1' : '0'); ?>"
     class="pointer-events-none fixed inset-y-0 right-0 z-50 w-screen max-w-full translate-x-full transition-transform duration-300 ease-out">
    <div id="buyPanelPdfVerifyingOverlay" class="fixed inset-0 z-[60] hidden bg-black/35">
        <div class="flex h-full w-full items-center justify-center px-6">
            <div class="w-full max-w-sm rounded-3xl border border-zinc-200 bg-white p-5 text-center">
                <div class="mx-auto flex w-full items-center justify-center">
                    <img
                        id="buyPanelLoadingGif"
                        alt="Cargando tu PDF"
                        class="h-24 w-24"
                    />
                </div>
                <p class="mt-3 text-sm font-semibold text-zinc-900">Estamos cargando tu PDF</p>
                <p class="mt-1 text-xs text-zinc-600">Esto tomará solo unos segundos.</p>
            </div>
        </div>
    </div>
    <div class="gafa-safari-soft-surface pointer-events-auto ml-auto flex h-full w-full max-w-none flex-col border-l border-zinc-200 bg-white/95 shadow-xl backdrop-blur">
        <div class="flex items-center justify-between px-8 py-4 border-b border-[#E5E7EB] bg-[#F8FAFC]">
            <h2 class="text-base font-semibold text-zinc-900">Tu compra</h2>
            <button type="button" class="rounded-full p-1.5 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700" data-close-buy-panel aria-label="Cerrar panel de compra">
                <span class="text-lg leading-none">×</span>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto px-8 py-5 space-y-5">
            <div class="rounded-2xl border border-zinc-200/70 bg-white p-4" data-buy-step="1">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">PASO 1</p>
                <p class="mt-2 text-sm font-semibold text-zinc-900">¿Tus gafitas llevan fórmula?</p>
                <p class="mt-1 text-xs text-zinc-600">Es decir, si necesitas que las gafas tengan la medida que te dio el optómetra para ayudarte a ver mejor.</p>

                <div class="mt-3 grid gap-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 hover:bg-zinc-50">
                        <input type="radio" name="gafitas_formula" value="no_sin_formula" form="buyPanelPayForm" class="mt-1 h-4 w-4 rounded border-zinc-300" data-buy-step1 required <?php echo e(in_array(old('gafitas_formula'), ['no_sin_formula', 'sin_aumento_neutro'], true) ? 'checked' : ''); ?> />
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold">No, sin fórmula</span>
                            <span class="mt-0.5 block text-xs text-zinc-600">Quiero lentes sin aumento, para estilo, descanso o protección</span>
                        </span>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 hover:bg-zinc-50">
                        <input type="radio" name="gafitas_formula" value="si_con_formula" form="buyPanelPayForm" class="mt-1 h-4 w-4 rounded border-zinc-300" data-buy-step1 required <?php echo e(old('gafitas_formula') === 'si_con_formula' ? 'checked' : ''); ?> />
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold">Sí, con fórmula</span>
                            <span class="mt-0.5 block text-xs text-zinc-600">Tengo una fórmula médica y necesito lentes con mi medida</span>
                        </span>
                    </label>

                </div>

                <?php $__errorArgs = ['gafitas_formula'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-2 text-xs font-semibold text-rose-700"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="rounded-2xl border border-zinc-200/70 bg-white p-4 hidden" data-buy-step="2">
                <?php
                    $gafasFormulas = \App\Services\GafasFormulasContent::load();
                    $step2LensModulesMap = [
                        'con_aumento_monofocal' => [
                            'value' => 'con_aumento_monofocal',
                            'title' => 'Monofocal',
                            'tag' => 'Uso diario',
                            'description' => $gafasFormulas['mono_description'] ?? 'Son lentes con una sola fórmula en toda la superficie, diseñados para ver bien a una sola distancia: de lejos o de cerca. Son la opción más común, ideales para el uso diario, ofreciendo una visión clara, cómoda y sin complicaciones.',
                        ],
                        'progresivos' => [
                            'value' => 'progresivos',
                            'title' => 'Progresivos',
                            'tag' => 'Todo en uno',
                            'description' => $gafasFormulas['progresivo_description'] ?? 'Son lentes que tienen varias graduaciones en un mismo lente, lo que permite ver bien a diferentes distancias sin necesidad de cambiar de gafas. En la parte superior sirven para ver de lejos, en la zona media para distancias intermedias como el computador, y en la parte inferior para leer o ver de cerca. No tienen líneas visibles y la transición entre cada distancia es suave y natural.',
                        ],
                        'ocupacional' => [
                            'value' => 'ocupacional',
                            'title' => 'Ocupacionales',
                            'tag' => 'Pantalla y oficina',
                            'description' => $gafasFormulas['ocupacional_description'] ?? 'Son lentes diseñados para actividades específicas como trabajar en computador, oficina o lectura prolongada. Tienen una graduación que permite ver con mayor comodidad a distancias intermedias y cercanas, reduciendo el esfuerzo visual durante largas jornadas. No están pensados para ver de lejos, sino para brindar descanso y enfoque en el trabajo diario.',
                        ],
                        'bifocal' => [
                            'value' => 'bifocal',
                            'title' => 'Bifocales',
                            'tag' => 'Lejos + cerca',
                            'description' => $gafasFormulas['bifocal_description'] ?? 'Son lentes que tienen dos graduaciones en un mismo lente, una para ver de lejos y otra para ver de cerca. La parte superior se usa para visión lejana y en la parte inferior hay un pequeño segmento visible que permite leer o ver de cerca. Son una opción práctica para quienes necesitan ambas correcciones, aunque el cambio entre distancias no es gradual.',
                        ],
                    ];
                    $step2LensModules = array_values(array_intersect_key($step2LensModulesMap, array_flip($allowedLensDesigns)));

                    $step2MaterialModules = [
                        [
                            'value' => 'nara_basica_156',
                            'title' => $polyEnabled ? 'Nara POLY 1.59' : 'Nara Basica 1.56',
                            'description' => 'Son lentes con una sola fórmula en toda la superficie, diseñados para ver bien a una sola distancia: de lejos o de cerca. Son la opción más común, ideales para el uso diario, ofreciendo una visión clara, cómoda y sin complicaciones.',
                            'active' => true,
                        ],
                        [
                            'value' => 'nara_premium_160',
                            'title' => 'Nara Premium 1.60',
                            'description' => 'Son lentes fabricados en un material de mayor tecnología que permite obtener gafas más delgadas, livianas y estéticas. A diferencia de los lentes básicos, reducen el grosor del lente, haciendo que se vean mejor en cualquier montura y sean más cómodos durante todo el día.Son ideales para fórmulas medias y para quienes buscan un equilibrio perfecto entre estética, comodidad y calidad.',
                            'active' => false,
                        ],
                        [
                            'value' => 'alto_indice_167',
                            'title' => 'Nara Alto indice 1.67',
                            'description' => 'Son lentes fabricados con un material que permite reducir el grosor frente a los lentes tradicionales, ofreciendo una mejor estética y mayor comodidad. Son una opción intermedia dentro de los altos índices, ideales para personas con fórmulas medias que quieren lentes más delgados sin ir a las gamas más altas.',
                            'active' => false,
                        ],
                        [
                            'value' => 'alto_indice_177',
                            'title' => 'Nara Alto indice 1.77',
                            'description' => 'Son lentes de alta tecnología diseñados para lograr la máxima delgadez y ligereza, incluso en fórmulas altas. Reducen significativamente el grosor del lente, mejorando la apariencia de las gafas y haciendo que sean mucho más cómodas de usar.',
                            'active' => false,
                        ],
                        [
                            'value' => 'transitions',
                            'title' => 'Transitions®',
                            'description' => 'Son lentes de marca que se adaptan automáticamente a la luz. En interiores son completamente transparentes y al exponerse al sol se oscurecen, funcionando como gafas de sol. Están fabricados con tecnología avanzada que reacciona a los rayos UV, brindando protección, comodidad y practicidad en un solo lente.',
                            'active' => false,
                        ],
                    ];

                    $step2ProtectionModules = [
                        [
                            'value' => 'blanco',
                            'title' => 'Blanco',
                            'description' => 'Son lentes con una sola fórmula en toda la superficie, diseñados para ver bien a una sola distancia: de lejos o de cerca. Son la opción más común, ideales para el uso diario, ofreciendo una visión clara, cómoda y sin complicaciones.',
                            'active' => false,
                        ],
                        [
                            'value' => 'ar_azul',
                            'title' => 'AR Azul',
                            'description' => 'Son lentes con tratamiento antirreflejo que incluye un filtro de luz azul, ideal para quienes pasan mucho tiempo frente a pantallas. Reducen reflejos molestos y ayudan a disminuir la fatiga visual, brindando mayor comodidad durante el día.',
                            'active' => false,
                        ],
                        [
                            'value' => 'ar_verde',
                            'title' => 'AR Verde',
                            'description' => 'Son lentes con tratamiento antirreflejo que mejora la claridad visual y la transparencia del lente, reduciendo reflejos de la luz natural y artificial. Son ideales para una visión más nítida y una mejor apariencia estética.',
                            'active' => false,
                        ],
                        [
                            'value' => 'ar_azul_fotocromatico_blue_block',
                            'title' => 'AR azul + Fotocromatico + Blue Block',
                            'description' => 'Son lentes completos que combinan varias tecnologías en uno solo. Reducen reflejos, filtran la luz azul de pantallas y además se oscurecen con el sol, adaptándose automáticamente a la luz. Ofrecen protección total, comodidad y practicidad en cualquier ambiente.',
                            'active' => false,
                        ],
                        [
                            'value' => 'ar_verde_fotocromatico_blue_block',
                            'title' => 'AR verde + Fotocromatico + Blue Block',
                            'description' => 'Son lentes de alta calidad que combinan antirreflejo para mayor claridad visual, filtro de luz azul y tecnología fotocromática. Permiten ver con mayor nitidez, proteger los ojos y adaptarse a diferentes condiciones de luz. Son ideales para quienes buscan rendimiento, estética y protección en un solo lente.',
                            'active' => true,
                        ],
                    ];

                    $selectedStep2Material = (string) old('step2_material', request('step2_material', ''));
                    $selectedStep2Protection = (string) old('step2_protection', request('step2_protection', ''));
                ?>

                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">PASO 2</p>
                <p class="mt-2 text-[1.05rem] font-bold tracking-tight text-zinc-900 sm:text-[1.15rem]">¿Qué tipo de lente necesitas?</p>
                <p class="mt-1 text-sm leading-6 text-zinc-600">El lente puede ser diferente dependiendo de cómo necesites ver.</p>

                <div class="mt-4 space-y-3">
                    <?php
                        $gafasFormulas = \App\Services\GafasFormulasContent::load();
                        $moduleIconFor = function ($value) use ($gafasFormulas) {
                            if ($value === 'con_aumento_monofocal') return $gafasFormulas['mono_icon'] ?? asset('images/lente.png');
                            if ($value === 'bifocal') return $gafasFormulas['bifocal_icon'] ?? asset('images/lente.png');
                            if ($value === 'ocupacional') return $gafasFormulas['ocupacional_icon'] ?? asset('images/lente.png');
                            if ($value === 'progresivos') return $gafasFormulas['progresivo_icon'] ?? asset('images/lente.png');
                            return $gafasFormulas['default_lente'] ?? asset('images/lente.png');
                        };
                    ?>
                    <?php $__currentLoopData = $step2LensModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="block cursor-pointer">
                            <input
                                type="radio"
                                name="tipo_lente_necesitas"
                                value="<?php echo e($module['value']); ?>"
                                form="buyPanelPayForm"
                                class="peer sr-only"
                                data-buy-step2
                                required
                                <?php echo e(old('tipo_lente_necesitas') === $module['value'] ? 'checked' : ''); ?>

                            />

                            <div class="relative flex items-center gap-4 overflow-hidden rounded-[1.7rem] border border-zinc-300/80 bg-white p-4 transition duration-200 hover:-translate-y-0.5 hover:border-zinc-800 hover:shadow-md peer-checked:border-zinc-800 peer-checked:bg-[#111827]/10 peer-checked:shadow-md sm:gap-5 sm:p-5">
                                <div class="flex h-20 w-24 shrink-0 items-center justify-center sm:h-24 sm:w-28">
                                    <img src="<?php echo e($moduleIconFor($module['value'])); ?>" alt="Lente" loading="lazy" class="h-16 w-20 object-contain sm:h-20 sm:w-24" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-lg font-black tracking-tight text-zinc-900 sm:text-[1.15rem]"><?php echo e($module['title']); ?></span>
                                    </div>
                                    <p class="mt-1.5 text-sm leading-6 text-zinc-600" style="text-align: justify;"><?php echo e($module['description']); ?></p>
                                </div>

                                <span class="absolute right-4 top-4 inline-flex h-5 w-5 items-center justify-center rounded-full border border-zinc-300 bg-white transition peer-checked:border-zinc-800 peer-checked:bg-[#111827]">
                                    <span class="h-2 w-2 rounded-full bg-white"></span>
                                </span>
                            </div>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="mt-7 hidden border-t border-zinc-100 pt-5" data-step2-material-wrap>
                    <p class="text-center text-sm font-black italic tracking-tight text-zinc-900">Material del lente</p>
                    <p class="mt-1 text-center text-sm leading-6 text-zinc-600">Los lentes pueden tener un tratamiento especial que reduce los reflejos y protege tus ojos</p>

                    <div class="mt-4 space-y-3">
                        <?php $__currentLoopData = $step2MaterialModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button
                                type="button"
                                class="group block w-full rounded-[1.7rem] border border-zinc-300/80 bg-white text-left transition duration-200 hover:-translate-y-0.5 hover:border-zinc-800 hover:shadow-md <?php echo e($selectedStep2Material === $module['value'] ? 'ring-2 ring-zinc-800 ring-offset-2 ring-offset-white' : ''); ?>"
                                data-step2-material-card="<?php echo e($module['value']); ?>"
                                aria-pressed="<?php echo e($selectedStep2Material === $module['value'] ? 'true' : 'false'); ?>"
                            >
                                <div class="flex items-center gap-4 p-4 sm:gap-5 sm:p-5">
                                    <div class="flex h-20 w-24 shrink-0 items-center justify-center sm:h-24 sm:w-28">
                                        <img src="<?php echo e(asset('images/material.png')); ?>" alt="Material del lente" loading="lazy" class="h-16 w-20 object-contain sm:h-20 sm:w-24" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-[1.05rem] font-black tracking-tight text-zinc-900 sm:text-[1.12rem]"><?php echo e($module['title']); ?></p>
                                        <p class="mt-1.5 text-sm leading-6 text-zinc-600"><?php echo e($module['description']); ?></p>
                                    </div>
                                </div>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <div class="mt-7 hidden border-t border-zinc-100 pt-5" data-step2-protection-wrap>
                    <p class="text-center text-sm font-black italic tracking-tight text-zinc-900">¿Quieres protección para tus ojos?</p>
                    <p class="mt-1 text-center text-sm leading-6 text-zinc-600">Los lentes pueden tener un tratamiento especial que reduce los reflejos y protege tus ojos</p>

                    <div class="mt-4 space-y-3">
                        <?php $__currentLoopData = $step2ProtectionModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button
                                type="button"
                                class="group block w-full rounded-[1.7rem] border border-zinc-300/80 bg-white text-left transition duration-200 hover:-translate-y-0.5 hover:border-zinc-800 hover:shadow-md <?php echo e($selectedStep2Protection === $module['value'] ? 'ring-2 ring-zinc-800 ring-offset-2 ring-offset-white' : ''); ?>"
                                data-step2-protection-card="<?php echo e($module['value']); ?>"
                                aria-pressed="<?php echo e($selectedStep2Protection === $module['value'] ? 'true' : 'false'); ?>"
                            >
                                <div class="flex items-center gap-4 p-4 sm:gap-5 sm:p-5">
                                    <div class="flex h-20 w-24 shrink-0 items-center justify-center sm:h-24 sm:w-28">
                                        <img src="<?php echo e(asset('images/material.png')); ?>" alt="Material del lente" loading="lazy" class="h-16 w-20 object-contain sm:h-20 sm:w-24" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-[1.05rem] font-black tracking-tight text-zinc-900 sm:text-[1.12rem]"><?php echo e($module['title']); ?></p>
                                        <p class="mt-1.5 text-sm leading-6 text-zinc-600"><?php echo e($module['description']); ?></p>
                                    </div>
                                </div>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <?php $__errorArgs = ['tipo_lente_necesitas'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-2 text-xs font-semibold text-rose-700"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="rounded-2xl border border-zinc-200/70 bg-white p-4 hidden" data-buy-step="3">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">PASO 3</p>
                <p class="mt-2 text-sm font-semibold text-zinc-900">Escribir tu fórmula manualmente</p>

                <p class="mt-1 text-[11px] text-zinc-600">Esfera: valores positivos o negativos. Cilindro: normalmente en negativo. Eje: de 0 a 180.</p>

                <div class="mt-4 space-y-4">
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50/60 p-4">
                        <div class="mb-3 rounded-2xl bg-zinc-100 px-3 py-2">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-900">Ojo derecho</p>
                            <p class="text-[10px] text-zinc-600">OD</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-1 block text-[11px] font-semibold text-zinc-600">Esfera</span>
                                <input type="hidden" name="rx_od_esfera" form="buyPanelPayForm" value="<?php echo e(old('rx_od_esfera', '')); ?>">
                                <button type="button" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-zinc-300 flex items-center justify-between gap-2 text-left" data-rx-picker data-rx-picker-for="rx_od_esfera" data-rx-picker-type="esfera" data-rx-picker-eye="derecho (OD)">
                                    <span><?php echo e(old('rx_od_esfera') ?: 'Seleccionar'); ?></span>
                                    <svg class="h-4 w-4 flex-shrink-0 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-[11px] font-semibold text-zinc-600">Cilindro</span>
                                <input type="hidden" name="rx_od_cilindro" form="buyPanelPayForm" value="<?php echo e(old('rx_od_cilindro', '')); ?>">
                                <button type="button" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-zinc-300 flex items-center justify-between gap-2 text-left" data-rx-picker data-rx-picker-for="rx_od_cilindro" data-rx-picker-type="cilindro" data-rx-picker-eye="derecho (OD)">
                                    <span><?php echo e(old('rx_od_cilindro') ?: 'Seleccionar'); ?></span>
                                    <svg class="h-4 w-4 flex-shrink-0 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-[11px] font-semibold text-zinc-600">Eje</span>
                                <input type="hidden" name="rx_od_eje" form="buyPanelPayForm" value="<?php echo e(old('rx_od_eje', '')); ?>">
                                <button type="button" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-zinc-300 flex items-center justify-between gap-2 text-left" data-rx-picker data-rx-picker-for="rx_od_eje" data-rx-picker-type="eje" data-rx-picker-eye="derecho (OD)">
                                    <span><?php echo e(old('rx_od_eje') ?: 'Seleccionar'); ?></span>
                                    <svg class="h-4 w-4 flex-shrink-0 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50/60 p-4">
                        <div class="mb-3 rounded-2xl bg-zinc-100 px-3 py-2">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-900">Ojo izquierdo</p>
                            <p class="text-[10px] text-zinc-600">OI</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="block">
                                <span class="mb-1 block text-[11px] font-semibold text-zinc-600">Esfera</span>
                                <input type="hidden" name="rx_oi_esfera" form="buyPanelPayForm" value="<?php echo e(old('rx_oi_esfera', '')); ?>">
                                <button type="button" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-zinc-300 flex items-center justify-between gap-2 text-left" data-rx-picker data-rx-picker-for="rx_oi_esfera" data-rx-picker-type="esfera" data-rx-picker-eye="izquierdo (OI)">
                                    <span><?php echo e(old('rx_oi_esfera') ?: 'Seleccionar'); ?></span>
                                    <svg class="h-4 w-4 flex-shrink-0 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-[11px] font-semibold text-zinc-600">Cilindro</span>
                                <input type="hidden" name="rx_oi_cilindro" form="buyPanelPayForm" value="<?php echo e(old('rx_oi_cilindro', '')); ?>">
                                <button type="button" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-zinc-300 flex items-center justify-between gap-2 text-left" data-rx-picker data-rx-picker-for="rx_oi_cilindro" data-rx-picker-type="cilindro" data-rx-picker-eye="izquierdo (OI)">
                                    <span><?php echo e(old('rx_oi_cilindro') ?: 'Seleccionar'); ?></span>
                                    <svg class="h-4 w-4 flex-shrink-0 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-[11px] font-semibold text-zinc-600">Eje</span>
                                <input type="hidden" name="rx_oi_eje" form="buyPanelPayForm" value="<?php echo e(old('rx_oi_eje', '')); ?>">
                                <button type="button" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-zinc-300 flex items-center justify-between gap-2 text-left" data-rx-picker data-rx-picker-for="rx_oi_eje" data-rx-picker-type="eje" data-rx-picker-eye="izquierdo (OI)">
                                    <span><?php echo e(old('rx_oi_eje') ?: 'Seleccionar'); ?></span>
                                    <svg class="h-4 w-4 flex-shrink-0 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-4" data-rx-adicion-wrap>
                    <label class="block">
                        <span class="mb-1 block text-[11px] font-semibold text-zinc-600">Adición (ambos ojos)</span>
                        <input type="hidden" name="rx_od_adicion" form="buyPanelPayForm" value="<?php echo e(old('rx_od_adicion', old('rx_oi_adicion', ''))); ?>">
                        <input type="hidden" name="rx_oi_adicion" form="buyPanelPayForm" value="<?php echo e(old('rx_oi_adicion', old('rx_od_adicion', ''))); ?>">
                        <button type="button" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm text-zinc-900 outline-none focus:border-zinc-300 flex items-center justify-between gap-2 text-left" data-rx-picker data-rx-picker-for="rx_od_adicion" data-rx-picker-type="adicion" data-rx-picker-eye="derecho e izquierdo">
                            <span><?php echo e(old('rx_od_adicion', old('rx_oi_adicion')) ?: 'Seleccionar'); ?></span>
                            <svg class="h-4 w-4 flex-shrink-0 text-zinc-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </label>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-zinc-700">DNP</label>
                        <input name="rx_distancia_pupilar" value="<?php echo e(old('rx_distancia_pupilar', old('rx_od_dnp', old('rx_oi_dnp', '')))); ?>" form="buyPanelPayForm" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" inputmode="decimal" min="0" data-buy-step3-dnp />
                        <?php $__errorArgs = ['rx_distancia_pupilar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-xs font-semibold text-rose-700"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-zinc-700">Año de nacimiento</label>
                        <input name="rx_ano_nacimiento" value="<?php echo e(old('rx_ano_nacimiento')); ?>" form="buyPanelPayForm" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" data-buy-step3-year />
                        <?php $__errorArgs = ['rx_ano_nacimiento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-xs font-semibold text-rose-700"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            </div>

            <div class="space-y-5 hidden" data-buy-step="4">
                <div class="rounded-2xl border border-zinc-200/70 bg-zinc-50/60 p-4">
                    <p class="text-sm font-semibold text-zinc-900"><?php echo e($producto->nombre); ?></p>
                    <p class="mt-1 text-xs text-zinc-600" data-buy-step4-intro>Selecciona el tipo de lente que quieres para estas gafas.</p>
                </div>

                <div class="rounded-2xl border border-zinc-200/70 bg-white p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500" data-buy-step4-title>PASO 4</p>
                    <p class="mt-2 text-sm font-semibold text-zinc-900" data-buy-step4-question>Selecciona tu tipo de lente</p>
                    <p class="mt-1 text-xs text-zinc-600" data-buy-step4-help>Los lentes pueden tener un tratamiento especial que reduce los reflejos y protege tus ojos</p>
                </div>

                <?php
                    $formulas = \App\Services\GafasFormulasContent::load();
                    $blancoDescription = $formulas['bifocal_blanco_description'];
                    $arAzulDescription = $formulas['bifocal_ar_azul_description'];
                    $arVerdeDescription = $formulas['bifocal_ar_verde_description'];
                    $arAzulFotocromaticoDescription = $formulas['bifocal_ar_azul_foto_blue_description'];
                    $arVerdeFotocromaticoDescription = $formulas['bifocal_ar_verde_foto_blue_description'];
                    $transitionsDescription = $formulas['bifocal_159_transitions_description'];
                    $bifocal159BlancoDescription = $formulas['bifocal_159_blanco_description'];
                    $bifocal159ArVerdeDescription = $formulas['bifocal_159_ar_verde_description'];
                    $bifocal159BlueBlockDescription = $formulas['bifocal_159_blue_block_description'];
                    $bifocal159FotoArBlueDescription = $formulas['bifocal_159_foto_ar_blue_description'];
                    $bifocal159ArVerdeFotoBlueDescription = $formulas['bifocal_159_ar_verde_foto_blue_description'];

                    $bifocalDisplayCards = [
                        '156_blanco' => [
                            'title' => 'Blanco',
                            'description' => $blancoDescription,
                        ],
                        '156_blue_block' => [
                            'title' => 'AR Azul',
                            'description' => $arAzulDescription,
                        ],
                        '156_ar_verde' => [
                            'title' => 'AR Verde',
                            'description' => $arVerdeDescription,
                        ],
                        '156_fotocromatico_superhidrofobico' => [
                            'title' => 'AR azul + Fotocromatico + Blue Block',
                            'description' => $arAzulFotocromaticoDescription,
                        ],
                        '156_ar_verde_fotocromatico_blue_block' => [
                            'title' => 'AR verde + Fotocromatico + Blue Block',
                            'description' => $arVerdeFotocromaticoDescription,
                        ],
                        '159_transitions_gens' => [
                            'title' => $lensTypeOptionsBifocal['159_transitions_gens'] ?? 'Transitions®',
                            'description' => $transitionsDescription,
                        ],
                        '159_bifocal_blanco' => [
                            'title' => $lensTypeOptionsBifocal['159_bifocal_blanco'] ?? 'Bifocal 1.59 Blanco',
                            'description' => $bifocal159BlancoDescription,
                        ],
                        '159_bifocal_ar_verde' => [
                            'title' => $lensTypeOptionsBifocal['159_bifocal_ar_verde'] ?? 'Bifocal 1.59 AR Verde',
                            'description' => $bifocal159ArVerdeDescription,
                        ],
                        '159_bifocal_blue_block' => [
                            'title' => $lensTypeOptionsBifocal['159_bifocal_blue_block'] ?? 'Bifocal 1.59 Blue Block',
                            'description' => $bifocal159BlueBlockDescription,
                        ],
                        '159_bifocal_fotocromatico_superhidrofobico' => [
                            'title' => $lensTypeOptionsBifocal['159_bifocal_fotocromatico_superhidrofobico'] ?? 'Bifocal 1.59 Fotocromático + AR + Blue Block',
                            'description' => $bifocal159FotoArBlueDescription,
                        ],
                        '159_bifocal_ar_verde_fotocromatico_blue_block' => [
                            'title' => $lensTypeOptionsBifocal['159_bifocal_ar_verde_fotocromatico_blue_block'] ?? 'Bifocal 1.59 AR verde + Fotocromático + Blue Block',
                            'description' => $bifocal159ArVerdeFotoBlueDescription,
                        ],
                    ];

                    foreach ($lensTypeOptionsBifocal as $key => $label) {
                        if (!array_key_exists($key, $bifocalDisplayCards)) {
                            $description = match (true) {
                                str_contains($key, 'ar_verde_fotocromatico_blue_block') => $arVerdeFotocromaticoDescription,
                                str_contains($key, 'fotocromatico_superhidrofobico') => $arAzulFotocromaticoDescription,
                                str_contains($key, 'blue_block') => $arAzulDescription,
                                str_contains($key, 'ar_verde') => $arVerdeDescription,
                                str_contains($key, 'blanco') => $blancoDescription,
                                str_contains($key, 'transitions') => $transitionsDescription,
                                default => '',
                            };

                            $bifocalDisplayCards[$key] = [
                                'title' => $label,
                                'description' => $description,
                            ];
                        }
                    }
                ?>

                <div class="space-y-2 hidden" data-buy-lens-select-wrap aria-hidden="true">
                    <label class="sr-only" for="buyPanelLensType">Tipo de lente</label>
                    <select id="buyPanelLensType" class="sr-only" tabindex="-1" data-buy-lens-type>
                        <?php $__currentLoopData = $lensTypeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php echo e((string) $selectedLensType === (string) $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <?php
                    $naraBasicaDisplayCards = [
                        '156_blanco' => $bifocalDisplayCards['156_blanco'],
                        '156_blue_block' => $bifocalDisplayCards['156_blue_block'],
                        '156_ar_verde' => $bifocalDisplayCards['156_ar_verde'],
                        '156_fotocromatico_superhidrofobico' => $bifocalDisplayCards['156_fotocromatico_superhidrofobico'],
                        '156_ar_verde_fotocromatico_blue_block' => $bifocalDisplayCards['156_ar_verde_fotocromatico_blue_block'],
                    ];
                    $naraPremiumDisplayCards = [
                        '160_premium' => [
                            'title' => 'Fotocromatico + AR azul + Blue Block',
                            'description' => $arAzulFotocromaticoDescription,
                        ],
                    ];
                    $altoIndiceDisplayCards = [
                        '1.67' => [
                            '167_blue_block' => [
                                'title' => 'AR azul',
                                'description' => $arAzulDescription,
                            ],
                            '167_ar_verde' => [
                                'title' => 'AR Verde',
                                'description' => $arVerdeDescription,
                            ],
                            '167_ar_azul_fotocromatico_blue_block' => [
                                'title' => 'AR azul + Fotocromatico + Blue Block',
                                'description' => $arAzulFotocromaticoDescription,
                            ],
                            '167_ar_verde_fotocromatico_blue_block' => [
                                'title' => 'AR verde + Fotocromatico + Blue Block',
                                'description' => $arVerdeFotocromaticoDescription,
                            ],
                        ],
                        '1.74' => [
                            '174_blue_block' => [
                                'title' => 'AR azul',
                                'description' => $arAzulDescription,
                            ],
                            '174_ar_verde' => [
                                'title' => 'AR Verde',
                                'description' => $arVerdeDescription,
                            ],
                            '174_ar_azul_fotocromatico_blue_block' => [
                                'title' => 'AR azul + Fotocromatico + Blue Block',
                                'description' => $arAzulFotocromaticoDescription,
                            ],
                            '174_ar_verde_fotocromatico_blue_block' => [
                                'title' => 'AR verde + Fotocromatico + Blue Block',
                                'description' => $arVerdeFotocromaticoDescription,
                            ],
                        ],
                    ];
                    $resolveFormulaCardIcon = function (string $key) use ($formulas) {
                        $icon = $formulas['bifocal_icon'] ?? $formulas['default_lente'] ?? null;

                        if ($key === '160_premium') {
                            return $formulas['bifocal_ar_azul_foto_blue_icon'] ?? $icon;
                        }

                        if (str_contains($key, '159')) {
                            if (str_contains($key, 'blanco')) {
                                return $formulas['bifocal_159_blanco_icon'] ?? $icon;
                            }

                            if (str_contains($key, 'ar_verde_fotocromatico') || str_contains($key, 'ar_verde_fotocromatico_blue_block')) {
                                return $formulas['bifocal_159_ar_verde_foto_blue_icon'] ?? $icon;
                            }

                            if (str_contains($key, 'fotocromatico_superhidrofobico')) {
                                return $formulas['bifocal_159_foto_ar_blue_icon'] ?? $icon;
                            }

                            if (str_contains($key, 'foto') && str_contains($key, 'ar')) {
                                return $formulas['bifocal_159_foto_ar_blue_icon'] ?? $icon;
                            }

                            if (str_contains($key, 'blue_block')) {
                                return $formulas['bifocal_159_blue_block_icon'] ?? $icon;
                            }

                            if (str_contains($key, 'transitions')) {
                                return $formulas['bifocal_159_transitions_icon'] ?? $icon;
                            }

                            if (str_contains($key, 'ar_verde')) {
                                return $formulas['bifocal_159_ar_verde_icon'] ?? $icon;
                            }

                            return $icon;
                        }

                        if (str_contains($key, 'blanco')) {
                            return $formulas['bifocal_blanco_icon'] ?? $icon;
                        }

                        if (str_contains($key, 'ar_verde_fotocromatico') || str_contains($key, 'ar_verde_fotocromatico_blue_block')) {
                            return $formulas['bifocal_ar_verde_foto_blue_icon'] ?? $icon;
                        }

                        if (str_contains($key, 'fotocromatico_superhidrofobico')) {
                            return $formulas['bifocal_ar_azul_foto_blue_icon'] ?? $icon;
                        }

                        if (str_contains($key, 'foto') && str_contains($key, 'ar')) {
                            return $formulas['bifocal_ar_azul_foto_blue_icon'] ?? $icon;
                        }

                        if (str_contains($key, 'blue_block')) {
                            return $formulas['bifocal_ar_azul_icon'] ?? $icon;
                        }

                        if (str_contains($key, 'ar_verde')) {
                            return $formulas['bifocal_ar_verde_icon'] ?? $icon;
                        }

                        return $icon;
                    };
                ?>

                <div class="space-y-3 hidden" data-buy-bifocal-cards-wrap>
                    <p class="text-xs font-semibold text-zinc-700">Tipo de lente</p>
                    <div class="space-y-3">
                        <?php $__currentLoopData = $bifocalDisplayCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $icon = $resolveFormulaCardIcon($key);
                            ?>
                            <button
                                type="button"
                                data-buy-bifocal-lens="<?php echo e($key); ?>"
                                aria-pressed="<?php echo e((string) $selectedLensType === (string) $key ? 'true' : 'false'); ?>"
                                class="w-full rounded-[30px] border border-zinc-300 bg-white p-4 text-left shadow-sm transition hover:border-zinc-400 hover:bg-zinc-50"
                            >
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <?php if(!empty($icon)): ?>
                                        <div class="hidden sm:flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full border border-zinc-300 bg-zinc-50">
                                            <img src="<?php echo e($icon); ?>" alt="<?php echo e($card['title']); ?>" class="h-10 w-10 object-contain" loading="lazy">
                                        </div>
                                    <?php else: ?>
                                        <div class="hidden sm:flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full border border-zinc-300 bg-zinc-50 text-zinc-400">
                                            <svg class="h-10 w-10" viewBox="0 0 80 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <ellipse cx="40" cy="24" rx="29" ry="16" stroke="currentColor" stroke-width="2.5"/>
                                                <path d="M11 24C11 35.0457 24.4315 44 40 44C55.5685 44 69 35.0457 69 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-lg font-bold leading-tight text-zinc-900"><?php echo e($card['title']); ?></p>
                                        <?php if(filled($card['description'])): ?>
                                            <p class="mt-1 text-sm leading-6 text-zinc-700" style="text-align: justify;"><?php echo e($card['description']); ?></p>
                                        <?php else: ?>
                                            <div class="mt-2 min-h-[52px] rounded-2xl border border-dashed border-zinc-200 bg-zinc-50/80"></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <div class="space-y-2 hidden" data-buy-ocupacional-wrap>
                    <p class="text-xs font-semibold text-zinc-700">Ocupacional 1.56</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" data-buy-ocupacional="156_blue_block" class="rounded-2xl border border-zinc-800 bg-[#111827] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f2937]">
                            AR azul
                        </button>
                        <button type="button" data-buy-ocupacional="156_ar_verde" class="rounded-2xl border border-zinc-800 bg-[#111827] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f2937]">
                            AR Verde
                        </button>
                    </div>
                </div>

                <div class="space-y-3 hidden" data-buy-monofocal-wrap>
                    <div class="rounded-2xl border border-zinc-200/70 bg-white p-4" data-buy-plano-rx-wrap>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold text-zinc-700">ESFERA/CILINDRO</p>
                                <p class="mt-1 text-[11px] text-zinc-600" data-buy-plano-rx-help>Como no subes PDF en “Sin aumento NEUTRO”, elige los valores de ambos ojos para calcular el precio. La esfera puede ser positiva o negativa y el cilindro se maneja en negativo.</p>
                            </div>
                            <div class="rounded-2xl bg-rose-50 px-3 py-2 text-[11px] text-zinc-700 sm:max-w-[180px] sm:text-right">
                                <p class="font-semibold text-zinc-900" data-buy-plano-rx-summary>Máximo actual: Esfera 0.00, Cilindro 0.00</p>
                                <p class="mt-0.5" data-buy-plano-rx-tier-label>Rango calculado automáticamente</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div class="hidden grid-cols-[96px_minmax(0,1fr)_minmax(0,1fr)] gap-3 md:grid">
                                <div class="rounded-2xl bg-zinc-100 px-3 py-2 text-xs font-semibold text-zinc-600">OJO</div>
                                <div class="rounded-2xl bg-zinc-100 px-3 py-2 text-xs font-semibold text-zinc-600">Esfera</div>
                                <div class="rounded-2xl bg-zinc-100 px-3 py-2 text-xs font-semibold text-zinc-600">Cilindro</div>
                            </div>

                            <div class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-3 md:border-0 md:bg-transparent md:p-0">
                                <div class="grid gap-3 md:grid-cols-[96px_minmax(0,1fr)_minmax(0,1fr)] md:items-center">
                                    <div class="rounded-2xl bg-zinc-100 px-3 py-3 md:py-2">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-900">Ojo derecho</p>
                                        <p class="text-[10px] text-zinc-600">OD</p>
                                    </div>
                                    <label class="block">
                                        <span class="mb-1 block text-[11px] font-semibold text-zinc-600 md:hidden">Esfera</span>
                                        <select name="plano_rx_od_esfera" form="buyPanelPayForm" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-zinc-400" data-buy-plano-rx-manual="sphere_od">
                                            <?php $__currentLoopData = $planoNeutralSphereOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($option['value']); ?>" <?php echo e(old('plano_rx_od_esfera', '0.00') === $option['value'] ? 'selected' : ''); ?>><?php echo e($option['label']); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="mb-1 block text-[11px] font-semibold text-zinc-600 md:hidden">Cilindro</span>
                                        <select name="plano_rx_od_cilindro" form="buyPanelPayForm" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-zinc-400" data-buy-plano-rx-manual="cyl_od">
                                            <?php $__currentLoopData = $planoNeutralCylinderOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($option['value']); ?>" <?php echo e(old('plano_rx_od_cilindro', '0.00') === $option['value'] ? 'selected' : ''); ?>><?php echo e($option['label']); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </label>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-zinc-200 bg-zinc-50/70 p-3 md:border-0 md:bg-transparent md:p-0">
                                <div class="grid gap-3 md:grid-cols-[96px_minmax(0,1fr)_minmax(0,1fr)] md:items-center">
                                    <div class="rounded-2xl bg-zinc-100 px-3 py-3 md:py-2">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-zinc-900">Ojo izquierdo</p>
                                        <p class="text-[10px] text-zinc-600">OI</p>
                                    </div>
                                    <label class="block">
                                        <span class="mb-1 block text-[11px] font-semibold text-zinc-600 md:hidden">Esfera</span>
                                        <select name="plano_rx_oi_esfera" form="buyPanelPayForm" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-zinc-400" data-buy-plano-rx-manual="sphere_oi">
                                            <?php $__currentLoopData = $planoNeutralSphereOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($option['value']); ?>" <?php echo e(old('plano_rx_oi_esfera', '0.00') === $option['value'] ? 'selected' : ''); ?>><?php echo e($option['label']); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="mb-1 block text-[11px] font-semibold text-zinc-600 md:hidden">Cilindro</span>
                                        <select name="plano_rx_oi_cilindro" form="buyPanelPayForm" class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-zinc-400" data-buy-plano-rx-manual="cyl_oi">
                                            <?php $__currentLoopData = $planoNeutralCylinderOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($option['value']); ?>" <?php echo e(old('plano_rx_oi_cilindro', '0.00') === $option['value'] ? 'selected' : ''); ?>><?php echo e($option['label']); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <button type="button" class="w-full rounded-2xl border border-zinc-800 bg-[#111827] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f2937]" data-buy-monofocal-category="nara_basica_156"><?php echo e($polyEnabled ? 'Nara POLY 1.59' : 'Nara Basica 1.56'); ?></button>
                        <button type="button" class="w-full rounded-2xl border border-zinc-800 bg-[#111827] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f2937]" data-buy-monofocal-category="nara_premium_160">Nara Premium 1.60</button>
                        <button type="button" class="w-full rounded-2xl border border-zinc-800 bg-[#111827] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f2937]" data-buy-monofocal-category="alto_indice">Alto indice</button>
                        <button type="button" class="w-full rounded-2xl border border-zinc-800 bg-[#111827] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f2937]" data-buy-monofocal-category="transition">Transition</button>
                    </div>

                    <div class="hidden rounded-2xl border border-zinc-200/70 bg-white p-4" data-buy-monofocal-options="nara_basica_156">
                        <p class="text-xs font-semibold text-zinc-700">Seleccionar</p>
                        <div class="mt-3 space-y-3">
                            <?php $__currentLoopData = $naraBasicaDisplayCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $icon = $resolveFormulaCardIcon($key);
                                ?>
                                <button
                                    type="button"
                                    data-buy-monofocal-lens="<?php echo e($key); ?>"
                                    aria-pressed="<?php echo e((string) $selectedLensType === (string) $key ? 'true' : 'false'); ?>"
                                    class="w-full rounded-[30px] border border-zinc-300 bg-white p-4 text-left shadow-sm transition hover:border-zinc-400 hover:bg-zinc-50"
                                >
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                        <?php if(!empty($icon)): ?>
                                            <div class="hidden sm:flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full border border-zinc-300 bg-zinc-50">
                                                <img src="<?php echo e($icon); ?>" alt="<?php echo e($card['title']); ?>" class="h-10 w-10 object-contain" loading="lazy">
                                            </div>
                                        <?php else: ?>
                                            <div class="hidden sm:flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full border border-zinc-300 bg-zinc-50 text-zinc-400">
                                                <svg class="h-10 w-10" viewBox="0 0 80 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <ellipse cx="40" cy="24" rx="29" ry="16" stroke="currentColor" stroke-width="2.5"/>
                                                    <path d="M11 24C11 35.0457 24.4315 44 40 44C55.5685 44 69 35.0457 69 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-lg font-bold leading-tight text-zinc-900"><?php echo e($card['title']); ?></p>
                                            <?php if(filled($card['description'])): ?>
                                                <p class="mt-1 text-sm leading-6 text-zinc-700" style="text-align: justify;"><?php echo e($card['description']); ?></p>
                                            <?php else: ?>
                                                <div class="mt-2 min-h-[52px] rounded-2xl border border-dashed border-zinc-200 bg-zinc-50/80"></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="hidden rounded-2xl border border-zinc-200/70 bg-white p-4" data-buy-monofocal-options="nara_premium_160">
                        <p class="text-xs font-semibold text-zinc-700">Seleccionar</p>
                        <div class="mt-3 space-y-3">
                            <?php $__currentLoopData = $naraPremiumDisplayCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $icon = $resolveFormulaCardIcon($key);
                                ?>
                                <button
                                    type="button"
                                    data-buy-monofocal-lens="<?php echo e($key); ?>"
                                    aria-pressed="<?php echo e((string) $selectedLensType === (string) $key ? 'true' : 'false'); ?>"
                                    class="w-full rounded-[30px] border border-zinc-300 bg-white p-4 text-left shadow-sm transition hover:border-zinc-400 hover:bg-zinc-50"
                                >
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                        <?php if(!empty($icon)): ?>
                                            <div class="hidden sm:flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full border border-zinc-300 bg-zinc-50">
                                                <img src="<?php echo e($icon); ?>" alt="<?php echo e($card['title']); ?>" class="h-10 w-10 object-contain" loading="lazy">
                                            </div>
                                        <?php else: ?>
                                            <div class="hidden sm:flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full border border-zinc-300 bg-zinc-50 text-zinc-400">
                                                <svg class="h-10 w-10" viewBox="0 0 80 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <ellipse cx="40" cy="24" rx="29" ry="16" stroke="currentColor" stroke-width="2.5"/>
                                                    <path d="M11 24C11 35.0457 24.4315 44 40 44C55.5685 44 69 35.0457 69 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-lg font-bold leading-tight text-zinc-900"><?php echo e($card['title']); ?></p>
                                            <?php if(filled($card['description'])): ?>
                                                <p class="mt-1 text-sm leading-6 text-zinc-700" style="text-align: justify;"><?php echo e($card['description']); ?></p>
                                            <?php else: ?>
                                                <div class="mt-2 min-h-[52px] rounded-2xl border border-dashed border-zinc-200 bg-zinc-50/80"></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="hidden rounded-2xl border border-zinc-200/70 bg-white p-4" data-buy-monofocal-options="alto_indice">
                        <div class="space-y-4">
                            <?php $__currentLoopData = $altoIndiceDisplayCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionTitle => $cards): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div>
                                    <p class="text-sm font-semibold text-zinc-900"><?php echo e($sectionTitle); ?></p>
                                    <div class="mt-2 space-y-3">
                                        <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $icon = $resolveFormulaCardIcon($key);
                                            ?>
                                            <button
                                                type="button"
                                                data-buy-monofocal-lens="<?php echo e($key); ?>"
                                                aria-pressed="<?php echo e((string) $selectedLensType === (string) $key ? 'true' : 'false'); ?>"
                                                class="w-full rounded-[30px] border border-zinc-300 bg-white p-4 text-left shadow-sm transition hover:border-zinc-400 hover:bg-zinc-50"
                                            >
                                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                    <?php if(!empty($icon)): ?>
                                                        <div class="hidden sm:flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full border border-zinc-300 bg-zinc-50">
                                                            <img src="<?php echo e($icon); ?>" alt="<?php echo e($card['title']); ?>" class="h-10 w-10 object-contain" loading="lazy">
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="hidden sm:flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full border border-zinc-300 bg-zinc-50 text-zinc-400">
                                                            <svg class="h-10 w-10" viewBox="0 0 80 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                                <ellipse cx="40" cy="24" rx="29" ry="16" stroke="currentColor" stroke-width="2.5"/>
                                                                <path d="M11 24C11 35.0457 24.4315 44 40 44C55.5685 44 69 35.0457 69 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                                            </svg>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-lg font-bold leading-tight text-zinc-900"><?php echo e($card['title']); ?></p>
                                                        <?php if(filled($card['description'])): ?>
                                                            <p class="mt-1 text-sm leading-6 text-zinc-700" style="text-align: justify;"><?php echo e($card['description']); ?></p>
                                                        <?php else: ?>
                                                            <div class="mt-2 min-h-[52px] rounded-2xl border border-dashed border-zinc-200 bg-zinc-50/80"></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="hidden rounded-2xl border border-zinc-200/70 bg-white p-4" data-buy-monofocal-options="transition">
                        <button type="button" class="w-full rounded-2xl border border-zinc-800 bg-[#111827] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f2937]" data-buy-monofocal-lens="159_transitions_gens">Seleccionar</button>
                    </div>
                </div>

                <div class="space-y-3 hidden" data-buy-noformula-wrap>
                    <div class="rounded-2xl border border-zinc-200/70 bg-white p-4">
                        <p class="text-xs font-semibold text-zinc-700">Seleccionar</p>
                        <?php
                            $noFormulaButtonLabels = \App\Services\Gafas\GafaLensPricing::lensTypeLabelsForNoFormula($polyEnabled);
                            $noFormulaButtonOrder = [
                                '156_blanco',
                                '156_blue_block',
                                '156_ar_verde',
                                '156_fotocromatico_superhidrofobico',
                                '156_ar_verde_fotocromatico_blue_block',
                            ];
                        ?>
                        <div class="mt-3 grid gap-2">
                            <?php $__currentLoopData = $noFormulaButtonOrder; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $noFormulaKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button" class="w-full rounded-2xl border border-zinc-800 bg-[#111827] px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1f2937]" data-buy-noformula-lens="<?php echo e($noFormulaKey); ?>"><?php echo e($noFormulaButtonLabels[$noFormulaKey] ?? ($lensTypeOptionsMonofocal[$noFormulaKey] ?? $noFormulaKey)); ?></button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>

                <div data-buy-color-anchor class="hidden" aria-hidden="true"></div>
                <div class="space-y-2 hidden scroll-mt-24 rounded-2xl border border-transparent p-3 transition-all duration-300" data-buy-color-wrap>
                    <label class="text-xs font-semibold text-zinc-700" for="buyPanelLensColor" data-buy-lens-color-label>Color</label>
                    <?php
$selectedLensColor = old('lens_color', request('lens_color'))
?>
                    <select id="buyPanelLensColor" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-800 outline-none focus:border-zinc-400" data-buy-lens-color>
                        <option value="">Seleccionar</option>
                        <option value="Gris" <?php echo e($selectedLensColor === 'Gris' ? 'selected' : ''); ?>>Gris</option>
                        <option value="Marrón" <?php echo e($selectedLensColor === 'Marrón' ? 'selected' : ''); ?>>Marrón</option>
                        <option value="Verde Grafito" <?php echo e($selectedLensColor === 'Verde Grafito' ? 'selected' : ''); ?>>Verde Grafito</option>
                        <option value="Verde esmeralda" <?php echo e($selectedLensColor === 'Verde esmeralda' ? 'selected' : ''); ?>>Verde esmeralda</option>
                        <option value="Zafiro" <?php echo e($selectedLensColor === 'Zafiro' ? 'selected' : ''); ?>>Zafiro</option>
                        <option value="Amatista" <?php echo e($selectedLensColor === 'Amatista' ? 'selected' : ''); ?>>Amatista</option>
                        <option value="Ambar" <?php echo e($selectedLensColor === 'Ambar' ? 'selected' : ''); ?>>Ambar</option>
                        <option value="Rubi" <?php echo e($selectedLensColor === 'Rubi' ? 'selected' : ''); ?>>Rubi</option>
                    </select>
                    <p class="hidden rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 shadow-sm" data-buy-color-error>
                        Elige el color de tus gafas.
                    </p>
                </div>

                <div class="space-y-3" data-buy-nara-wrap>
                    <p class="text-xs font-semibold uppercase tracking-wider text-sky-800/80" data-buy-nara-title>Categoría NARA</p>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <button type="button" data-buy-nara="basica" class="group flex flex-col items-center rounded-2xl border border-zinc-200/70 bg-white px-3 py-3 text-center text-xs font-semibold text-zinc-700 shadow-sm transition hover:border-zinc-800 hover:bg-[#111827]/10">
                            <div class="mb-2 h-14 w-16 rounded-full bg-center bg-cover shadow-inner" style="background-image: url('<?php echo e($formulas['nara_basica'] ?? $formulas['default_lente']); ?>');"></div>
                            <span class="text-[11px] font-bold text-sky-900">NARA</span>
                            <span class="text-[11px] text-sky-800" data-buy-nara-label>BÁSICA</span>
                            <div class="mt-1 text-[11px]">
                                <span class="text-amber-400">★★★★</span><span class="text-zinc-300">☆</span>
                            </div>
                        </button>

                        <button type="button" data-buy-nara="media" class="group flex flex-col items-center rounded-2xl border border-zinc-200/70 bg-white px-3 py-3 text-center text-xs font-semibold text-zinc-700 shadow-sm transition hover:border-zinc-800 hover:bg-[#111827]/10">
                            <div class="mb-2 h-14 w-16 rounded-full bg-center bg-cover shadow-inner" style="background-image: url('<?php echo e($formulas['nara_media'] ?? $formulas['default_lente']); ?>');"></div>
                            <span class="text-[11px] font-bold text-sky-900">NARA</span>
                            <span class="text-[11px] text-sky-800" data-buy-nara-label>MEDIA</span>
                            <div class="mt-1 text-[11px]">
                                <span class="text-amber-400">★★★★</span><span class="text-zinc-300">☆</span>
                            </div>
                        </button>

                        <button type="button" data-buy-nara="alta" class="group flex flex-col items-center rounded-2xl border border-zinc-200/70 bg-white px-3 py-3 text-center text-xs font-semibold text-zinc-700 shadow-sm transition hover:border-zinc-800 hover:bg-[#111827]/10">
                            <div class="mb-2 h-14 w-16 rounded-full bg-center bg-cover shadow-inner" style="background-image: url('<?php echo e($formulas['nara_alta'] ?? $formulas['default_lente']); ?>');"></div>
                            <span class="text-[11px] font-bold text-sky-900">NARA</span>
                            <span class="text-[11px] text-sky-800" data-buy-nara-label>ALTA</span>
                            <div class="mt-1 text-[11px] text-amber-400">★★★★★</div>
                        </button>

                        <?php if(!$polyEnabled): ?>
                        <button type="button" data-buy-nara="premium" class="group flex flex-col items-center rounded-2xl border border-zinc-200/70 bg-white px-3 py-3 text-center text-xs font-semibold text-zinc-700 shadow-sm transition hover:border-zinc-800 hover:bg-[#111827]/10">
                            <div class="mb-2 h-14 w-16 rounded-full bg-center bg-cover shadow-inner" style="background-image: url('<?php echo e($formulas['nara_premium'] ?? $formulas['default_lente']); ?>');"></div>
                            <span class="text-[11px] font-bold text-sky-900">NARA</span>
                            <span class="text-[11px] text-sky-800" data-buy-nara-label>PREMIUM</span>
                            <div class="mt-1 text-[11px] text-amber-400">★★★★★</div>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
$matrixInfo = \App\Services\Gafas\GafaLensPricing::matrix()
?>
                <div class="space-y-4 hidden" data-buy-progresivos-presets>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-700" data-buy-progresivos-presets-title>
                        Selecciona tu lente progresivo
                    </p>
                    <div class="rounded-2xl border border-zinc-800 bg-[#111827] p-4 text-white" data-buy-progresivo-card="156_blanco">
                        <p class="text-xs font-semibold text-white" data-buy-progresivo-card-title><?php echo e($polyEnabled ? 'Nara POLY 1.59 Blanco' : 'Lente Nara Basico 1.56'); ?></p>
                        <p class="mt-2 text-xs text-white/90"><?php echo e($polyEnabled ? 'Lente progresivo Nara POLY 1.59 blanco. Permite ver de lejos, intermedio y de cerca en un solo lente.' : 'Lente progresivo blanco 1.56, la opción más sencilla. Permite ver de lejos, intermedio y de cerca en un solo lente. Ideal si buscas una solución funcional y económica'); ?></p>
                        <button type="button" class="mt-3 w-full rounded-2xl border border-white/70 bg-white px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-zinc-800 transition hover:bg-white/90" data-buy-lens-preset="156_blanco">SELECCIONAR</button>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-[#111827] p-4 text-white" data-buy-progresivo-card="156_fotocromatico_superhidrofobico">
                        <p class="text-xs font-semibold text-white" data-buy-progresivo-card-title><?php echo e($polyEnabled ? 'Nara POLY 1.59 Fotocromatico' : 'Lente nara 1.56'); ?></p>
                        <p class="mt-2 text-xs text-white/90"><?php echo e($polyEnabled ? 'Lente progresivo Nara POLY 1.59 fotocromático: claro en interiores y oscuro al sol. Incluye antirreflejo, filtro azul y capa hidrofóbica.' : 'Lente progresivo fotocromático que se adapta a la luz: claro en interiores y oscuro al sol. Incluye antireflejo, filtro de luz azul y capa hidrofóbica, que ayuda a reducir reflejos, proteger tus ojos y mantener el lente más limpio'); ?></p>
                        <button type="button" class="mt-3 w-full rounded-2xl border border-white/70 bg-white px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-zinc-800 transition hover:bg-white/90" data-buy-lens-preset="156_fotocromatico_superhidrofobico">SELECCIONAR</button>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-[#111827] p-4 text-white" data-buy-progresivo-card="160_premium">
                        <p class="text-xs font-semibold text-white" data-buy-progresivo-card-title>Lente Premium 1.60</p>
                        <p class="mt-2 text-xs text-white/90">Lente progresivo premium con todas las tecnologías: fotocromático, antireflejo, filtro azul y tratamiento hidrofóbico. Ofrece mayor comodidad visual, mejor claridad y más protección especialmente durante todo el día.</p>
                        <button type="button" class="mt-3 w-full rounded-2xl border border-white/70 bg-white px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-zinc-800 transition hover:bg-white/90" data-buy-lens-preset="160_premium">SELECCIONAR</button>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-[#111827] p-4 text-white" data-buy-progresivo-card="159_transitions_gens">
                        <p class="text-xs font-semibold text-white" data-buy-progresivo-card-title><?php echo e($polyEnabled ? 'Nara POLY 1.59 Transitions' : 'Transition'); ?></p>
                        <p class="mt-2 text-xs text-white/90">Lente progresivo con tecnología Transitions, que se adapta automáticamente a la luz para proteger tus ojos en interiores y exteriores. Incluye antireflejo, filtro azul y tratamiento hidrofóbico, brindando máxima protección y calidad visual.</p>
                        <button type="button" class="mt-3 w-full rounded-2xl border border-white/70 bg-white px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-zinc-800 transition hover:bg-white/90" data-buy-lens-preset="159_transitions_gens">SELECCIONAR</button>
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-200/70 bg-white p-4" data-buy-pdf-wrap>
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500" data-buy-pdf-title>Fórmula (PDF)</p>
                    <p class="mt-2 text-xs text-zinc-600">Obligatorio: sube tu receta óptica en PDF para continuar.</p>
                    <input type="file" name="prescription_pdf" form="buyPanelPayForm" accept="application/pdf" class="mt-3 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-2.5 text-xs text-zinc-700 file:mr-3 file:rounded-xl file:border-0 file:bg-zinc-800 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-zinc-900" data-buy-pdf-input />
                    <p class="mt-2 text-xs font-semibold text-rose-700 hidden" data-buy-pdf-size-error>El PDF es demasiado grande. Máximo permitido: 20MB. Comprime el archivo e intenta de nuevo.</p>
                    <?php $__errorArgs = ['prescription_pdf'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-xs font-semibold text-rose-700"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>
        <div class="border-t border-zinc-200/70 bg-white/90 px-6 py-4 space-y-3">
            <div class="flex flex-col gap-1 text-xs text-zinc-700">
                <div class="flex items-center justify-between">
                    <span>Precio montura</span>
                    <span id="buyPanelBasePrice" class="font-semibold"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Precio lentes</span>
                    <span id="buyPanelLensPrice" class="font-semibold"></span>
                </div>
                <div class="-mt-0.5 space-y-0.5">
                    <p id="buyPanelLensDetail" class="text-[11px] text-zinc-500"></p>
                    <p id="buyPanelColorDetail" class="text-[11px] text-zinc-500"></p>
                </div>
                <div class="flex items-center justify-between border-t border-dashed border-zinc-200 pt-2 mt-1">
                    <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-900">Total estimado</span>
                    <span id="buyPanelTotal" class="text-sm font-semibold text-zinc-900"></span>
                </div>
            </div>

            <form id="buyPanelPayForm" action="<?php echo e(route('gafas.prescription.checkout', ['producto' => $producto->slug])); ?>" method="POST" enctype="multipart/form-data" class="space-y-3">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="no_prescription" value="0" data-buy-no-prescription />
                <input type="hidden" name="plano_neutro" value="0" data-buy-plano-neutro />
                <input type="hidden" name="plano_rx_sphere_max" value="<?php echo e(e((string) old('plano_rx_sphere_max', request('plano_rx_sphere_max', '0.00')))); ?>" data-buy-plano-rx-sphere />
                <input type="hidden" name="plano_rx_cyl_max" value="<?php echo e(e((string) old('plano_rx_cyl_max', request('plano_rx_cyl_max', '0.00')))); ?>" data-buy-plano-rx-cyl />
                <input type="hidden" name="lens_type" value="<?php echo e(e((string) $selectedLensType)); ?>" data-buy-lens-type-input />
                <input type="hidden" name="nara_level" value="<?php echo e(e((string) $selectedNaraLevel)); ?>" data-buy-nara-input />
                <input type="hidden" name="step2_material" value="<?php echo e(e((string) old('step2_material', request('step2_material')))); ?>" data-step2-material-input />
                <input type="hidden" name="step2_protection" value="<?php echo e(e((string) old('step2_protection', request('step2_protection')))); ?>" data-step2-protection-input />
                <input type="hidden" name="frame_color" value="<?php echo e(e((string) ($colorLabel ?? ($producto->color ?? '')))); ?>" data-buy-frame-color-input />
                <input type="hidden" name="lens_color" value="<?php echo e(e((string) old('lens_color', request('lens_color')))); ?>" data-buy-lens-color-input <?php echo e((string) $selectedLensType === '159_transitions_gens' ? '' : 'disabled'); ?> />
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-[#111827] px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#1f2937] focus:outline-none focus:ring-2 focus:ring-zinc-500">
                    Continuar al pago
                </button>
            </form>
            <button type="button" data-close-buy-panel class="inline-flex w-full items-center justify-center rounded-2xl border border-zinc-200 bg-white px-4 py-2.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">
                Cancelar
            </button>
        </div>
    </div>
    
</div>

<script>
    (() => {
        const primaryImg = document.getElementById('gafaPrimaryImg');
        const primaryBlur = document.getElementById('gafaPrimaryBlur');
        const prevImageBtn = document.getElementById('gafaPrevImage');
        const nextImageBtn = document.getElementById('gafaNextImage');
        const thumbsWrap = document.getElementById('gafaThumbsWrap');
        const thumbsContainer = document.getElementById('gafaThumbs');
        const colorLabel = document.getElementById('gafaColorLabelValue');
        const colorLabelMobile = document.getElementById('gafaColorLabelValueMobile');
        const mobileColorSection = document.getElementById('gafaMobileColorSection');
        const mobileColorSwatches = document.getElementById('gafaMobileColorSwatches');
        const colorStockLabel = document.getElementById('gafaColorStockValue');
        const frameColorInput = document.querySelector('[data-buy-frame-color-input]');
        const cartFrameColorInput = document.querySelector('[data-cart-frame-color-input]');
        const buyTrigger = document.querySelector('[data-open-buy-panel]') || document.querySelector('[data-direct-polarizada-buy]');
        const cartSubmitButton = document.querySelector('[data-cart-add-form] button[type="submit"]');
        if (!primaryImg) return;

        let buttons = Array.from(document.querySelectorAll('[data-gafa-thumb]'));
        let colorButtons = Array.from(document.querySelectorAll('[data-gafa-color-swatch]'));
        let currentGalleryImages = buttons.map((button) => String(button.getAttribute('data-gafa-thumb') || '').trim()).filter((value, index, all) => value !== '' && all.indexOf(value) === index);
        let currentGalleryIndex = currentGalleryImages.length > 0 ? 0 : -1;
        const buyEnabledLabel = 'Adquiere aquí';
        const cartEnabledLabel = 'Añadir al carrito primero';
        const defaultNavTheme = {
            background: '#57989d',
            foreground: '#f8fafc',
            shadow: 'rgba(87, 152, 157, 0.42)',
        };

        const clamp = (value, min, max) => Math.max(min, Math.min(max, value));

        const parseRgbChannels = (colorValue) => {
            const raw = String(colorValue || '').trim();
            const match = raw.match(/^rgba?\(([^)]+)\)$/i);
            if (!match) return null;

            const channels = match[1]
                .split(',')
                .slice(0, 3)
                .map((part) => Number.parseFloat(part.trim()));

            if (channels.length !== 3 || channels.some((channel) => !Number.isFinite(channel))) {
                return null;
            }

            return channels.map((channel) => clamp(Math.round(channel), 0, 255));
        };

        const getContrastColor = (rgb) => {
            if (!Array.isArray(rgb) || rgb.length !== 3) {
                return defaultNavTheme.foreground;
            }

            const [red, green, blue] = rgb.map((channel) => {
                const normalized = channel / 255;
                return normalized <= 0.03928
                    ? normalized / 12.92
                    : Math.pow((normalized + 0.055) / 1.055, 2.4);
            });
            const luminance = (0.2126 * red) + (0.7152 * green) + (0.0722 * blue);

            return luminance > 0.45 ? '#111827' : '#f8fafc';
        };

        const findColorButtonForImage = (src) => {
            const normalizedSrc = String(src || '').trim();
            if (normalizedSrc === '') {
                return colorButtons.find((button) => button.dataset.active === 'true') || null;
            }

            return colorButtons.find((button) => {
                const mainImage = String(button.getAttribute('data-color-image') || '').trim();
                if (mainImage !== '' && mainImage === normalizedSrc) {
                    return true;
                }

                return parseColorImages(button).includes(normalizedSrc);
            }) || colorButtons.find((button) => button.dataset.active === 'true') || null;
        };

        const applyNavButtonsTheme = (src = '') => {
            const matchedColorButton = findColorButtonForImage(src);
            const computed = matchedColorButton ? window.getComputedStyle(matchedColorButton) : null;
            const background = defaultNavTheme.background;
            const rgb = parseRgbChannels(background);
            const foreground = defaultNavTheme.foreground || getContrastColor(rgb);
            const shadow = defaultNavTheme.shadow;

            [prevImageBtn, nextImageBtn].forEach((button) => {
                if (!button) return;
                button.style.setProperty('--gafa-nav-bg', background || defaultNavTheme.background);
                button.style.setProperty('--gafa-nav-fg', foreground);
                button.style.setProperty('--gafa-nav-shadow', shadow);
            });
        };

        const buildMobileColorSwatches = () => {
            if (!mobileColorSwatches || !mobileColorSection) return;

            const sourceButtons = Array.from(document.querySelectorAll('[data-gafa-color-source="sidebar"] [data-gafa-color-swatch]'));
            mobileColorSwatches.innerHTML = '';

            if (!sourceButtons.length) {
                mobileColorSection.classList.add('hidden');
                colorButtons = Array.from(document.querySelectorAll('[data-gafa-color-swatch]'));
                return;
            }

            sourceButtons.forEach((button) => {
                const clone = button.cloneNode(true);
                clone.setAttribute('data-gafa-color-source', 'mobile');
                mobileColorSwatches.appendChild(clone);
            });

            colorButtons = Array.from(document.querySelectorAll('[data-gafa-color-swatch]'));
        };

        const updateArrowState = () => {
            const showArrows = currentGalleryImages.length > 1;

            [prevImageBtn, nextImageBtn].forEach((button) => {
                if (!button) return;
                button.classList.toggle('hidden', !showArrows);
                button.classList.toggle('flex', showArrows);
            });

            if (prevImageBtn) {
                prevImageBtn.disabled = !showArrows || currentGalleryIndex <= 0;
            }

            if (nextImageBtn) {
                nextImageBtn.disabled = !showArrows || currentGalleryIndex >= currentGalleryImages.length - 1;
            }
        };

        const renderStockState = (rawStock, colorName = '') => {
            const parsed = rawStock !== null && rawStock !== '' ? Number(rawStock) : null;
            const hasValue = parsed !== null && Number.isFinite(parsed);
            const soldOut = hasValue && parsed <= 0;

            if (colorStockLabel) {
                colorStockLabel.classList.remove('text-rose-700', 'text-emerald-700');
                if (!hasValue) {
                    colorStockLabel.textContent = 'Disponible';
                    colorStockLabel.classList.add('text-emerald-700');
                } else {
                    colorStockLabel.textContent = `${Math.max(0, parsed)} disponibles`;
                    colorStockLabel.classList.add(soldOut ? 'text-rose-700' : 'text-emerald-700');
                }
            }

            if (buyTrigger) {
                buyTrigger.disabled = soldOut;
                buyTrigger.textContent = soldOut ? 'Color agotado' : buyEnabledLabel;
                buyTrigger.classList.toggle('opacity-50', soldOut);
                buyTrigger.classList.toggle('cursor-not-allowed', soldOut);
            }

            if (cartSubmitButton) {
                cartSubmitButton.disabled = soldOut;
                cartSubmitButton.textContent = soldOut ? 'Color agotado' : cartEnabledLabel;
                cartSubmitButton.classList.toggle('opacity-60', soldOut);
                cartSubmitButton.classList.toggle('cursor-not-allowed', soldOut);
            }
        };

        const setSrc = (src) => {
            if (!src) return;
            primaryImg.setAttribute('src', src);
            if (primaryBlur) {
                primaryBlur.setAttribute('src', src);
            }
            applyNavButtonsTheme(src);
        };

        const setActive = (activeBtn) => {
            buttons.forEach((b) => {
                b.classList.remove('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
                b.setAttribute('aria-current', 'false');
            });
            if (!activeBtn) return;
            activeBtn.classList.add('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
            activeBtn.setAttribute('aria-current', 'true');
        };

        const goToGalleryIndex = (index) => {
            if (!Array.isArray(currentGalleryImages) || currentGalleryImages.length === 0) {
                currentGalleryIndex = -1;
                updateArrowState();
                return;
            }

            const boundedIndex = Math.max(0, Math.min(currentGalleryImages.length - 1, index));
            currentGalleryIndex = boundedIndex;
            const src = currentGalleryImages[boundedIndex] || '';

            if (src !== '') {
                setSrc(src);
            }

            const activeButton = buttons.find((thumb) => (thumb.getAttribute('data-gafa-thumb') || '') === src) || null;
            if (activeButton) {
                setActive(activeButton);
            }

            updateArrowState();
        };

        const bindThumbButtons = () => {
            buttons = Array.from(document.querySelectorAll('[data-gafa-thumb]'));

            buttons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const src = btn.getAttribute('data-gafa-thumb');
                    const index = currentGalleryImages.findIndex((image) => image === src);
                    if (index >= 0) {
                        goToGalleryIndex(index);
                        return;
                    }

                    setSrc(src);
                    setActive(btn);
                });
            });
        };

        const parseColorImages = (button) => {
            const raw = button ? (button.getAttribute('data-color-images') || '[]') : '[]';

            try {
                const parsed = JSON.parse(raw);
                if (!Array.isArray(parsed)) {
                    return [];
                }

                return parsed
                    .map((value) => String(value || '').trim())
                    .filter((value, index, all) => value !== '' && all.indexOf(value) === index);
            } catch (_) {
                return [];
            }
        };

        const renderThumbButtons = (images = [], preferredSrc = '') => {
            if (!thumbsWrap || !thumbsContainer) return;

            const uniqueImages = images
                .map((value) => String(value || '').trim())
                .filter((value, index, all) => value !== '' && all.indexOf(value) === index);

            thumbsContainer.innerHTML = '';
            thumbsWrap.classList.add('hidden');

            uniqueImages.forEach((src) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'group w-16 shrink-0 overflow-hidden rounded-2xl border border-zinc-200/70 bg-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-zinc-500 sm:w-auto';
                button.setAttribute('data-gafa-thumb', src);
                button.setAttribute('aria-label', 'Ver imagen');

                const wrap = document.createElement('div');
                wrap.className = 'relative aspect-square w-full bg-gradient-to-br from-zinc-50 to-white';

                const image = document.createElement('img');
                image.src = src;
                image.alt = '';
                image.className = 'absolute inset-0 h-full w-full object-cover transition duration-200 group-hover:scale-[1.10] group-hover:saturate-110';

                wrap.appendChild(image);
                button.appendChild(wrap);
                thumbsContainer.appendChild(button);
            });

            currentGalleryImages = uniqueImages;
            currentGalleryIndex = preferredSrc && uniqueImages.includes(preferredSrc)
                ? uniqueImages.indexOf(preferredSrc)
                : (uniqueImages.length > 0 ? 0 : -1);

            bindThumbButtons();

            const activeSrc = preferredSrc && uniqueImages.includes(preferredSrc)
                ? preferredSrc
                : (uniqueImages[0] || '');
            const activeButton = buttons.find((thumb) => (thumb.getAttribute('data-gafa-thumb') || '') === activeSrc) || buttons[0] || null;
            if (activeButton) {
                setActive(activeButton);
            }

            updateArrowState();
        };

        const setActiveColor = (activeBtn) => {
            const activeName = activeBtn ? String(activeBtn.getAttribute('data-color-name') || '').trim().toLowerCase() : '';
            const activeImage = activeBtn ? String(activeBtn.getAttribute('data-color-image') || '').trim() : '';

            colorButtons.forEach((b) => {
                const name = String(b.getAttribute('data-color-name') || '').trim().toLowerCase();
                const image = String(b.getAttribute('data-color-image') || '').trim();
                const isActive = activeName !== '' ? name === activeName : (activeImage !== '' && image === activeImage);
                b.dataset.active = isActive ? 'true' : 'false';
                b.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        };

        if (buttons.length) {
            setActive(buttons[0]);
        }

        bindThumbButtons();

        if (prevImageBtn) {
            prevImageBtn.addEventListener('click', () => {
                goToGalleryIndex(currentGalleryIndex - 1);
            });
        }

        if (nextImageBtn) {
            nextImageBtn.addEventListener('click', () => {
                goToGalleryIndex(currentGalleryIndex + 1);
            });
        }

        buildMobileColorSwatches();

        colorButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const src = btn.getAttribute('data-color-image');
                const name = btn.getAttribute('data-color-name') || '';
                const images = parseColorImages(btn);

                if (src && !images.includes(src)) {
                    images.unshift(src);
                }

                renderThumbButtons(images, src || images[0] || '');

                if (src) {
                    setSrc(src);
                    const thumbMatch = buttons.find((thumb) => (thumb.getAttribute('data-gafa-thumb') || '') === src);
                    if (thumbMatch) {
                        setActive(thumbMatch);
                    }
                }

                if (colorLabel && name.trim() !== '') {
                    colorLabel.textContent = name;
                }

                if (colorLabelMobile && name.trim() !== '') {
                    colorLabelMobile.textContent = name;
                }

                if (frameColorInput && name.trim() !== '') {
                    frameColorInput.value = name.trim();
                }

                if (cartFrameColorInput && name.trim() !== '') {
                    cartFrameColorInput.value = name.trim();
                }

                renderStockState(btn.getAttribute('data-color-stock'), name.trim());
                setActiveColor(btn);
            });
        });

        const initialActiveColor = colorButtons.find((b) => b.dataset.active === 'true') || colorButtons[0] || null;
        if (initialActiveColor) {
            setActiveColor(initialActiveColor);
            const initialName = (initialActiveColor.getAttribute('data-color-name') || '').trim();
            if (colorLabelMobile && initialName !== '') {
                colorLabelMobile.textContent = initialName;
            }
            if (frameColorInput && initialName !== '') {
                frameColorInput.value = initialName;
            }
            if (cartFrameColorInput && initialName !== '') {
                cartFrameColorInput.value = initialName;
            }
            renderStockState(initialActiveColor.getAttribute('data-color-stock'), initialName);
            const initialSrc = initialActiveColor.getAttribute('data-color-image');
            const initialImages = parseColorImages(initialActiveColor);
            if (initialSrc && !initialImages.includes(initialSrc)) {
                initialImages.unshift(initialSrc);
            }
            renderThumbButtons(initialImages, initialSrc || initialImages[0] || '');
            if (initialSrc) {
                const initialIndex = currentGalleryImages.findIndex((image) => image === initialSrc);
                goToGalleryIndex(initialIndex >= 0 ? initialIndex : 0);
            } else {
                applyNavButtonsTheme('');
            }
        } else {
            applyNavButtonsTheme(primaryImg.getAttribute('src') || '');
        }

        updateArrowState();
    })();
</script>

<?php if(!$formulaPermitida): ?>
<script>
    (() => {
        const directBuyBtn = document.querySelector('[data-direct-polarizada-buy]');
        const directBuyForm = document.getElementById('directPolarizadaBuyForm');
        if (!directBuyBtn || !directBuyForm) return;
        const directFrameColorInput = directBuyForm.querySelector('[data-direct-frame-color]');
        directBuyBtn.addEventListener('click', () => {
            if (directBuyBtn.disabled) return;
            if (directFrameColorInput) {
                const mainFrameColorInput = document.querySelector('[data-buy-frame-color-input]');
                if (mainFrameColorInput) {
                    directFrameColorInput.value = mainFrameColorInput.value;
                }
            }
            directBuyForm.submit();
        });
    })();
</script>
<?php endif; ?>

<script>
    (() => {
        const area = document.getElementById('gafaZoomArea');
        const img = document.getElementById('gafaPrimaryImg');
        const prevImageBtn = document.getElementById('gafaPrevImage');
        const nextImageBtn = document.getElementById('gafaNextImage');
        if (!area || !img) return;

        const baseScaleRaw = img.getAttribute('data-base-scale');
        const baseScaleNum = Number.parseFloat(String(baseScaleRaw || '1'));
        const baseScale = Number.isFinite(baseScaleNum) ? baseScaleNum : 1;

        const defaultOrigin = img.getAttribute('data-default-origin') || '50% 50%';
        const isTouchViewport = window.matchMedia('(hover: none), (pointer: coarse)').matches;

        let isZooming = false;
        let touchZooming = false;
        const hoverFactor = 1.8;
        const touchFactor = 2.1;

        const clamp = (n, min, max) => Math.max(min, Math.min(max, n));
        const isArrowTarget = (target) => {
            if (!(target instanceof Element)) return false;

            return (prevImageBtn && prevImageBtn.contains(target)) || (nextImageBtn && nextImageBtn.contains(target));
        };

        const setZoom = (xPct, yPct, factor = hoverFactor) => {
            img.style.transformOrigin = `${xPct}% ${yPct}%`;
            img.style.transform = `scale(${(baseScale * factor).toFixed(4)})`;
        };

        let zoomMoveHandler = null;

        const resetZoom = () => {
            img.style.transformOrigin = defaultOrigin;
            img.style.transform = `scale(${baseScale})`;
            area.style.touchAction = '';
            if (zoomMoveHandler) area.removeEventListener('touchmove', zoomMoveHandler);
            touchZooming = false;
        };

        area.addEventListener('mouseenter', (e) => {
            if (isArrowTarget(e.target)) {
                isZooming = false;
                area.classList.remove('cursor-zoom-in');
                resetZoom();
                return;
            }

            isZooming = true;
            area.classList.add('cursor-zoom-in');
        });

        area.addEventListener('mouseleave', () => {
            isZooming = false;
            area.classList.remove('cursor-zoom-in');
            resetZoom();
        });

        area.addEventListener('mousemove', (e) => {
            if (isArrowTarget(e.target)) {
                isZooming = false;
                area.classList.remove('cursor-zoom-in');
                resetZoom();
                return;
            }

            if (!isZooming) {
                isZooming = true;
                area.classList.add('cursor-zoom-in');
            }

            const rect = area.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;

            setZoom(clamp(x, 0, 100), clamp(y, 0, 100));
        });

        if (isTouchViewport) {
            const toPercent = (touch) => {
                const rect = area.getBoundingClientRect();
                const x = ((touch.clientX - rect.left) / rect.width) * 100;
                const y = ((touch.clientY - rect.top) / rect.height) * 100;
                return {
                    x: clamp(x, 0, 100),
                    y: clamp(y, 0, 100),
                };
            };

            zoomMoveHandler = (e) => {
                if (!e.touches || e.touches.length !== 1) return;
                e.preventDefault();
                const p = toPercent(e.touches[0]);
                setZoom(p.x, p.y, touchFactor);
            };

            let lastTapAt = 0;
            let lastTapPos = { x: 50, y: 50 };

            area.addEventListener('touchstart', (e) => {
                if (!e.touches || e.touches.length !== 1) return;
                if (touchZooming) {
                    const p = toPercent(e.touches[0]);
                    setZoom(p.x, p.y, touchFactor);
                }
            }, { passive: true });

            area.addEventListener('touchend', (e) => {
                const changed = e.changedTouches && e.changedTouches[0];
                if (!changed) return;

                const now = Date.now();
                const delta = now - lastTapAt;
                lastTapAt = now;

                const p = toPercent(changed);
                lastTapPos = p;

                if (delta > 280) return;

                if (touchZooming) {
                    resetZoom();
                    return;
                }

                touchZooming = true;
                area.style.touchAction = 'none';
                area.addEventListener('touchmove', zoomMoveHandler, { passive: false });
                setZoom(lastTapPos.x, lastTapPos.y, touchFactor);
            }, { passive: true });
        }

        resetZoom();
    })();
</script>

<script>
    (() => {
        const sidebar = document.querySelector('[data-gafa-sidebar]');
        const panel = document.getElementById('buyPanel');
        const backdrop = document.getElementById('buyPanelBackdrop');
        const openButtons = Array.from(document.querySelectorAll('[data-open-buy-panel]'));
        const closeButtons = Array.from(document.querySelectorAll('[data-close-buy-panel]'));
        const basePriceEl = document.getElementById('buyPanelBasePrice');
        const lensPriceEl = document.getElementById('buyPanelLensPrice');
        const totalPriceEl = document.getElementById('buyPanelTotal');
        const lensDetailEl = document.getElementById('buyPanelLensDetail');
        const colorDetailEl = document.getElementById('buyPanelColorDetail');

        const lensTypeSelect = panel ? panel.querySelector('[data-buy-lens-type]') : null;
        const lensTypeSelectWrap = panel ? panel.querySelector('[data-buy-lens-select-wrap]') : null;
        const bifocalCardsWrap = panel ? panel.querySelector('[data-buy-bifocal-cards-wrap]') : null;
        const bifocalButtons = panel ? Array.from(panel.querySelectorAll('[data-buy-bifocal-lens]')) : [];
        const naraButtons = panel ? Array.from(panel.querySelectorAll('[data-buy-nara]')) : [];
        const payForm = document.getElementById('buyPanelPayForm');
        const lensTypeInput = payForm ? payForm.querySelector('[data-buy-lens-type-input]') : null;
        const naraInput = payForm ? payForm.querySelector('[data-buy-nara-input]') : null;
        const step2MaterialInput = payForm ? payForm.querySelector('[data-step2-material-input]') : null;
        const step2ProtectionInput = payForm ? payForm.querySelector('[data-step2-protection-input]') : null;

        const verifyingOverlay = document.getElementById('buyPanelPdfVerifyingOverlay');
        const pdfInput = panel ? panel.querySelector('input[name="prescription_pdf"]') : null;
        const pdfWrap = panel ? panel.querySelector('[data-buy-pdf-wrap]') : null;
        const pdfTitle = pdfWrap ? pdfWrap.querySelector('[data-buy-pdf-title]') : null;
        const payBtn = payForm ? payForm.querySelector('button[type="submit"]') : null;
        const noPrescriptionInput = payForm ? payForm.querySelector('[data-buy-no-prescription]') : null;
        const planoNeutroInput = payForm ? payForm.querySelector('[data-buy-plano-neutro]') : null;

        const planoRxSphereInput = payForm ? payForm.querySelector('[data-buy-plano-rx-sphere]') : null;
        const planoRxCylInput = payForm ? payForm.querySelector('[data-buy-plano-rx-cyl]') : null;

        const colorAnchor = panel ? panel.querySelector('[data-buy-color-anchor]') : null;
        const colorWrap = panel ? panel.querySelector('[data-buy-color-wrap]') : null;
        const lensColorSelect = panel ? panel.querySelector('[data-buy-lens-color]') : null;
        const colorError = panel ? panel.querySelector('[data-buy-color-error]') : null;
        const lensColorInput = payForm ? payForm.querySelector('[data-buy-lens-color-input]') : null;

        const naraWrap = panel ? panel.querySelector('[data-buy-nara-wrap]') : null;
        const naraTitle = naraWrap ? naraWrap.querySelector('[data-buy-nara-title]') : null;
        const naraLabels = naraWrap ? Array.from(naraWrap.querySelectorAll('[data-buy-nara-label]')) : [];
        const step4Intro = panel ? panel.querySelector('[data-buy-step4-intro]') : null;
        const step4Title = panel ? panel.querySelector('[data-buy-step4-title]') : null;
        const step4Question = panel ? panel.querySelector('[data-buy-step4-question]') : null;
        const step4Help = panel ? panel.querySelector('[data-buy-step4-help]') : null;

        const ocupacionalWrap = panel ? panel.querySelector('[data-buy-ocupacional-wrap]') : null;
        const ocupacionalButtons = ocupacionalWrap ? Array.from(ocupacionalWrap.querySelectorAll('[data-buy-ocupacional]')) : [];

        const progresivosPresetsWrap = panel ? panel.querySelector('[data-buy-progresivos-presets]') : null;
        const progresivosPresetButtons = progresivosPresetsWrap ? Array.from(progresivosPresetsWrap.querySelectorAll('[data-buy-lens-preset]')) : [];
        const progresivosPresetsTitle = progresivosPresetsWrap ? progresivosPresetsWrap.querySelector('[data-buy-progresivos-presets-title]') : null;
        const progresivosCardTitles = progresivosPresetsWrap ? Array.from(progresivosPresetsWrap.querySelectorAll('[data-buy-progresivo-card-title]')) : [];

        const monofocalWrap = panel ? panel.querySelector('[data-buy-monofocal-wrap]') : null;
        const noFormulaWrap = panel ? panel.querySelector('[data-buy-noformula-wrap]') : null;
        const monofocalCategoryButtons = monofocalWrap ? Array.from(monofocalWrap.querySelectorAll('[data-buy-monofocal-category]')) : [];
        const monofocalOptionGroups = monofocalWrap ? Array.from(monofocalWrap.querySelectorAll('[data-buy-monofocal-options]')) : [];
        const monofocalLensButtons = monofocalWrap ? Array.from(monofocalWrap.querySelectorAll('[data-buy-monofocal-lens]')) : [];
        const noFormulaLensButtons = noFormulaWrap ? Array.from(noFormulaWrap.querySelectorAll('[data-buy-noformula-lens]')) : [];

        const planoRxWrap = monofocalWrap ? monofocalWrap.querySelector('[data-buy-plano-rx-wrap]') : null;
        const planoRxHelp = monofocalWrap ? monofocalWrap.querySelector('[data-buy-plano-rx-help]') : null;
        const planoRxSummary = monofocalWrap ? monofocalWrap.querySelector('[data-buy-plano-rx-summary]') : null;
        const planoRxTierLabel = monofocalWrap ? monofocalWrap.querySelector('[data-buy-plano-rx-tier-label]') : null;
        const planoRxManualInputs = monofocalWrap ? Array.from(monofocalWrap.querySelectorAll('[data-buy-plano-rx-manual]')) : [];

        let selectedMonofocalCategory = '';
        let selectedStep2Material = step2MaterialInput ? String(step2MaterialInput.value || '') : '';
        let selectedStep2Protection = step2ProtectionInput ? String(step2ProtectionInput.value || '') : '';

        const step1 = panel ? panel.querySelector('[data-buy-step="1"]') : null;
        const step2 = panel ? panel.querySelector('[data-buy-step="2"]') : null;
        const step3 = panel ? panel.querySelector('[data-buy-step="3"]') : null;
        const step4 = panel ? panel.querySelector('[data-buy-step="4"]') : null;
        const step2MaterialWrap = panel ? panel.querySelector('[data-step2-material-wrap]') : null;
        const step2ProtectionWrap = panel ? panel.querySelector('[data-step2-protection-wrap]') : null;
        const step2MaterialCards = panel ? Array.from(panel.querySelectorAll('[data-step2-material-card]')) : [];
        const step2ProtectionCards = panel ? Array.from(panel.querySelectorAll('[data-step2-protection-card]')) : [];
        const step1Radios = panel ? Array.from(panel.querySelectorAll('input[name="gafitas_formula"]')) : [];
        const step2Radios = panel ? Array.from(panel.querySelectorAll('input[name="tipo_lente_necesitas"]')) : [];
        const step3Year = panel ? panel.querySelector('[data-buy-step3-year]') : null;
        const adicionWraps = panel ? Array.from(panel.querySelectorAll('[data-rx-adicion-wrap]')) : [];

        const rxOdSphere = panel ? panel.querySelector('[name="rx_od_esfera"]') : null;
        const rxOdCyl = panel ? panel.querySelector('[name="rx_od_cilindro"]') : null;
        const rxOiSphere = panel ? panel.querySelector('[name="rx_oi_esfera"]') : null;
        const rxOiCyl = panel ? panel.querySelector('[name="rx_oi_cilindro"]') : null;
        const rxOdAxis = panel ? panel.querySelector('[name="rx_od_eje"]') : null;
        const rxOiAxis = panel ? panel.querySelector('[name="rx_oi_eje"]') : null;
        const rxDistanciaPupilar = panel ? panel.querySelector('[name="rx_distancia_pupilar"]') : null;
        const rxOdAdicion = panel ? panel.querySelector('[name="rx_od_adicion"]') : null;
        const rxOiAdicion = panel ? panel.querySelector('[name="rx_oi_adicion"]') : null;

        if (!sidebar || !panel || !backdrop || !openButtons.length || !payForm || !lensTypeSelect || !naraButtons.length) return;

        const basePriceRaw = panel.getAttribute('data-base-price') || '0';
        const currencyLabel = panel.getAttribute('data-currency-label') || '';
        const basePrice = Number.parseFloat(basePriceRaw) || 0;

        const matrixRaw = panel.getAttribute('data-lens-matrix') || '{}';
        let matrix = {};
        try {
            matrix = JSON.parse(matrixRaw);
        } catch {
            matrix = {};
        }

        const monoRaw = panel.getAttribute('data-mono-pricing') || '{}';
        let monoPricing = {};
        try {
            monoPricing = JSON.parse(monoRaw);
        } catch {
            monoPricing = {};
        }

        const bifocalRaw = panel.getAttribute('data-bifocal-pricing') || '{}';
        let bifocalPricing = {};
        try {
            bifocalPricing = JSON.parse(bifocalRaw);
        } catch {
            bifocalPricing = {};
        }

        const ocupacionalRaw = panel.getAttribute('data-ocupacional-pricing') || '{}';
        let ocupacionalPricing = {};
        try {
            ocupacionalPricing = JSON.parse(ocupacionalRaw);
        } catch {
            ocupacionalPricing = {};
        }

        const lensOptionsProgresivosRaw = panel.getAttribute('data-lens-options-progresivos') || '{}';
        const lensOptionsMonofocalRaw = panel.getAttribute('data-lens-options-monofocal') || '{}';
        const lensOptionsBifocalRaw = panel.getAttribute('data-lens-options-bifocal') || '{}';
        const lensOptionsOcupacionalRaw = panel.getAttribute('data-lens-options-ocupacional') || '{}';
        let lensOptionsProgresivos = {};
        let lensOptionsMonofocal = {};
        let lensOptionsBifocal = {};
        let lensOptionsOcupacional = {};
        try {
            lensOptionsProgresivos = JSON.parse(lensOptionsProgresivosRaw) || {};
        } catch {
            lensOptionsProgresivos = {};
        }
        try {
            lensOptionsMonofocal = JSON.parse(lensOptionsMonofocalRaw) || {};
        } catch {
            lensOptionsMonofocal = {};
        }
        try {
            lensOptionsBifocal = JSON.parse(lensOptionsBifocalRaw) || {};
        } catch {
            lensOptionsBifocal = {};
        }
        try {
            lensOptionsOcupacional = JSON.parse(lensOptionsOcupacionalRaw) || {};
        } catch {
            lensOptionsOcupacional = {};
        }

        let selectedLensType = String(panel.getAttribute('data-selected-lens') || '').trim();
        let selectedNara = String(panel.getAttribute('data-selected-nara') || '').trim();
        const isPolyEnabled = String(panel.getAttribute('data-poly-enabled') || '0') === '1';

        const noFormulaLensTypes = noFormulaLensButtons
            .map((btn) => String(btn.getAttribute('data-buy-noformula-lens') || ''))
            .filter((value) => value !== '');
        const noFormulaLensTypeSet = new Set(noFormulaLensTypes);
        let lastFormulaLensType = String(selectedLensType || '');
        let lastNoFormulaLensType = '';

        let currentStep = 0;

        const formatMoney = (value) => {
            if (!Number.isFinite(value)) return '';
            const formatted = value.toLocaleString('es-CO', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });
            return currencyLabel ? `${formatted} ${currencyLabel}` : formatted;
        };

        const isChecked = (inputs) => inputs.some((i) => i && i.checked);
        const getCheckedValue = (inputs) => {
            const el = inputs.find((i) => i && i.checked);
            return el ? String(el.value || '') : '';
        };

        const getTipoLenteNecesitas = () => {
            const v = getCheckedValue(step2Radios);
            return String(v || '');
        };

        const hasStep2Material = () => String(selectedStep2Material || '').trim() !== '';
        const hasStep2Protection = () => String(selectedStep2Protection || '').trim() !== '';

        const getFormulaChoice = () => {
            const v = getCheckedValue(step1Radios);
            return String(v || '');
        };

        const hasValue = (el) => {
            if (!el) return false;
            return String(el.value || '').trim() !== '';
        };

        const isPlanoNeutro = () => getFormulaChoice() === 'sin_aumento_neutro';
        const isNoFormulaSimple = () => getFormulaChoice() === 'no_sin_formula';

        const getMode = () => {
            const t = getTipoLenteNecesitas();
            if (t === 'con_aumento_monofocal') return 'monofocal';
            if (t === 'bifocal') return 'bifocal';
            if (t === 'ocupacional') return 'ocupacional';
            return 'progresivos';
        };

        const getLensOptionMap = (mode) => {
            return mode === 'monofocal'
                ? lensOptionsMonofocal
                : (mode === 'bifocal'
                    ? lensOptionsBifocal
                    : (mode === 'ocupacional'
                        ? lensOptionsOcupacional
                        : lensOptionsProgresivos));
        };

        const getLensLabel = (lensType, mode = getMode()) => {
            const value = String(lensType || '').trim();
            if (value === '') return '';
            const map = getLensOptionMap(mode);
            return String((map && map[value]) ? map[value] : value);
        };

        const hasSelectedLensForMode = (mode = getMode()) => {
            const value = String(selectedLensType || '').trim();
            if (value === '') return false;
            const map = getLensOptionMap(mode);
            return Object.prototype.hasOwnProperty.call(map || {}, value);
        };

        const hasSelectedNara = () => {
            const value = String(selectedNara || '').trim();
            if (value === '') return false;
            return naraButtons.some((btn) => String(btn.getAttribute('data-buy-nara') || '') === value);
        };

        const rebuildLensOptions = (mode) => {
            const map = getLensOptionMap(mode);
            const keys = Object.keys(map || {});
            if (!keys.length) return;

            const current = String(selectedLensType || lensTypeSelect.value || '').trim();
            const requiresManualLensSelection = mode === 'ocupacional' || mode === 'progresivos' || mode === 'bifocal' || mode === 'monofocal';
            lensTypeSelect.innerHTML = '';

            if (requiresManualLensSelection) {
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Selecciona una opción';
                lensTypeSelect.appendChild(placeholder);
            }

            keys.forEach((key) => {
                const opt = document.createElement('option');
                opt.value = key;
                opt.textContent = String(map[key] || key);
                lensTypeSelect.appendChild(opt);
            });

            const next = keys.includes(current)
                ? current
                : (requiresManualLensSelection ? '' : keys[0]);

            lensTypeSelect.value = next;
            if (next === '' && requiresManualLensSelection) {
                lensTypeSelect.selectedIndex = 0;
            }

            selectedLensType = String(next || '');
            panel.setAttribute('data-selected-lens', selectedLensType);
        };

        const setActiveOcupacional = () => {
            if (!ocupacionalButtons.length) return;
            ocupacionalButtons.forEach((btn) => {
                btn.classList.remove('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
                btn.setAttribute('aria-pressed', 'false');
            });
            const active = ocupacionalButtons.find((btn) => String(btn.getAttribute('data-buy-ocupacional') || '') === String(selectedLensType || ''));
            if (!active) return;
            active.classList.add('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
            active.setAttribute('aria-pressed', 'true');
        };

        const syncBifocalCards = () => {
            if (!bifocalButtons.length) return;
            bifocalButtons.forEach((btn) => {
                btn.classList.remove('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
                btn.setAttribute('aria-pressed', 'false');
            });
            const active = bifocalButtons.find((btn) => String(btn.getAttribute('data-buy-bifocal-lens') || '') === String(selectedLensType || ''));
            if (!active) return;
            active.classList.add('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
            active.setAttribute('aria-pressed', 'true');
        };

        const setSelectedLensType = (next) => {
            const value = String(next || '').trim();
            selectedLensType = value;
            panel.setAttribute('data-selected-lens', selectedLensType);
            try {
                lensTypeSelect.value = selectedLensType;
                if (!selectedLensType) {
                    lensTypeSelect.selectedIndex = -1;
                }
            } catch {
                // noop
            }
            syncBifocalCards();
        };

        const setActiveStep2Card = (cards, activeValue) => {
            cards.forEach((card) => {
                card.classList.remove('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
                card.setAttribute('aria-pressed', 'false');
            });

            const value = String(activeValue || '');
            if (!value) return;

            const active = cards.find((card) => {
                const materialValue = String(card.getAttribute('data-step2-material-card') || '');
                const protectionValue = String(card.getAttribute('data-step2-protection-card') || '');
                return materialValue === value || protectionValue === value;
            });

            if (!active) return;
            active.classList.add('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
            active.setAttribute('aria-pressed', 'true');
        };

        const syncStep2ProgressiveUi = () => {
            // PASO 2 solo debe mostrar el tipo de lente que necesita el usuario.
            // El formulario manual debe aparecer enseguida en el PASO 3.
            if (step2MaterialWrap) {
                step2MaterialWrap.classList.add('hidden');
            }

            if (step2ProtectionWrap) {
                step2ProtectionWrap.classList.add('hidden');
            }

            setActiveStep2Card(step2MaterialCards, selectedStep2Material);
            setActiveStep2Card(step2ProtectionCards, selectedStep2Protection);
        };

        const syncNoFormulaLensButtons = () => {
            if (!noFormulaLensButtons.length) return;
            noFormulaLensButtons.forEach((button) => {
                button.classList.remove('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
                button.setAttribute('aria-pressed', 'false');
            });
            const active = noFormulaLensButtons.find((button) => String(button.getAttribute('data-buy-noformula-lens') || '') === String(selectedLensType || ''));
            if (!active) return;
            active.classList.add('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
            active.setAttribute('aria-pressed', 'true');
        };

        const enterNoFormulaFlow = () => {
            if (!noFormulaLensTypes.length) return;

            if (!noFormulaLensTypeSet.has(String(selectedLensType || '')) && selectedLensType) {
                lastFormulaLensType = String(selectedLensType || '');
            }

            const nextLens = noFormulaLensTypeSet.has(String(selectedLensType || ''))
                ? String(selectedLensType || '')
                : (noFormulaLensTypeSet.has(String(lastNoFormulaLensType || ''))
                    ? String(lastNoFormulaLensType || '')
                    : '');

            setSelectedLensType(nextLens);
            lastNoFormulaLensType = String(nextLens || '');
            syncNoFormulaLensButtons();
        };

        const leaveNoFormulaFlow = () => {
            if (noFormulaLensTypeSet.has(String(selectedLensType || ''))) {
                lastNoFormulaLensType = String(selectedLensType || '');
                if (String(lastFormulaLensType || '') !== '') {
                    setSelectedLensType(lastFormulaLensType);
                }
            }
            syncNoFormulaLensButtons();
        };

        const syncProgresivosCards = () => {
            if (!progresivosPresetsWrap) return;
            const cards = Array.from(progresivosPresetsWrap.querySelectorAll('[data-buy-progresivo-card]'));
            cards.forEach((card) => {
                card.classList.remove('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
            });
            const active = cards.find((c) => String(c.getAttribute('data-buy-progresivo-card') || '') === String(selectedLensType || ''));
            if (!active) return;
            active.classList.add('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
        };

        const inferMonofocalCategory = () => {
            const t = String(selectedLensType || '').trim();
            if (!t) return '';
            if (t === '159_transitions_gens') return 'transition';
            if (t === '160_premium') return 'nara_premium_160';
            if (t.startsWith('167_') || t.startsWith('174_')) return 'alto_indice';
            if (t.startsWith('156_')) return 'nara_basica_156';
            return '';
        };

        const setMonofocalCategory = (category) => {
            if (!monofocalWrap) return;
            selectedMonofocalCategory = String(category || '');
            monofocalCategoryButtons.forEach((btn) => {
                btn.classList.remove('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
                btn.setAttribute('aria-pressed', 'false');
            });
            const active = monofocalCategoryButtons.find((b) => String(b.getAttribute('data-buy-monofocal-category') || '') === selectedMonofocalCategory);
            if (active) {
                active.classList.add('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
                active.setAttribute('aria-pressed', 'true');
            }

            monofocalOptionGroups.forEach((g) => {
                g.classList.toggle('hidden', String(g.getAttribute('data-buy-monofocal-options') || '') !== selectedMonofocalCategory);
            });
        };

        const syncMonofocalLensButtons = () => {
            if (!monofocalLensButtons.length) return;
            monofocalLensButtons.forEach((btn) => {
                btn.classList.remove('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
                btn.setAttribute('aria-pressed', 'false');
            });
            const active = monofocalLensButtons.find((b) => String(b.getAttribute('data-buy-monofocal-lens') || '') === String(selectedLensType || ''));
            if (!active) return;
            active.classList.add('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
            active.setAttribute('aria-pressed', 'true');
        };

        const applyMode = () => {
            const mode = getMode();
            const plano = isPlanoNeutro();
            const noFormulaSimple = isNoFormulaSimple();
            if (naraWrap) {
                naraWrap.classList.toggle('hidden', mode !== 'progresivos');
            }

            if (ocupacionalWrap) {
                ocupacionalWrap.classList.toggle('hidden', mode !== 'ocupacional');
            }
            if (lensTypeSelectWrap) {
                lensTypeSelectWrap.classList.add('hidden');
            }
            if (bifocalCardsWrap) {
                bifocalCardsWrap.classList.toggle('hidden', mode !== 'bifocal');
            }
            if (progresivosPresetsWrap) {
                progresivosPresetsWrap.classList.toggle('hidden', mode !== 'progresivos');
            }

            if (monofocalWrap) {
                monofocalWrap.classList.toggle('hidden', mode !== 'monofocal' || noFormulaSimple);
            }
            if (noFormulaWrap) {
                noFormulaWrap.classList.toggle('hidden', !(mode === 'monofocal' && noFormulaSimple));
            }

            if (step4Intro) {
                step4Intro.textContent = mode === 'progresivos'
                    ? 'Selecciona el tipo de lente progresivo digital que quieres para estas gafas.'
                    : (mode === 'monofocal'
                        ? (isNoFormulaSimple()
                            ? 'Selecciona el tratamiento que quieres para tus lentes sin aumento.'
                            : 'Selecciona el tipo de lente monofocal que quieres para estas gafas.')
                        : (mode === 'bifocal'
                            ? 'Selecciona el tipo de lente bifocal que quieres para estas gafas.'
                            : 'Selecciona el tipo de lente ocupacional que quieres para estas gafas.'));
            }
            if (step4Title) {
                step4Title.textContent = mode === 'progresivos'
                    ? 'PASO 4 Progresivos'
                    : (mode === 'monofocal'
                        ? (isNoFormulaSimple() ? 'PASO 4 Sin fórmula' : 'PASO 4 Monofocal')
                        : (mode === 'bifocal'
                            ? 'PASO 4 Bifocal'
                            : 'PASO 4 Ocupacional'));
            }
            if (step4Question) {
                step4Question.textContent = mode === 'progresivos'
                    ? '¿Que gama quieres?'
                    : (mode === 'ocupacional'
                        ? '¿Quieres protección para tus ojos?'
                        : (mode === 'monofocal'
                            ? '¿Quieres protección para tus ojos?'
                            : 'Selecciona tu tipo de lente'));
            }
            if (step4Help) {
                step4Help.textContent = isNoFormulaSimple()
                    ? 'Elige el acabado de tus lentes sin aumento.'
                    : 'Los lentes pueden tener un tratamiento especial que reduce los reflejos y protege tus ojos';
            }

            rebuildLensOptions(mode);
            setActiveOcupacional();
            syncBifocalCards();
            syncProgresivosCards();

            if (mode === 'monofocal' && noFormulaSimple) {
                enterNoFormulaFlow();
            } else {
                leaveNoFormulaFlow();
            }

            if (mode === 'monofocal') {
                const inferredMonofocalCategory = inferMonofocalCategory();
                setMonofocalCategory(selectedMonofocalCategory || inferredMonofocalCategory);
                syncMonofocalLensButtons();
            }

            // Ocultar y deshabilitar Adición en Monofocal para que no se muestre ni dé error
            adicionWraps.forEach((wrap) => {
                const isMonofocal = mode === 'monofocal';
                wrap.classList.toggle('hidden', isMonofocal);
                const hiddenInput = wrap.querySelector('input[type="hidden"]');
                if (hiddenInput) hiddenInput.disabled = isMonofocal;
            });

            applyPlanoRxUi();
            syncStep2ProgressiveUi();
        };

        const formatRxValue = (value) => {
            const n = Number.parseFloat(String(value || '0'));
            if (!Number.isFinite(n)) return '0.00';
            return n.toFixed(2);
        };

        const parseSignedRxValue = (value) => {
            const n = Number.parseFloat(String(value || '').trim().replace(',', '.'));
            return Number.isFinite(n) ? n : 0;
        };

        const getPlanoRxManualValues = () => {
            const getByKey = (key) => {
                const input = planoRxManualInputs.find((el) => String(el.getAttribute('data-buy-plano-rx-manual') || '') === key);
                return input ? parseSignedRxValue(input.value) : 0;
            };

            return {
                odSphere: getByKey('sphere_od'),
                odCyl: getByKey('cyl_od'),
                oiSphere: getByKey('sphere_oi'),
                oiCyl: getByKey('cyl_oi'),
            };
        };

        const getPlanoRx = () => {
            const manual = getPlanoRxManualValues();
            const s = Math.max(Math.abs(manual.odSphere), Math.abs(manual.oiSphere));
            const c = Math.max(Math.abs(manual.odCyl), Math.abs(manual.oiCyl));
            return {
                sphere: Number.isFinite(s) ? Math.max(0, Math.abs(s)) : 0,
                cyl: Number.isFinite(c) ? Math.max(0, Math.abs(c)) : 0,
            };
        };

        const syncPlanoRxHiddenInputs = () => {
            const rx = getPlanoRx();
            if (planoRxSphereInput) planoRxSphereInput.value = formatRxValue(rx.sphere);
            if (planoRxCylInput) planoRxCylInput.value = formatRxValue(rx.cyl);
        };

        const getPlanoRxTierLabel = () => {
            const rx = getPlanoRx();
            const lensType = String(selectedLensType || '');

            if (lensType === '159_transitions_gens') {
                return (rx.sphere <= 2 && rx.cyl <= 2)
                    ? 'Rango activo: menor o igual a 2'
                    : 'Rango activo: mayor a 2';
            }

            if (lensType === '160_premium') {
                return (rx.sphere <= 4 && rx.cyl <= 4)
                    ? 'Rango activo: hasta ±4'
                    : 'Rango activo: mayor a ±4';
            }

            if (String(selectedMonofocalCategory || '') === 'alto_indice') {
                return 'Rango activo: no varía por la fórmula';
            }

            if (rx.sphere <= 3 && rx.cyl <= 3) {
                return 'Rango activo: hasta ±3';
            }
            if (rx.sphere <= 4 && rx.cyl <= 4) {
                return 'Rango activo: hasta ±4';
            }
            return 'Rango activo: después de ±4';
        };

        const updatePlanoRxLabels = () => {
            if (!planoRxWrap) return;

            const rx = getPlanoRx();
            if (planoRxSummary) {
                planoRxSummary.textContent = `Máximo actual: Esfera ${formatRxValue(rx.sphere)}, Cilindro ${formatRxValue(rx.cyl)}`;
            }
            if (planoRxTierLabel) {
                planoRxTierLabel.textContent = getPlanoRxTierLabel();
            }

            if (!planoRxHelp) return;
            if (String(selectedMonofocalCategory || '') === 'alto_indice') {
                planoRxHelp.textContent = 'Ingresa los valores de ambos ojos. En alto índice el precio no cambia por la fórmula, pero dejamos la referencia visual.';
                return;
            }
            if (String(selectedLensType || '') === '159_transitions_gens') {
                planoRxHelp.textContent = 'Para Transition, si esfera y cilindro quedan en 2 o menos toma la tarifa menor. Si alguno supera 2, pasa a la tarifa mayor.';
                return;
            }
            if (String(selectedLensType || '') === '160_premium') {
                planoRxHelp.textContent = 'Para Nara Premium 1.60, si ambos máximos quedan hasta ±4 se usa la primera tarifa. Si alguno supera ±4, se aplica la tarifa mayor.';
                return;
            }
            planoRxHelp.textContent = isPolyEnabled && String(selectedLensType || '').startsWith('156_')
                ? 'Para Nara POLY 1.59, el precio se calcula con el valor absoluto más alto entre ambos ojos. Hasta ±3 en esfera y ±2 en cilindro usa la primera tarifa, hasta ±4 la segunda, hasta ±9 la tercera y después la cuarta.'
                : 'Para Nara Básica 1.56, el precio se calcula con el valor absoluto más alto entre ambos ojos. Hasta ±3 usa la primera tarifa, hasta ±4 la segunda y después de ±4 la tercera.';
        };

        const applyPlanoRxUi = () => {
            const plano = isPlanoNeutro();
            if (planoRxWrap) {
                planoRxWrap.classList.toggle('hidden', !plano);
            }
            if (!plano) return;

            syncPlanoRxHiddenInputs();
            updatePlanoRxLabels();
        };

        const parseRx = (el) => {
            if (!el) return 0;
            const raw = String(el.value || '').trim().replace(',', '.');
            const n = Number.parseFloat(raw);
            return Number.isFinite(n) ? n : 0;
        };

        const getRxMaxAbs = () => {
            if (isPlanoNeutro()) {
                const rx = getPlanoRx();
                if (rx.sphere > 0 || rx.cyl > 0) return rx;
            }
            const odSph = parseRx(rxOdSphere);
            const oiSph = parseRx(rxOiSphere);
            const odCyl = parseRx(rxOdCyl);
            const oiCyl = parseRx(rxOiCyl);
            const sphere = Math.max(Math.abs(odSph), Math.abs(oiSph));
            const cyl = Math.max(Math.abs(odCyl), Math.abs(oiCyl));
            return { sphere, cyl };
        };

        const getMonofocalTier = (lensType, sphereMax, cylMax) => {
            const polyRules = (monoPricing && monoPricing.rules && monoPricing.rules.poly_156) ? monoPricing.rules.poly_156 : null;
            const isPoly156 = isPolyEnabled
                && polyRules
                && !!polyRules.enabled
                && String(lensType || '').startsWith('156_');

            if (isPoly156) {
                if (sphereMax <= 3 && cylMax <= 2) return 1;
                if (sphereMax <= 4 && cylMax <= 4) return 2;
                if (sphereMax <= 9 && cylMax <= 9) return 3;
                return 4;
            }

            if (lensType === '160_premium') {
                return (sphereMax <= 4 && cylMax <= 4) ? 1 : 2;
            }
            if (sphereMax <= 3 && cylMax <= 3) return 1;
            if (sphereMax <= 4 && cylMax <= 4) return 2;
            return 3;
        };

        const getMonofocalPrice = (lensType) => {
            if (!lensType) return 0;
            const fixed = (monoPricing && monoPricing.fixed) ? monoPricing.fixed : {};
            const tiered = (monoPricing && monoPricing.tiered) ? monoPricing.tiered : {};
            const noFormula = (monoPricing && monoPricing.no_formula) ? monoPricing.no_formula : {};

            if (isNoFormulaSimple()) {
                const noFormulaVal = noFormula[lensType];
                if (noFormulaVal !== undefined && noFormulaVal !== null) {
                    const n = Number.parseFloat(String(noFormulaVal || '0'));
                    return Number.isFinite(n) ? n : 0;
                }

                const fixedVal = fixed[lensType];
                if (fixedVal !== undefined && fixedVal !== null) {
                    const n = Number.parseFloat(String(fixedVal || '0'));
                    return Number.isFinite(n) ? n : 0;
                }

                const tiers = tiered[lensType];
                if (tiers) {
                    const baseTier = tiers['1'] ?? tiers[1] ?? 0;
                    const n = Number.parseFloat(String(baseTier || '0'));
                    return Number.isFinite(n) ? n : 0;
                }
            }

            if (lensType === '159_transitions_gens') {
                const rules = (monoPricing && monoPricing.rules && monoPricing.rules.transitions) ? monoPricing.rules.transitions : null;
                const rx = getRxMaxAbs();
                const max = rules ? Number.parseFloat(String(rules.tier1_max ?? '0')) : 2;
                const p1 = rules ? Number.parseFloat(String(rules.price_tier1 ?? '0')) : 475000;
                const p2 = rules ? Number.parseFloat(String(rules.price_tier2 ?? '0')) : 499000;
                const pColor = rules ? Number.parseFloat(String(rules.price_with_color ?? '0')) : 520000;
                const colorVal = lensColorSelect ? String(lensColorSelect.value || '').trim() : '';
                if (colorVal !== '') return Number.isFinite(pColor) ? pColor : 0;
                const useTier1 = rx.sphere <= max && rx.cyl <= max;
                return useTier1 ? (Number.isFinite(p1) ? p1 : 0) : (Number.isFinite(p2) ? p2 : 0);
            }

            const fixedVal = fixed[lensType];
            if (fixedVal !== undefined && fixedVal !== null) {
                const n = Number.parseFloat(String(fixedVal || '0'));
                return Number.isFinite(n) ? n : 0;
            }

            const tiers = tiered[lensType];
            if (!tiers) return 0;
            const rx = getRxMaxAbs();
            const tier = getMonofocalTier(lensType, rx.sphere, rx.cyl);
            const v = tiers[String(tier)] ?? tiers[tier];
            const n = Number.parseFloat(String(v || '0'));
            return Number.isFinite(n) ? n : 0;
        };

        const getFixedPrice = (pricing, lensType) => {
            if (!lensType) return 0;
            const fixed = (pricing && pricing.fixed) ? pricing.fixed : {};
            const v = fixed[lensType];
            const n = Number.parseFloat(String(v || '0'));
            return Number.isFinite(n) ? n : 0;
        };

        const updatePrices = () => {
            const isNoPrescription = !!(noPrescriptionInput && String(noPrescriptionInput.value || '0') === '1');
            const mode = getMode();
            const canQuoteLenses = !isNoPrescription && currentStep >= 4;
            const hasLensSelection = canQuoteLenses && hasSelectedLensForMode(mode);
            const hasNaraSelection = mode !== 'progresivos' || hasSelectedNara();

            const transitionsSelected = hasLensSelection && String(selectedLensType || '') === '159_transitions_gens';
            const mustPickColor = canQuoteLenses && transitionsSelected;

            const syncColorWrapPlacement = () => {
                if (!colorWrap) return;

                // En progresivos, cuando se elige Transition, el selector de color debe quedar
                // justo debajo del card de Transition.
                if (mode === 'progresivos' && mustPickColor) {
                    const transitionsCard = progresivosPresetsWrap
                        ? progresivosPresetsWrap.querySelector('[data-buy-progresivo-card="159_transitions_gens"]')
                        : null;
                    if (transitionsCard) {
                        transitionsCard.insertAdjacentElement('afterend', colorWrap);
                        return;
                    }
                }

                // Si no aplica, devolver a la posición original.
                if (colorAnchor && colorAnchor.parentElement) {
                    colorAnchor.insertAdjacentElement('afterend', colorWrap);
                }
            };

            const syncMissingSelectionStyles = () => {
                // Subrayado rojo para Color cuando es requerido y aún no se ha seleccionado.
                if (colorWrap && lensColorSelect) {
                    const label = colorWrap.querySelector('[data-buy-lens-color-label]');
                    const missingColor = mustPickColor && String(lensColorSelect.value || '').trim() === '';
                    if (label) {
                        label.classList.toggle('gafa-required-underline', missingColor);
                    }
                    lensColorSelect.classList.toggle('gafa-required-border', missingColor);
                }

                // Subrayado rojo para Categoría NARA cuando aplica y no se ha elegido.
                if (naraTitle) {
                    const missingNara = canQuoteLenses && mode === 'progresivos' && !hasSelectedNara();
                    naraTitle.classList.toggle('gafa-required-underline', missingNara);

                    // También subrayar en rojo las opciones (BÁSICA/MEDIA/ALTA/PREMIUM).
                    naraLabels.forEach((label) => {
                        label.classList.toggle('gafa-required-underline', missingNara);
                    });
                }

                // Subrayado rojo para la lista de lentes progresivos cuando no han seleccionado ninguno.
                const missingProgresivosLens = canQuoteLenses && mode === 'progresivos' && !hasSelectedLensForMode(mode);
                if (progresivosPresetsTitle) {
                    progresivosPresetsTitle.classList.toggle('gafa-required-underline', missingProgresivosLens);
                }
                progresivosCardTitles.forEach((title) => {
                    title.classList.toggle('gafa-required-underline', missingProgresivosLens);
                });

                // Subrayado rojo para Fórmula (PDF) cuando aplica y aún no se ha subido archivo.
                if (pdfWrap && pdfInput) {
                    const missingPdf = canQuoteLenses
                        && !isNoPrescription
                        && !isPlanoNeutro()
                        && !isNoFormulaSimple()
                        && !(pdfInput.files && pdfInput.files.length > 0);

                    if (pdfTitle) {
                        pdfTitle.classList.toggle('gafa-required-underline', missingPdf);
                    }

                    pdfInput.classList.toggle('gafa-required-border', missingPdf);
                    pdfWrap.classList.toggle('gafa-required-border', missingPdf);
                }
            };

            if (colorWrap) {
                colorWrap.classList.toggle('hidden', !mustPickColor);
            }

            syncColorWrapPlacement();
            if (lensColorSelect) {
                if (mustPickColor) {
                    lensColorSelect.setAttribute('required', 'required');
                } else {
                    lensColorSelect.removeAttribute('required');
                    try {
                        lensColorSelect.value = '';
                    } catch {
                        // noop
                    }
                }
            }
            if (lensColorInput) {
                const v = lensColorSelect ? String(lensColorSelect.value || '') : '';
                lensColorInput.value = mustPickColor ? v : '';
                lensColorInput.disabled = !mustPickColor;
            }
            if (!mustPickColor) {
                hideColorValidation();
            }

            syncMissingSelectionStyles();

            let lensPrice = 0;
            let lensDetail = '';
            let colorDetail = '';
            if (canQuoteLenses) {
                const mode = getMode();
                if (mode === 'monofocal') {
                    lensPrice = getMonofocalPrice(String(selectedLensType || ''));

                    if (isNoFormulaSimple()) {
                        lensDetail = 'SIN AUMENTO (sin cálculo por fórmula)';
                    } else

                    if (String(selectedLensType || '') === '159_transitions_gens') {
                        const rx = getRxMaxAbs();
                        const rules = (monoPricing && monoPricing.rules && monoPricing.rules.transitions) ? monoPricing.rules.transitions : null;
                        const max = rules ? Number.parseFloat(String(rules.tier1_max ?? '0')) : 2;
                        const useTier1 = rx.sphere <= max && rx.cyl <= max;
                        const colorVal = lensColorSelect ? String(lensColorSelect.value || '').trim() : '';
                        const currentLensLabel = getLensLabel(selectedLensType, mode) || 'Transitions®';
                        if (colorVal !== '') {
                            // Con color seleccionado, el precio final queda fijo y no depende del rango.
                            lensDetail = currentLensLabel;
                            colorDetail = `Color: ${colorVal}`;
                        } else {
                            lensDetail = `${currentLensLabel} · ${useTier1 ? 'ESFERA/CILINDRO: hasta 2' : 'ESFERA/CILINDRO: mayor a 2'}`;
                        }
                    } else {
                        const fixed = (monoPricing && monoPricing.fixed) ? monoPricing.fixed : {};
                        const fixedVal = fixed[String(selectedLensType || '')];
                        const currentLensLabel = getLensLabel(selectedLensType, mode) || 'Lente seleccionado';
                        if (fixedVal !== undefined && fixedVal !== null) {
                            lensDetail = currentLensLabel;
                        } else {
                            const rx = getRxMaxAbs();
                            const lensType = String(selectedLensType || '');
                            const tier = getMonofocalTier(lensType, rx.sphere, rx.cyl);
                            const tierLabel = lensType === '160_premium'
                                ? (tier === 1 ? 'Hasta ±4' : 'Mayor a ±4')
                                : (tier === 1 ? 'Hasta ±3' : (tier === 2 ? 'Hasta ±4' : 'Después de ±4'));
                            lensDetail = `${currentLensLabel} · ESFERA/CILINDRO: ${tierLabel}`;
                        }
                    }
                } else if (mode === 'bifocal') {
                    const bifocalLensType = String(selectedLensType || '');
                    const bifocalTiered = (bifocalPricing && bifocalPricing.tiered) ? bifocalPricing.tiered : {};
                    if (isPolyEnabled && bifocalLensType.startsWith('156_') && bifocalTiered[bifocalLensType]) {
                        const rx = getRxMaxAbs();
                        const tier = getMonofocalTier(bifocalLensType, rx.sphere, rx.cyl);
                        const tierValue = bifocalTiered[bifocalLensType][String(tier)] ?? bifocalTiered[bifocalLensType][tier];
                        lensPrice = Number.parseFloat(String(tierValue || '0')) || 0;
                    } else {
                        lensPrice = getFixedPrice(bifocalPricing, bifocalLensType);
                    }
                    lensDetail = getLensLabel(selectedLensType, mode) || '';
                } else if (mode === 'ocupacional') {
                    lensPrice = getFixedPrice(ocupacionalPricing, String(selectedLensType || ''));
                    lensDetail = '';
                } else {
                    const row = (matrix && selectedLensType) ? matrix[selectedLensType] : null;
                    const raw = row && selectedNara ? row[selectedNara] : 0;
                    lensPrice = Number.parseFloat(String(raw || '0')) || 0;
                    lensDetail = hasNaraSelection ? `NARA ${String(selectedNara || '').toUpperCase()}` : '';
                }
            }

            if (basePriceEl) basePriceEl.textContent = formatMoney(basePrice);
            if (lensPriceEl) lensPriceEl.textContent = formatMoney(lensPrice);
            if (totalPriceEl) totalPriceEl.textContent = formatMoney(basePrice + lensPrice);

            if (lensDetailEl) {
                lensDetailEl.textContent = canQuoteLenses ? lensDetail : '';
            }
            if (colorDetailEl) {
                colorDetailEl.textContent = canQuoteLenses ? colorDetail : '';
            }

            const colorIsReady = !mustPickColor || !!(lensColorSelect && String(lensColorSelect.value || '').trim() !== '');
            const hasPdfReady = isNoPrescription
                || isPlanoNeutro()
                || isNoFormulaSimple()
                || !!(pdfInput && pdfInput.files && pdfInput.files.length > 0);
            const canPayNow = canQuoteLenses && hasLensSelection && hasNaraSelection && colorIsReady && hasPdfReady;
            if (payBtn) {
                payBtn.disabled = !canPayNow;
                payBtn.classList.toggle('opacity-60', !canPayNow);
                payBtn.classList.toggle('cursor-not-allowed', !canPayNow);
            }

            if (!canQuoteLenses || !hasLensSelection) {
                if (lensTypeInput) lensTypeInput.value = '';
                if (naraInput) {
                    naraInput.value = '';
                    naraInput.disabled = true;
                }
            } else {
                if (lensTypeInput) lensTypeInput.value = selectedLensType;
                if (naraInput) {
                    if (mode === 'progresivos' && hasNaraSelection) {
                        naraInput.disabled = false;
                        naraInput.value = selectedNara;
                    } else {
                        naraInput.value = '';
                        naraInput.disabled = true;
                    }
                }
            }
        };

        const setActiveNara = (active) => {
            naraButtons.forEach((btn) => {
                btn.classList.remove('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
                btn.setAttribute('aria-pressed', 'false');
            });
            if (!active) return;
            active.classList.add('ring-2', 'ring-zinc-800', 'ring-offset-2', 'ring-offset-white');
            active.setAttribute('aria-pressed', 'true');
        };

        const updatePanelWidth = () => {
            const rect = sidebar?.getBoundingClientRect();
            const sidebarWidth = rect?.width || window.innerWidth;
            const viewportPadding = 16;
            const desktopTarget = Math.max(sidebarWidth + 120, window.innerWidth * 0.5);
            const width = window.innerWidth >= 1024
                ? Math.min(window.innerWidth - viewportPadding, desktopTarget)
                : Math.min(window.innerWidth - viewportPadding, window.innerWidth);

            panel.style.width = `${Math.round(width)}px`;
        };

        const openPanel = (href) => {
            // Carga el GIF solo la primera vez que se abre el panel
            const loadingGif = document.getElementById('buyPanelLoadingGif');
            if (loadingGif && !loadingGif.src) {
                loadingGif.src = 'https://media0.giphy.com/media/v1.Y2lkPTZjMDliOTUya2QyMm9qaHh4NmdiajViZjZ1OTF2NHVwcXhjdXQ0aWU0cnk3N2gzYiZlcD12MV9zdGlja2Vyc19zZWFyY2gmY3Q9cw/5j5ZLtybC9q9avWqX8/giphy.gif';
            }
            updatePanelWidth();
            backdrop.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            requestAnimationFrame(() => {
                panel.classList.remove('translate-x-full');
            });
            applyMode();
            updatePrices();
            try {
                syncSteps();
            } catch {
                // noop
            }
        };

        const closePanel = () => {
            panel.classList.add('translate-x-full');
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        openButtons.forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                openPanel('#');
            });
        });

        lensTypeSelect.addEventListener('change', () => {
            selectedLensType = String(lensTypeSelect.value || '');
            panel.setAttribute('data-selected-lens', selectedLensType);
            syncBifocalCards();
            syncProgresivosCards();
            applyPlanoRxUi();
            updatePrices();
        });

        [rxOdSphere, rxOiSphere, rxOdCyl, rxOiCyl].forEach((el) => {
            if (!el) return;
            el.addEventListener('input', () => {
                updateStep3FieldStates();
                syncSteps();
                updatePrices();
            });
            el.addEventListener('change', () => {
                updateStep3FieldStates();
                syncSteps();
                updatePrices();
            });
        });

        [rxOdAxis, rxOiAxis, rxDistanciaPupilar, rxOdAdicion, rxOiAdicion].forEach((el) => {
            if (!el) return;
            el.addEventListener('input', () => {
                updateStep3FieldStates();
                syncSteps();
                updatePrices();
            });
            el.addEventListener('change', () => {
                updateStep3FieldStates();
                syncSteps();
                updatePrices();
            });
        });

        if (lensColorSelect) {
            lensColorSelect.addEventListener('change', () => {
                const colorVal = String(lensColorSelect.value || '').trim();
                if (colorVal !== '') {
                    hideColorValidation();
                }
                updatePrices();
            });
        }

        if (pdfInput) {
            pdfInput.addEventListener('change', () => {
                const pdfSizeError = pdfWrap ? pdfWrap.querySelector('[data-buy-pdf-size-error]') : null;
                if (pdfSizeError) pdfSizeError.classList.add('hidden');
                syncSteps();
                updatePrices();
            });
        }

        naraButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                selectedNara = String(btn.getAttribute('data-buy-nara') || '');
                panel.setAttribute('data-selected-nara', selectedNara);
                setActiveNara(btn);
                updatePrices();
            });
        });

        ocupacionalButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const next = String(btn.getAttribute('data-buy-ocupacional') || '');
                if (!next) return;
                selectedLensType = next;
                panel.setAttribute('data-selected-lens', selectedLensType);
                try {
                    lensTypeSelect.value = selectedLensType;
                } catch {
                    // noop
                }
                setActiveOcupacional();
                updatePrices();
            });
        });

        bifocalButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const next = String(btn.getAttribute('data-buy-bifocal-lens') || '');
                if (!next) return;
                setSelectedLensType(next);
                updatePrices();
            });
        });

        progresivosPresetButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const next = String(btn.getAttribute('data-buy-lens-preset') || '');
                if (!next) return;
                selectedLensType = next;
                panel.setAttribute('data-selected-lens', selectedLensType);
                try {
                    lensTypeSelect.value = selectedLensType;
                } catch {
                    // noop
                }
                syncProgresivosCards();
                updatePrices();
            });
        });

        monofocalCategoryButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const next = String(btn.getAttribute('data-buy-monofocal-category') || '');
                if (!next) return;
                const currentCategory = inferMonofocalCategory() || String(selectedMonofocalCategory || '');
                setMonofocalCategory(next);
                if (next === 'transition') {
                    setSelectedLensType('159_transitions_gens');
                } else if (currentCategory !== next) {
                    setSelectedLensType('');
                }
                syncMonofocalLensButtons();
                applyPlanoRxUi();
                updatePrices();
            });
        });

        monofocalLensButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const next = String(btn.getAttribute('data-buy-monofocal-lens') || '');
                if (!next) return;
                selectedLensType = next;
                panel.setAttribute('data-selected-lens', selectedLensType);
                try {
                    lensTypeSelect.value = selectedLensType;
                } catch {
                    // noop
                }
                setMonofocalCategory(inferMonofocalCategory());
                syncMonofocalLensButtons();
                applyPlanoRxUi();
                updatePrices();
            });
        });

        noFormulaLensButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const next = String(btn.getAttribute('data-buy-noformula-lens') || '');
                if (!next) return;
                setSelectedLensType(next);
                lastNoFormulaLensType = next;
                syncNoFormulaLensButtons();
                updatePrices();
            });
        });

        step2MaterialCards.forEach((card) => {
            card.addEventListener('click', () => {
                const value = String(card.getAttribute('data-step2-material-card') || '');
                if (!value) return;
                selectedStep2Material = value;
                if (step2MaterialInput) step2MaterialInput.value = value;

                // Al cambiar material se reinicia protección para mantener el flujo progresivo.
                selectedStep2Protection = '';
                if (step2ProtectionInput) step2ProtectionInput.value = '';

                syncStep2ProgressiveUi();
                syncSteps();
            });
        });

        step2ProtectionCards.forEach((card) => {
            card.addEventListener('click', () => {
                const value = String(card.getAttribute('data-step2-protection-card') || '');
                if (!value) return;
                selectedStep2Protection = value;
                if (step2ProtectionInput) step2ProtectionInput.value = value;

                syncStep2ProgressiveUi();
                syncSteps();
            });
        });

        planoRxManualInputs.forEach((input) => {
            input.addEventListener('change', () => {
                syncPlanoRxHiddenInputs();
                updatePlanoRxLabels();
                updatePrices();
            });
            input.addEventListener('input', () => {
                syncPlanoRxHiddenInputs();
                updatePlanoRxLabels();
                updatePrices();
            });
        });

        closeButtons.forEach((btn) => {
            btn.addEventListener('click', () => closePanel());
        });

        backdrop.addEventListener('click', () => closePanel());

        window.addEventListener('resize', updatePanelWidth);

        // Inicializar selección visual
        if (selectedLensType) {
            lensTypeSelect.value = selectedLensType;
        } else {
            try {
                lensTypeSelect.selectedIndex = -1;
            } catch {
                // noop
            }
        }
        const initialBtn = naraButtons.find((b) => String(b.getAttribute('data-buy-nara') || '') === selectedNara);
        if (initialBtn) {
            selectedNara = String(initialBtn.getAttribute('data-buy-nara') || selectedNara);
            setActiveNara(initialBtn);
        } else {
            selectedNara = '';
            setActiveNara(null);
        }
        syncPlanoRxHiddenInputs();
        updatePlanoRxLabels();
        updatePrices();
        syncBifocalCards();
        syncProgresivosCards();

        const clearRadios = (inputs) => inputs.forEach((i) => { if (i) i.checked = false; });

        const setNoPrescription = (value) => {
            if (noPrescriptionInput) noPrescriptionInput.value = value ? '1' : '0';
        };

        const setPlanoNeutro = (value) => {
            if (planoNeutroInput) planoNeutroInput.value = value ? '1' : '0';
        };

        const forceTipoLenteNecesitas = (value) => {
            const v = String(value || '');
            const r = step2Radios.find((x) => String(x && x.value ? x.value : '') === v);
            if (r) r.checked = true;
        };

        const setPdfRequired = (value) => {
            if (!pdfInput) return;
            if (value) {
                pdfInput.setAttribute('required', 'required');
            } else {
                pdfInput.removeAttribute('required');
                try {
                    pdfInput.value = '';
                } catch {
                    // noop
                }
            }
        };

        const showUpTo = (step, opts = {}) => {
            const { allowPay = false, requirePdf = false, requireStep2 = false, requireYear = false } = opts;
            const map = [null, step1, step2, step3, step4];
            for (let i = 1; i <= 4; i += 1) {
                const el = map[i];
                if (!el) continue;
                if (i <= step) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            }

            // Evita que los campos ocultos bloqueen validación del navegador.
            step1Radios.forEach((r) => {
                if (!r) return;
                if (step >= 1) r.setAttribute('required', 'required');
                else r.removeAttribute('required');
            });
            step2Radios.forEach((r) => {
                if (!r) return;
                if (requireStep2 && step >= 2) r.setAttribute('required', 'required');
                else r.removeAttribute('required');
            });
            if (step3Year) {
                if (requireYear && step >= 3) step3Year.setAttribute('required', 'required');
                else step3Year.removeAttribute('required');
            }

            setPdfRequired(requirePdf);

            if (pdfWrap) {
                pdfWrap.classList.toggle('hidden', !requirePdf);
            }

            const canPay = allowPay;
            if (payBtn) {
                payBtn.disabled = !canPay;
                payBtn.classList.toggle('opacity-60', !canPay);
                payBtn.classList.toggle('cursor-not-allowed', !canPay);
            }
        };

        const syncSteps = () => {
            const done1 = isChecked(step1Radios);
            if (!done1) {
                setNoPrescription(false);
                setPlanoNeutro(false);
                showUpTo(1, { allowPay: false, requirePdf: false, requireStep2: false, requireYear: false });
                currentStep = 1;
                updatePrices();
                return 1;
            }

            const step1Value = getCheckedValue(step1Radios);
            const isFormula = step1Value === 'si_con_formula';

            if (step1Value === 'sin_aumento_neutro' || step1Value === 'no_sin_formula') {
                setPlanoNeutro(true);
                setNoPrescription(false);
                forceTipoLenteNecesitas('con_aumento_monofocal');
                applyMode();
                showUpTo(4, { allowPay: true, requirePdf: false, requireStep2: false, requireYear: false });
                if (step2) step2.classList.add('hidden');
                if (step3) step3.classList.add('hidden');
                currentStep = 4;
                updatePrices();
                return 4;
            }

            setPlanoNeutro(false);

            if (!isFormula) {
                // Sin fórmula / neutro: compra solo montura, sin PDF ni selección de lentes.
                setNoPrescription(true);
                showUpTo(1, { allowPay: true, requirePdf: false, requireStep2: false, requireYear: false });
                currentStep = 1;
                updatePrices();
                return 1;
            }

            setNoPrescription(false);

            const done2 = isChecked(step2Radios);
            if (!done2) {
                showUpTo(2, { allowPay: false, requirePdf: false, requireStep2: true, requireYear: false });
                currentStep = 2;
                updatePrices();
                return 2;
            }

            const mode = getMode();
            const requiresAdicion = mode !== 'monofocal';
            const formulaFieldsComplete = hasValue(rxOdSphere)
                && hasValue(rxOdCyl)
                && hasValue(rxOdAxis)
                && hasValue(rxOiSphere)
                && hasValue(rxOiCyl)
                && hasValue(rxOiAxis)
                && (!requiresAdicion || hasValue(rxOdAdicion));
            const done3 = formulaFieldsComplete;
            if (!done3) {
                showUpTo(3, { allowPay: false, requirePdf: false, requireStep2: true, requireYear: false });
                currentStep = 3;
                updatePrices();
                return 3;
            }

            showUpTo(4, { allowPay: true, requirePdf: true, requireStep2: true, requireYear: false });
            currentStep = 4;
            updatePrices();
            return 4;
        };

        const step3RequiredFields = [
            { input: rxOdSphere, visible: panel ? panel.querySelector('[data-rx-picker-for="rx_od_esfera"]') : null },
            { input: rxOdCyl, visible: panel ? panel.querySelector('[data-rx-picker-for="rx_od_cilindro"]') : null },
            { input: rxOdAxis, visible: panel ? panel.querySelector('[data-rx-picker-for="rx_od_eje"]') : null },
            { input: rxOiSphere, visible: panel ? panel.querySelector('[data-rx-picker-for="rx_oi_esfera"]') : null },
            { input: rxOiCyl, visible: panel ? panel.querySelector('[data-rx-picker-for="rx_oi_cilindro"]') : null },
            { input: rxOiAxis, visible: panel ? panel.querySelector('[data-rx-picker-for="rx_oi_eje"]') : null },
            { input: rxOdAdicion, visible: panel ? panel.querySelector('[data-rx-picker-for="rx_od_adicion"]') : null, isRequired: () => getMode() !== 'monofocal' },
        ];

        const clearFieldMissingState = (el) => {
            if (!el) return;
            el.classList.remove('border-rose-400', 'bg-rose-50', 'ring-2', 'ring-rose-200');
        };

        const setFieldMissingState = (el, missing) => {
            if (!el) return;
            clearFieldMissingState(el);
            if (!missing) return;
            el.classList.add('border-rose-400', 'bg-rose-50', 'ring-2', 'ring-rose-200');
        };

        const updateStep3FieldStates = () => {
            step3RequiredFields.forEach(({ input, visible, isRequired }) => {
                const required = typeof isRequired === 'function' ? !!isRequired() : true;
                setFieldMissingState(visible, required && !hasValue(input));
            });
        };

        const scrollToFirstMissing = (step) => {
            const map = [null, step1, step2, step3, step4];
            const el = map[step];
            if (!el) return;
            try {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch {
                // noop
            }

            if (step === 1 && step1Radios[0]) step1Radios[0].focus();
            if (step === 2 && step2Radios[0]) step2Radios[0].focus();
            if (step === 3) {
                const firstMissing = [
                    rxOdSphere,
                    rxOdCyl,
                    rxOdAxis,
                    rxOiSphere,
                    rxOiCyl,
                    rxOiAxis,
                    ...(getMode() !== 'monofocal' ? [rxOdAdicion] : []),
                ].find((input) => !hasValue(input));
                if (firstMissing) {
                    try {
                        firstMissing.focus();
                    } catch {
                        // noop
                    }
                }
            }
        };

        function hideColorValidation() {
            if (colorWrap) {
                colorWrap.classList.remove('border-amber-300', 'bg-amber-50/60', 'shadow-[0_0_0_4px_rgba(251,191,36,0.18)]');
            }
            if (lensColorSelect) {
                lensColorSelect.classList.remove('border-amber-400', 'bg-amber-50');
            }
            if (colorError) {
                colorError.classList.add('hidden');
            }
        }

        function showColorValidation() {
            if (colorWrap) {
                colorWrap.classList.remove('hidden');
                colorWrap.classList.add('border-amber-300', 'bg-amber-50/60', 'shadow-[0_0_0_4px_rgba(251,191,36,0.18)]');
                try {
                    colorWrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } catch {
                    // noop
                }
            }
            if (lensColorSelect) {
                lensColorSelect.classList.add('border-amber-400', 'bg-amber-50');
                try {
                    lensColorSelect.focus({ preventScroll: true });
                } catch {
                    lensColorSelect.focus();
                }
            }
            if (colorError) {
                colorError.classList.remove('hidden');
            }
        }

        step1Radios.forEach((r) => r.addEventListener('change', () => {
            // Si cambian el camino en PASO 1, resetea lo siguiente.
            clearRadios(step2Radios);
            selectedStep2Material = '';
            selectedStep2Protection = '';
            selectedMonofocalCategory = '';
            if (step2MaterialInput) step2MaterialInput.value = '';
            if (step2ProtectionInput) step2ProtectionInput.value = '';
            if (step3Year) step3Year.value = '';
            setPdfRequired(false);
            syncStep2ProgressiveUi();
            syncSteps();
        }));
        step2Radios.forEach((r) => r.addEventListener('change', () => {
            selectedStep2Material = '';
            selectedStep2Protection = '';
            selectedMonofocalCategory = '';
            selectedLensType = '';
            selectedNara = '';
            panel.setAttribute('data-selected-lens', '');
            panel.setAttribute('data-selected-nara', '');
            if (step2MaterialInput) step2MaterialInput.value = '';
            if (step2ProtectionInput) step2ProtectionInput.value = '';
            if (lensTypeInput) lensTypeInput.value = '';
            if (naraInput) {
                naraInput.value = '';
                naraInput.disabled = true;
            }
            try {
                lensTypeSelect.selectedIndex = -1;
            } catch {
                // noop
            }
            setActiveNara(null);
            applyMode();
            updateStep3FieldStates();
            syncSteps();
        }));
        if (step3Year) {
            step3Year.addEventListener('input', () => {
                updateStep3FieldStates();
                syncSteps();
            });
            step3Year.addEventListener('change', () => {
                updateStep3FieldStates();
                syncSteps();
            });
        }

        // Inicializar pasos al cargar.
        syncStep2ProgressiveUi();
        updateStep3FieldStates();
        syncSteps();

        // Si el backend devolvió errores del panel, abrirlo automáticamente.
        const openOnLoad = String(panel.getAttribute('data-open-on-load') || '0') === '1';
        if (openOnLoad) {
            try {
                openPanel('#');
            } catch {
                // noop
            }
        }

        // Mostrar overlay de carga del PDF por máximo 5 segundos.
        payForm.addEventListener('submit', (e) => {
            const step = syncSteps();
            const isNoPrescription = !!(noPrescriptionInput && String(noPrescriptionInput.value || '0') === '1');
            if (!isNoPrescription && step < 4) {
                e.preventDefault();
                updateStep3FieldStates();
                scrollToFirstMissing(step);
                return;
            }
            try {
                const mode = getMode();
                const hasLensSelection = hasSelectedLensForMode(mode);
                const hasNaraSelection = mode !== 'progresivos' || hasSelectedNara();
                if (!isNoPrescription && step >= 4 && (!hasLensSelection || !hasNaraSelection)) {
                    e.preventDefault();
                    updateStep3FieldStates();
                    scrollToFirstMissing(4);
                    return;
                }

                const transitionsSelected = String(selectedLensType || '') === '159_transitions_gens';
                if (!isNoPrescription && transitionsSelected) {
                    const colorVal = lensColorSelect ? String(lensColorSelect.value || '').trim() : '';
                    if (colorVal === '') {
                        e.preventDefault();
                        showColorValidation();
                        return;
                    }
                }
                const hasFile = !!(pdfInput && pdfInput.files && pdfInput.files.length > 0);
                if (!hasFile) return;

                // Validar tamaño máximo del PDF (20MB = 20 * 1024 * 1024 bytes)
                const pdfSizeError = pdfWrap ? pdfWrap.querySelector('[data-buy-pdf-size-error]') : null;
                const maxPdfBytes = 20 * 1024 * 1024;
                const pdfFile = pdfInput.files[0];
                if (pdfFile && pdfFile.size > maxPdfBytes) {
                    e.preventDefault();
                    if (pdfSizeError) pdfSizeError.classList.remove('hidden');
                    if (pdfWrap) pdfWrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                if (pdfSizeError) pdfSizeError.classList.add('hidden');

                if (payBtn) {
                    payBtn.disabled = true;
                    payBtn.classList.add('opacity-80', 'cursor-not-allowed');
                }

                if (verifyingOverlay) {
                    verifyingOverlay.classList.remove('hidden');
                    window.setTimeout(() => {
                        try {
                            verifyingOverlay.classList.add('hidden');
                            if (payBtn) {
                                payBtn.disabled = false;
                                payBtn.classList.remove('opacity-80', 'cursor-not-allowed');
                            }
                        } catch {
                            // noop
                        }
                    }, 5000);
                }
            } catch {
                // noop
            }
        });
    })();
</script>

<!-- RX Value Picker Modal -->
<div id="rxPickerModal" class="fixed inset-0 z-[9999] hidden" role="dialog" aria-modal="true" aria-labelledby="rxPickerTitle">
    <div class="absolute inset-0 bg-black/50" id="rxPickerBackdrop"></div>
    <div class="absolute inset-x-0 bottom-0 sm:inset-0 sm:flex sm:items-center sm:justify-center sm:p-6 pointer-events-none">
        <div class="pointer-events-auto relative w-full sm:max-w-2xl rounded-t-3xl sm:rounded-3xl bg-white shadow-2xl flex flex-col" style="max-height:88svh;max-height:88dvh">
            <div class="sm:hidden flex justify-center pt-3 pb-1 flex-shrink-0">
                <div class="w-10 h-1 rounded-full bg-zinc-300"></div>
            </div>
            <div class="flex items-center justify-between px-5 pt-4 pb-3 sm:pt-6 sm:px-6 flex-shrink-0">
                <h3 class="text-sm font-semibold text-zinc-900 sm:text-base leading-tight pr-3" id="rxPickerTitle">Seleccionar</h3>
                <button type="button" id="rxPickerClose" class="flex-shrink-0 rounded-full p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 transition">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="overflow-y-auto flex-1 px-4 sm:px-6 py-2" id="rxPickerGridArea"></div>
            <div class="px-4 sm:px-6 py-4 space-y-2 border-t border-zinc-100 flex-shrink-0">
                <button type="button" id="rxPickerConfirm" class="w-full rounded-2xl bg-[#111827] py-3.5 text-sm font-bold text-white hover:bg-[#1f2937] active:bg-[#2b5b60] transition">Confirmar</button>
                <button type="button" id="rxPickerCancel" class="w-full py-2.5 text-sm font-semibold text-zinc-500 hover:text-zinc-700 transition">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';
    var modal = document.getElementById('rxPickerModal');
    if (!modal) return;
    var titleEl = document.getElementById('rxPickerTitle');
    var gridArea = document.getElementById('rxPickerGridArea');
    var closeBtn = document.getElementById('rxPickerClose');
    var confirmBtn = document.getElementById('rxPickerConfirm');
    var cancelBtn = document.getElementById('rxPickerCancel');
    var backdrop = document.getElementById('rxPickerBackdrop');
    var activeTrigger = null;
    var activeHiddenInput = null;
    var pendingValue = null;

    function genRange(from, to, step) {
        var arr = [];
        var M = 10000;
        for (var i = Math.round(from * M); i <= Math.round(to * M); i += Math.round(step * M)) {
            arr.push(i / M);
        }
        return arr;
    }

    var CONFIGS = {
        esfera: {
            getTitle: function(eye) { return 'Selecciona el ESF del ojo ' + eye; },
            type: 'neg-pos',
            negatives: genRange(0.25, 15.00, 0.25).map(function(v) { return -v; }),
            positives: genRange(0.25, 8.00, 0.25),
            hasNeutro: true
        },
        cilindro: {
            getTitle: function(eye) { return 'Selecciona el CIL del ojo ' + eye; },
            type: 'neg-only',
            negatives: genRange(0.25, 15.00, 0.25).map(function(v) { return -v; }),
            hasNeutro: true
        },
        eje: {
            getTitle: function(eye) { return 'Selecciona el EJE del ojo ' + eye; },
            type: 'flat',
            values: Array.from({ length: 181 }, function(_, i) { return i; }),
            cols: 6,
            fmt: function(v) { return String(v).padStart(3, '0'); },
            store: function(v) { return String(v); }
        },
        adicion: {
            getTitle: function(eye) { return 'Selecciona la ADD del ojo ' + eye; },
            type: 'pos-only',
            positives: genRange(0.75, 4.50, 0.25)
        },
        dp: {
            getTitle: function() { return 'Selecciona la distancia pupilar'; },
            type: 'flat',
            values: genRange(45.0, 80.0, 0.5),
            cols: 5,
            fmt: function(v) { return v.toFixed(1); },
            store: function(v) { return v.toFixed(1); }
        }
    };

    function fmtDisplay(type, val) {
        if (val === null || val === undefined || val === '') return 'Seleccionar';
        var n = Number(val);
        if (!isFinite(n)) return String(val);
        if (type === 'eje') return String(Math.round(n)).padStart(3, '0');
        if (type === 'dp') return n.toFixed(1);
        if (n === 0) return 'Neutro/N';
        return n > 0 ? '+' + n.toFixed(2) : n.toFixed(2);
    }

    function btnCls(selected) {
        return 'rounded-xl border px-2 py-2 text-xs font-semibold text-center transition cursor-pointer ' +
            (selected ? 'border-zinc-800 bg-[#111827] text-white' : 'border-zinc-200 bg-white text-zinc-800 hover:border-zinc-800 hover:bg-[#111827]/10');
    }

    function makeBtn(label, value, selected) {
        var b = document.createElement('button');
        b.type = 'button';
        b.textContent = label;
        b.dataset.pval = value;
        b.className = btnCls(selected);
        return b;
    }

    function updateSelection(val) {
        pendingValue = val;
        gridArea.querySelectorAll('[data-pval]').forEach(function(b) {
            b.className = btnCls(b.dataset.pval === val);
        });
    }

    function buildGrid(config, currentVal) {
        gridArea.innerHTML = '';
        pendingValue = currentVal || null;

        if (config.type === 'neg-pos' || config.type === 'neg-only') {
            var isNegPos = config.type === 'neg-pos';
            var wrapper = document.createElement('div');
            wrapper.className = isNegPos ? 'grid grid-cols-2 gap-3' : 'w-full';

            var negPanel = document.createElement('div');
            negPanel.className = 'rounded-2xl bg-zinc-100 p-3';
            var negTitle = document.createElement('p');
            negTitle.className = 'text-[11px] font-semibold text-zinc-700 mb-2 text-center';
            negTitle.textContent = 'Valores negativos';
            negPanel.appendChild(negTitle);
            var negGrid = document.createElement('div');
            negGrid.className = 'grid grid-cols-2 gap-1.5 sm:grid-cols-3';
            if (config.hasNeutro) {
                var isSel0 = currentVal === '0.00' || currentVal === '0';
                negGrid.appendChild(makeBtn('Neutro/N', '0.00', isSel0));
            }
            config.negatives.forEach(function(v) {
                var s = v.toFixed(2);
                negGrid.appendChild(makeBtn(s, s, currentVal === s));
            });
            negPanel.appendChild(negGrid);
            wrapper.appendChild(negPanel);

            if (isNegPos) {
                var posPanel = document.createElement('div');
                posPanel.className = 'rounded-2xl bg-zinc-100 p-3';
                var posTitle = document.createElement('p');
                posTitle.className = 'text-[11px] font-semibold text-zinc-700 mb-2 text-center';
                posTitle.textContent = 'Valores positivos';
                posPanel.appendChild(posTitle);
                var posGrid = document.createElement('div');
                posGrid.className = 'grid grid-cols-2 gap-1.5 sm:grid-cols-3';
                config.positives.forEach(function(v) {
                    var s = v.toFixed(2);
                    posGrid.appendChild(makeBtn('+' + s, s, currentVal === s));
                });
                posPanel.appendChild(posGrid);
                wrapper.appendChild(posPanel);
            }
            gridArea.appendChild(wrapper);

        } else if (config.type === 'pos-only') {
            var panel = document.createElement('div');
            panel.className = 'rounded-2xl bg-sky-50 p-3';
            var pTitle = document.createElement('p');
            pTitle.className = 'text-[11px] font-semibold text-sky-800 mb-2 text-center';
            pTitle.textContent = 'Valores de adición';
            panel.appendChild(pTitle);
            var pgrid = document.createElement('div');
            pgrid.className = 'grid grid-cols-3 gap-1.5 sm:grid-cols-4';
            config.positives.forEach(function(v) {
                var s = v.toFixed(2);
                pgrid.appendChild(makeBtn('+' + s, s, currentVal === s));
            });
            panel.appendChild(pgrid);
            gridArea.appendChild(panel);

        } else {
            var fgrid = document.createElement('div');
            var cols = config.cols || 5;
            fgrid.style.cssText = 'display:grid;grid-template-columns:repeat(' + cols + ',minmax(0,1fr));gap:6px;';
            var fmtFn = config.fmt || function(v) { return String(v); };
            var storeFn = config.store || function(v) { return String(v); };
            config.values.forEach(function(v) {
                var stored = storeFn(v);
                fgrid.appendChild(makeBtn(fmtFn(v), stored, currentVal === stored));
            });
            gridArea.appendChild(fgrid);
        }

        gridArea.querySelectorAll('[data-pval]').forEach(function(b) {
            b.addEventListener('click', function() { updateSelection(b.dataset.pval); });
        });
    }

    function openModal(trigger) {
        var fieldName = trigger.dataset.rxPickerFor;
        var type = trigger.dataset.rxPickerType;
        var eye = trigger.dataset.rxPickerEye || '';
        var config = CONFIGS[type];
        if (!config) return;
        activeTrigger = trigger;
        activeHiddenInput = document.querySelector('input[name="' + fieldName + '"]');
        var currentVal = activeHiddenInput ? String(activeHiddenInput.value || '') : '';
        if (titleEl) titleEl.textContent = config.getTitle ? config.getTitle(eye) : 'Seleccionar';
        buildGrid(config, currentVal);
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(function() {
            var selBtn = gridArea.querySelector('.text-white');
            if (selBtn) selBtn.scrollIntoView({ block: 'nearest', behavior: 'auto' });
        }, 30);
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        activeTrigger = null;
        activeHiddenInput = null;
        pendingValue = null;
    }

    function confirmSelection() {
        if (pendingValue === null) { closeModal(); return; }
        if (activeHiddenInput) {
            activeHiddenInput.value = pendingValue;
            try {
                activeHiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                activeHiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            } catch {
                // noop
            }

            if (activeHiddenInput.name === 'rx_od_adicion') {
                var oiAdicion = document.querySelector('input[name="rx_oi_adicion"]');
                if (oiAdicion) {
                    oiAdicion.value = pendingValue;
                    try {
                        oiAdicion.dispatchEvent(new Event('input', { bubbles: true }));
                        oiAdicion.dispatchEvent(new Event('change', { bubbles: true }));
                    } catch {
                        // noop
                    }
                }
            }
        }
        if (activeTrigger) {
            var type = activeTrigger.dataset.rxPickerType;
            var span = activeTrigger.querySelector('span');
            if (span) span.textContent = fmtDisplay(type, pendingValue);
        }
        closeModal();
    }

    document.querySelectorAll('[data-rx-picker]').forEach(function(btn) {
        btn.addEventListener('click', function() { openModal(btn); });
    });
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (confirmBtn) confirmBtn.addEventListener('click', confirmSelection);
    if (backdrop) backdrop.addEventListener('click', closeModal);
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    // Inicializar etiquetas para valores old()
    document.querySelectorAll('[data-rx-picker]').forEach(function(btn) {
        var fieldName = btn.dataset.rxPickerFor;
        var type = btn.dataset.rxPickerType;
        var input = document.querySelector('input[name="' + fieldName + '"]');
        if (!input || !input.value) return;
        var span = btn.querySelector('span');
        if (span) span.textContent = fmtDisplay(type, input.value);
    });
})();
</script>

<?php if($hasCameraVirtual): ?>
    <script>
    (function () {
        var btnAbrir    = document.getElementById('btnAbrirCamara');
        var btnCerrar   = document.getElementById('btnCerrarCamara');
        var modal       = document.getElementById('modalCamara');
        var viewport    = document.getElementById('camaraViewport');
        var video       = document.getElementById('camaraVideo');
        var overlay     = document.getElementById('camaraOverlay');
        var errorMsg    = document.getElementById('camaraError');
        var noFaceMsg   = document.getElementById('camaraNoFace');
        var statusBox   = document.getElementById('camaraStatus');
        var statusText  = document.getElementById('camaraStatusText');
        var overlayCtx  = null;
        var contextLostCount = 0;
        var MAX_CONTEXT_LOSS_RETRIES = 3;
        var contextValidationIntervalId = 0;
        
        function initCanvasContext() {
            if (!overlay) return null;
            try {
                var ctx = overlay.getContext('2d', { alpha: true, willReadFrequently: false });
                contextLostCount = 0;
                return ctx;
            } catch (e) {
                return null;
            }
        }
        
        function validateAndRestoreContext() {
            if (!running) return;
            
            // Verificar si el contexto es válido en iOS
            if (IS_MOBILE_CAMERA && (IOS_SAFARI_PERF_MODE || IS_SAFARI_BROWSER)) {
                if (!overlayCtx || !overlayCtx.canvas || overlayCtx.canvas.width === 0) {
                    overlayCtx = initCanvasContext();
                }
            }
        }
        
        overlayCtx = initCanvasContext();
        var sidebarColorLabel = document.getElementById('gafaColorLabelValue');
        var mainColorButtons = Array.from(document.querySelectorAll('[data-gafa-color-swatch]'));
        var camColorButtons = Array.from(document.querySelectorAll('[data-cam-color-swatch]'));
        var backgroundNavSelectors = ['#gafaPrevImage', '#gafaNextImage', '[data-related-prev]', '[data-related-next]', '[data-related-carousel]'];
        var backgroundNavElements = backgroundNavSelectors
            .map(function (selector) { return Array.from(document.querySelectorAll(selector)); })
            .reduce(function (acc, current) { return acc.concat(current); }, []);
        var navbarSelectors = ['header.relative.z-\\[70\\]', 'div.relative.z-\\[70\\]', '#js-store-mobile-menu'];
        var navbarElements = navbarSelectors
            .map(function (selector) { return Array.from(document.querySelectorAll(selector)); })
            .reduce(function (acc, current) { return acc.concat(current); }, []);

        var stream       = null;
        var running      = false;
        var modelsLoaded = false;
        var smoothedPose = null;
        var renderPose = null;
        var renderFrameId = 0;
        var neutralEyeSpan = null;
        var lostPoseFrames = 0;
        var SMOOTH_ALPHA = 0.22;
        var ANGLE_SMOOTH_ALPHA = 0.16;
        var SPAN_ADAPT_ALPHA = 0.04;
        var UA = navigator.userAgent || '';
        var IS_IOS_DEVICE = /iPad|iPhone|iPod/i.test(UA) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        var IS_ANDROID_DEVICE = /Android/i.test(UA);
        var IS_SAFARI_BROWSER = /^((?!chrome|android|crios|fxios|edgios).)*safari/i.test(UA);
        var IS_MOBILE_CAMERA = window.matchMedia('(max-width: 640px)').matches;
        var IOS_SAFARI_PERF_MODE = IS_MOBILE_CAMERA && IS_IOS_DEVICE && IS_SAFARI_BROWSER;
        var MOBILE_PERFORMANCE_MODE = IS_MOBILE_CAMERA;
        var DETECTOR_INPUT_SIZE = IOS_SAFARI_PERF_MODE ? 128 : (IS_ANDROID_DEVICE ? 128 : (IS_MOBILE_CAMERA ? 160 : 320));
        var DETECTOR_SCORE_THRESHOLD = IOS_SAFARI_PERF_MODE ? 0.36 : (IS_MOBILE_CAMERA ? 0.38 : 0.5);
        var MAX_LOST_POSE_FRAMES = IS_MOBILE_CAMERA ? 10 : 6;
        var GAFAS_WIDTH_MULTIPLIER = IS_MOBILE_CAMERA ? 1.78 : 1.86;
        var GAFAS_CENTER_BIAS = IS_MOBILE_CAMERA ? 0.22 : 0.24;
        var GAFAS_VERTICAL_OFFSET = IS_MOBILE_CAMERA ? 0.05 : 0.07;
        var GAFAS_IMAGE_ANCHOR_Y = IS_MOBILE_CAMERA ? 0.31 : 0.33;
        var GAFAS_EYELINE_BLEND = IS_MOBILE_CAMERA ? 0.58 : 0.56;
        var GAFAS_UPLIFT_FACTOR = IS_MOBILE_CAMERA ? -0.018 : -0.016;
        var GAFAS_BROW_BRIDGE_BLEND = IS_MOBILE_CAMERA ? 0.68 : 0.66;
        var GAFAS_MAX_BELOW_BROW_RATIO = IS_MOBILE_CAMERA ? 0.96 : 0.94;
        var VIDEO_FIT_MODE = 'contain';
        var POSE_BASE_ALPHA = IOS_SAFARI_PERF_MODE ? 0.23 : (IS_MOBILE_CAMERA ? 0.2 : 0.18);
        var POSE_MAX_ALPHA = IOS_SAFARI_PERF_MODE ? 0.56 : (IS_MOBILE_CAMERA ? 0.5 : 0.42);
        var ANGLE_BASE_ALPHA = IS_MOBILE_CAMERA ? 0.15 : 0.14;
        var ANGLE_MAX_ALPHA = IS_MOBILE_CAMERA ? 0.4 : 0.34;
        var SIZE_BASE_ALPHA = IOS_SAFARI_PERF_MODE ? 0.22 : (IS_MOBILE_CAMERA ? 0.2 : 0.17);
        var SIZE_MAX_ALPHA = IOS_SAFARI_PERF_MODE ? 0.46 : (IS_MOBILE_CAMERA ? 0.42 : 0.34);
        var MOBILE_DETECT_INTERVAL_MS = IOS_SAFARI_PERF_MODE ? 60 : (IS_ANDROID_DEVICE ? 88 : 52);
        var TARGET_RENDER_FPS = IS_ANDROID_DEVICE ? 30 : (IS_MOBILE_CAMERA ? 45 : 60);
        var RENDER_INTERVAL_MS = 1000 / TARGET_RENDER_FPS;
        var lastRenderAt = 0;
        var RENDER_POSITION_LERP = IS_MOBILE_CAMERA ? 0.32 : 0.24;
        var RENDER_SIZE_LERP = IS_MOBILE_CAMERA ? 0.3 : 0.22;
        var RENDER_ANGLE_LERP = IS_MOBILE_CAMERA ? 0.26 : 0.2;
        var noFaceFrames = 0;
        var detectorOptions = null;
        var detectInFlight = false;
        var lastDetectAt = 0;
        var currentVideoConstraints = null;
        var cameraRecoveryTimeoutId = 0;
        var videoRecoveryHandlersBound = false;
        var isRecoveringCamera = false;
        var gafasOverlayMetrics = {
            visibleWidthRatio: 1,
            visibleHeightRatio: 1,
            centerOffsetX: 0,
            centerOffsetY: 0,
            anchorYNormalized: GAFAS_IMAGE_ANCHOR_Y,
        };
        var gafasAnalysisToken = 0;

        var MODELS_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@0.22.2/weights';

        // Pre-cargar imagen de las gafas
        var gafasImg = new Image();
        gafasImg.crossOrigin = 'anonymous';

        function resetGafasOverlayMetrics() {
            gafasOverlayMetrics = {
                visibleWidthRatio: 1,
                visibleHeightRatio: 1,
                centerOffsetX: 0,
                centerOffsetY: 0,
                anchorYNormalized: GAFAS_IMAGE_ANCHOR_Y,
            };
        }

        function analyzeCurrentGafasImage() {
            if (!(gafasImg && gafasImg.complete && gafasImg.naturalWidth > 0 && gafasImg.naturalHeight > 0)) {
                resetGafasOverlayMetrics();
                return;
            }

            var currentToken = ++gafasAnalysisToken;

            try {
                var naturalW = gafasImg.naturalWidth;
                var naturalH = gafasImg.naturalHeight;
                var sampleMax = 640;
                var scale = Math.min(1, sampleMax / Math.max(naturalW, naturalH));
                var sampleW = Math.max(1, Math.round(naturalW * scale));
                var sampleH = Math.max(1, Math.round(naturalH * scale));

                var probeCanvas = document.createElement('canvas');
                probeCanvas.width = sampleW;
                probeCanvas.height = sampleH;
                var probeCtx = probeCanvas.getContext('2d', { willReadFrequently: true });
                if (!probeCtx) {
                    resetGafasOverlayMetrics();
                    return;
                }

                probeCtx.clearRect(0, 0, sampleW, sampleH);
                probeCtx.drawImage(gafasImg, 0, 0, sampleW, sampleH);
                var pixels = probeCtx.getImageData(0, 0, sampleW, sampleH).data;

                var minX = sampleW;
                var minY = sampleH;
                var maxX = -1;
                var maxY = -1;
                var alphaThreshold = 14;

                for (var y = 0; y < sampleH; y++) {
                    for (var x = 0; x < sampleW; x++) {
                        var idx = ((y * sampleW) + x) * 4;
                        var alpha = pixels[idx + 3];
                        if (alpha <= alphaThreshold) continue;
                        if (x < minX) minX = x;
                        if (x > maxX) maxX = x;
                        if (y < minY) minY = y;
                        if (y > maxY) maxY = y;
                    }
                }

                if (currentToken !== gafasAnalysisToken) return;

                if (maxX < minX || maxY < minY) {
                    resetGafasOverlayMetrics();
                    return;
                }

                var left = minX / sampleW;
                var right = (maxX + 1) / sampleW;
                var top = minY / sampleH;
                var bottom = (maxY + 1) / sampleH;

                var visibleWidthRatio = clamp(right - left, 0.05, 1);
                var visibleHeightRatio = clamp(bottom - top, 0.05, 1);
                var visibleCenterX = (left + right) / 2;
                var visibleCenterY = (top + bottom) / 2;
                var anchorYNormalized = clamp(top + (visibleHeightRatio * GAFAS_IMAGE_ANCHOR_Y), 0.05, 0.95);

                gafasOverlayMetrics = {
                    visibleWidthRatio: visibleWidthRatio,
                    visibleHeightRatio: visibleHeightRatio,
                    centerOffsetX: visibleCenterX - 0.5,
                    centerOffsetY: visibleCenterY - 0.5,
                    anchorYNormalized: anchorYNormalized,
                };
            } catch (e) {
                resetGafasOverlayMetrics();
            }
        }

        gafasImg.addEventListener('load', function () {
            analyzeCurrentGafasImage();
        });

        gafasImg.addEventListener('error', function () {
            resetGafasOverlayMetrics();
        });

        gafasImg.src = '<?php echo e($primaryImg); ?>';

        if (video) {
            video.style.objectFit = VIDEO_FIT_MODE;
            video.style.objectPosition = 'center center';
            video.style.backgroundColor = 'transparent';
        }

        function syncViewportAspect() {
            if (!viewport || !IS_MOBILE_CAMERA) return;

            var videoWidth = video && video.videoWidth ? video.videoWidth : 0;
            var videoHeight = video && video.videoHeight ? video.videoHeight : 0;
            if (videoWidth > 0 && videoHeight > 0) {
                var ratioW = videoWidth;
                var ratioH = videoHeight;

                // En algunos iPhone/Android el metadata llega "acostado"; forzamos vertical en móvil.
                if (ratioW > ratioH) {
                    var tmp = ratioW;
                    ratioW = ratioH;
                    ratioH = tmp;
                }

                viewport.style.aspectRatio = ratioW + ' / ' + ratioH;
                return;
            }

            viewport.style.aspectRatio = '3 / 4';
        }

        function resetViewportAspect() {
            if (!viewport || !IS_MOBILE_CAMERA) return;
            viewport.style.aspectRatio = '3 / 4';
        }

        function revealVideoSurface() {
            if (!video) return;
            syncViewportAspect();
            video.style.opacity = '1';
            video.style.visibility = 'visible';
        }

        function clearCameraRecovery() {
            if (cameraRecoveryTimeoutId) {
                window.clearTimeout(cameraRecoveryTimeoutId);
                cameraRecoveryTimeoutId = 0;
            }
        }

        function bindVideoRecoveryHandlers() {
            if (!video || videoRecoveryHandlersBound) return;

            ['loadedmetadata', 'loadeddata', 'canplay', 'playing'].forEach(function (eventName) {
                video.addEventListener(eventName, revealVideoSurface);
            });

            ['emptied', 'error'].forEach(function (eventName) {
                video.addEventListener(eventName, function () {
                    scheduleCameraRecovery(true);
                });
            });

            videoRecoveryHandlersBound = true;
        }

        function restartCameraStream() {
            if (!running || isRecoveringCamera || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                return Promise.resolve();
            }

            isRecoveringCamera = true;
            var nextConstraints = currentVideoConstraints || {
                facingMode: 'user',
                width: { ideal: IS_MOBILE_CAMERA ? 720 : 1280 },
                height: { ideal: IS_MOBILE_CAMERA ? 960 : 720 },
                frameRate: IS_MOBILE_CAMERA ? { ideal: (IS_ANDROID_DEVICE ? 24 : 30), max: 30 } : { ideal: 30, max: 30 }
            };

            var previousStream = stream;

            return navigator.mediaDevices.getUserMedia({ video: nextConstraints, audio: false })
                .then(function (s) {
                    stream = s;
                    video.srcObject = s;
                    bindVideoRecoveryHandlers();
                    return null;
                })
                .then(function () {
                    return video.play().catch(function () {
                        return null;
                    });
                })
                .then(function () {
                    revealVideoSurface();
                    if (previousStream && previousStream !== stream) {
                        previousStream.getTracks().forEach(function (track) { track.stop(); });
                    }
                })
                .catch(function () {
                    return null;
                })
                .finally(function () {
                    isRecoveringCamera = false;
                });
        }

        function scheduleCameraRecovery(forceRestart) {
            if (!running || cameraRecoveryTimeoutId) return;

            cameraRecoveryTimeoutId = window.setTimeout(function () {
                cameraRecoveryTimeoutId = 0;

                if (!running || !video) return;

                if (!forceRestart) {
                    var playPromise = video.play();
                    if (playPromise && typeof playPromise.catch === 'function') {
                        playPromise.catch(function () {
                            return null;
                        });
                    }

                    revealVideoSurface();
                }

                if (forceRestart || video.readyState === 0 || video.networkState === HTMLMediaElement.NETWORK_NO_SOURCE) {
                    restartCameraStream();
                }
            }, forceRestart ? 180 : 260);
        }

        function maximizeMobileCameraStream(s) {
            return Promise.resolve(s);
        }

        function refreshVideoIfFaceMissingTooLong() {
            if (!running || !video || !IS_MOBILE_CAMERA) return;

            var playPromise = video.play();
            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(function () {
                    return null;
                });
            }

            if (IOS_SAFARI_PERF_MODE && lostPoseFrames > (MAX_LOST_POSE_FRAMES + 12) && (video.readyState < 2 || video.videoWidth === 0)) {
                scheduleCameraRecovery(false);
            }
        }

        function setCamActiveColor(activeBtn) {
            camColorButtons.forEach(function (b) {
                b.dataset.active = 'false';
                b.setAttribute('aria-pressed', 'false');
            });
            if (!activeBtn) return;
            activeBtn.dataset.active = 'true';
            activeBtn.setAttribute('aria-pressed', 'true');
        }

        function setGafasOverlaySource(src) {
            var next = (src || '').trim();
            if (!next) {
                next = '<?php echo e($primaryImg); ?>';
            }
            if (!next) return;
            resetGafasOverlayMetrics();
            gafasImg.src = next;
        }

        function normalizeColorGroupName(raw) {
            return String(raw || '')
                .split(',')
                .map(function (part) { return String(part || '').trim().toLowerCase(); })
                .filter(function (part) { return part !== ''; })
                .sort()
                .join('|');
        }

        function syncCameraColorByName(rawName) {
            var wanted = normalizeColorGroupName(rawName);
            if (!wanted) return;

            var exact = camColorButtons.find(function (btn) {
                return normalizeColorGroupName(btn.getAttribute('data-cam-color-name') || '') === wanted;
            });

            if (exact) {
                setGafasOverlaySource(exact.getAttribute('data-cam-color-image') || '');
                setCamActiveColor(exact);
            }
        }

        function mostrarError(msg) {
            errorMsg.textContent = msg;
            errorMsg.classList.remove('hidden');
            if (noFaceMsg) noFaceMsg.classList.add('hidden');
            statusBox.classList.remove('flex');
            statusBox.classList.add('hidden');
        }

        function setStatus(txt) {
            statusText.textContent = txt;
            statusBox.classList.remove('hidden');
            statusBox.classList.add('flex');
        }

        function ocultarStatus() {
            statusBox.classList.remove('flex');
            statusBox.classList.add('hidden');
        }

        function cargarScript() {
            return new Promise(function (resolve, reject) {
                if (window.faceapi) { resolve(); return; }
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js';
                s.onload = resolve;
                s.onerror = function () { reject(new Error('No se pudo cargar face-api.js')); };
                document.head.appendChild(s);
            });
        }

        function cargarModelos() {
            if (modelsLoaded) return Promise.resolve();
            return Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODELS_URL),
                faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODELS_URL),
            ]).then(function () {
                detectorOptions = new faceapi.TinyFaceDetectorOptions({
                    inputSize: DETECTOR_INPUT_SIZE,
                    scoreThreshold: DETECTOR_SCORE_THRESHOLD
                });
                modelsLoaded = true;
            });
        }

        function smoothAngle(prev, next, alpha) {
            var tau = Math.PI * 2;
            var diff = (next - prev + Math.PI) % tau;
            if (diff < 0) diff += tau;
            diff -= Math.PI;
            return prev + (diff * alpha);
        }

        function midpoint(a, b) {
            return {
                x: (a.x + b.x) / 2,
                y: (a.y + b.y) / 2,
            };
        }

        function averagePoints(points) {
            var valid = (points || []).filter(function (point) {
                return point && Number.isFinite(point.x) && Number.isFinite(point.y);
            });
            if (!valid.length) return { x: 0, y: 0 };
            var sum = valid.reduce(function (acc, point) {
                acc.x += point.x;
                acc.y += point.y;
                return acc;
            }, { x: 0, y: 0 });
            return {
                x: sum.x / valid.length,
                y: sum.y / valid.length,
            };
        }

        function distance(a, b) {
            if (!a || !b) return 0;
            var dx = (a.x || 0) - (b.x || 0);
            var dy = (a.y || 0) - (b.y || 0);
            return Math.sqrt((dx * dx) + (dy * dy));
        }

        function clamp(value, min, max) {
            return Math.max(min, Math.min(max, value));
        }

        function alphaFromDelta(delta, baseAlpha, maxAlpha, maxDelta) {
            var safeDelta = Number.isFinite(delta) ? Math.abs(delta) : 0;
            var t = clamp(safeDelta / maxDelta, 0, 1);
            return baseAlpha + ((maxAlpha - baseAlpha) * t);
        }

        function syncOverlaySize() {
            var dw = video && video.clientWidth ? video.clientWidth : 0;
            var dh = video && video.clientHeight ? video.clientHeight : 0;
            
            // En móviles (iOS/Android), usar viewport si video no tiene dimensiones
            if (!dw && viewport) dw = viewport.offsetWidth || 0;
            if (!dh && viewport) dh = viewport.offsetHeight || 0;
            
            // En iOS Safari, a veces clientWidth falla. Usar window inner dimensions
            if (IOS_SAFARI_PERF_MODE && (!dw || !dh)) {
                dw = window.innerWidth || dw || 0;
                dh = window.innerHeight || dh || 0;
            }
            
            if (!dw || !dh) return { dw: 0, dh: 0 };

            if (overlay.width !== dw || overlay.height !== dh) {
                overlay.width = dw;
                overlay.height = dh;
                // Forzar reinicio del contexto después de cambiar tamaño en móviles
                if (IS_MOBILE_CAMERA && overlayCtx) {
                    overlayCtx = null;
                    overlayCtx = initCanvasContext();
                }
            }

            return { dw: dw, dh: dh };
        }

        function renderLoop() {
            if (!running) {
                renderFrameId = 0;
                return;
            }

            var nowRender = performance.now();
            if ((nowRender - lastRenderAt) < RENDER_INTERVAL_MS) {
                renderFrameId = requestAnimationFrame(renderLoop);
                return;
            }
            lastRenderAt = nowRender;

            var dims = syncOverlaySize();
            var dw = dims.dw;
            var dh = dims.dh;
            
            // Reintentar obtener contexto si se perdió (especialmente en iOS)
            if (!overlayCtx || !overlayCtx.canvas) {
                overlayCtx = initCanvasContext();
                if (contextLostCount < MAX_CONTEXT_LOSS_RETRIES) {
                    contextLostCount++;
                }
            }
            
            // En iOS, validar canvas explícitamente
            if (IOS_SAFARI_PERF_MODE && overlayCtx && overlayCtx.canvas) {
                if (overlayCtx.canvas.width === 0 || overlayCtx.canvas.height === 0) {
                    overlayCtx = initCanvasContext();
                }
            }
            
            var ctx = overlayCtx || overlay.getContext('2d');
            overlayCtx = ctx;

            if (!dw || !dh || !ctx) {
                renderFrameId = requestAnimationFrame(renderLoop);
                return;
            }

            // Verificar que el contexto sigue siendo válido
            try {
                ctx.clearRect(0, 0, dw, dh);
            } catch (e) {
                // Context loss detectado, reintentar
                overlayCtx = initCanvasContext();
                renderFrameId = requestAnimationFrame(renderLoop);
                return;
            }

            if (!smoothedPose || !(gafasImg.complete && gafasImg.naturalWidth > 0)) {
                renderPose = null;
                renderFrameId = requestAnimationFrame(renderLoop);
                return;
            }

            if (!renderPose) {
                renderPose = {
                    cx: smoothedPose.cx,
                    cy: smoothedPose.cy,
                    gW: smoothedPose.gW,
                    angle: smoothedPose.angle,
                };
            } else {
                renderPose.cx += (smoothedPose.cx - renderPose.cx) * RENDER_POSITION_LERP;
                renderPose.cy += (smoothedPose.cy - renderPose.cy) * RENDER_POSITION_LERP;
                renderPose.gW += (smoothedPose.gW - renderPose.gW) * RENDER_SIZE_LERP;
                renderPose.angle = smoothAngle(renderPose.angle, smoothedPose.angle, RENDER_ANGLE_LERP);
            }

            var ratio = (gafasImg.naturalWidth > 0 && gafasImg.naturalHeight > 0)
                ? gafasImg.naturalWidth / gafasImg.naturalHeight
                : 2.5;
            var visibleWidthRatio = clamp(gafasOverlayMetrics.visibleWidthRatio || 1, 0.05, 1);
            var visibleHeightRatio = clamp(gafasOverlayMetrics.visibleHeightRatio || 1, 0.05, 1);
            var drawW = renderPose.gW / visibleWidthRatio;
            var drawH = drawW / ratio;
            var visibleDrawH = drawH * visibleHeightRatio;
            var drawX = renderPose.cx;
            var drawY = renderPose.cy;
            var drawAngle = renderPose.angle;
            var centerBiasX = drawW * (gafasOverlayMetrics.centerOffsetX || 0);
            var anchorYNormalized = clamp(gafasOverlayMetrics.anchorYNormalized || GAFAS_IMAGE_ANCHOR_Y, 0.05, 0.95);
            var yBias = visibleDrawH * GAFAS_VERTICAL_OFFSET;

            try {
                ctx.save();
                ctx.translate(drawX - centerBiasX, drawY + yBias);
                ctx.rotate(drawAngle);
                ctx.drawImage(gafasImg, -drawW / 2, -drawH * anchorYNormalized, drawW, drawH);
                ctx.restore();
            } catch (e) {
                // Manejo silencioso si falla el dibujo
            }

            renderFrameId = requestAnimationFrame(renderLoop);
        }

        function detectLoop() {
            if (!running) return;
            if (video.readyState < 2) { requestAnimationFrame(detectLoop); return; }

            if (!detectorOptions) {
                detectorOptions = new faceapi.TinyFaceDetectorOptions({
                    inputSize: DETECTOR_INPUT_SIZE,
                    scoreThreshold: DETECTOR_SCORE_THRESHOLD
                });
            }

            var now = performance.now();
            if (detectInFlight) {
                requestAnimationFrame(detectLoop);
                return;
            }

            if (MOBILE_PERFORMANCE_MODE && (now - lastDetectAt) < MOBILE_DETECT_INTERVAL_MS) {
                requestAnimationFrame(detectLoop);
                return;
            }

            detectInFlight = true;
            lastDetectAt = now;

            faceapi.detectSingleFace(video, detectorOptions).withFaceLandmarks(true)
                .then(function (detection) {
                    var dims = syncOverlaySize();
                    var dw = dims.dw;
                    var dh = dims.dh;
                    if (!dw || !dh) {
                        if (running) requestAnimationFrame(detectLoop);
                        return;
                    }

                    var vw = video.videoWidth || dw;
                    var vh = video.videoHeight || dh;
                    var fitScale = VIDEO_FIT_MODE === 'contain'
                        ? Math.min(dw / vw, dh / vh)
                        : Math.max(dw / vw, dh / vh);
                    var renderW = vw * fitScale;
                    var renderH = vh * fitScale;
                    var offsetX = (dw - renderW) / 2;
                    var offsetY = (dh - renderH) / 2;

                    function mapPoint(p) {
                        return {
                            x: (p.x * fitScale) + offsetX,
                            y: (p.y * fitScale) + offsetY,
                        };
                    }

                    var faces = detection ? [detection] : [];

                    if (!faces.length) {
                        lostPoseFrames += 1;
                        if (lostPoseFrames > MAX_LOST_POSE_FRAMES) {
                            smoothedPose = null;
                            renderPose = null;
                            neutralEyeSpan = null;
                        }
                        refreshVideoIfFaceMissingTooLong();
                        noFaceFrames += 1;
                        if (noFaceMsg) {
                            if (noFaceFrames >= 8) {
                                noFaceMsg.classList.remove('hidden');
                            } else {
                                noFaceMsg.classList.add('hidden');
                            }
                        }
                        if (running) requestAnimationFrame(detectLoop);
                        return;
                    }

                    noFaceFrames = 0;
                    lostPoseFrames = 0;
                    if (noFaceMsg) noFaceMsg.classList.add('hidden');

                    faces.slice(0, 1).forEach(function (det) {
                        var lm = det.landmarks;
                        var le = lm.getLeftEye().map(mapPoint);   // puntos 36-41
                        var re = lm.getRightEye().map(mapPoint);  // puntos 42-47
                        var nose = lm.getNose().map(mapPoint);
                        var lb = lm.getLeftEyeBrow().map(mapPoint);
                        var rb = lm.getRightEyeBrow().map(mapPoint);

                        var leftOuterCorner = le[0] || le[1] || { x: 0, y: 0 };
                        var leftInnerCorner = le[3] || le[2] || leftOuterCorner;
                        var rightInnerCorner = re[0] || re[1] || { x: 0, y: 0 };
                        var rightOuterCorner = re[3] || re[2] || rightInnerCorner;

                        // Centro de cada ojo para seguir mejor subidas/bajadas de cabeza.
                        var leftEyeCenter = le.reduce(function (acc, point) {
                            acc.x += point.x;
                            acc.y += point.y;
                            return acc;
                        }, { x: 0, y: 0 });
                        leftEyeCenter.x /= le.length;
                        leftEyeCenter.y /= le.length;

                        var rightEyeCenter = re.reduce(function (acc, point) {
                            acc.x += point.x;
                            acc.y += point.y;
                            return acc;
                        }, { x: 0, y: 0 });
                        rightEyeCenter.x /= re.length;
                        rightEyeCenter.y /= re.length;

                        // Anclar sobre una linea estable entre esquinas internas/externas de los ojos.
                        var innerEyeMid = midpoint(leftInnerCorner, rightInnerCorner);
                        var eyeMid = midpoint(leftEyeCenter, rightEyeCenter);

                        // Rotacion real segun la inclinacion de la cabeza usando extremos externos.
                        var angle = Math.atan2(rightOuterCorner.y - leftOuterCorner.y, rightOuterCorner.x - leftOuterCorner.x);

                        // Distancia real entre ojos para que no se encoja raro al girar.
                        var span = Math.sqrt(
                            Math.pow(rightOuterCorner.x - leftOuterCorner.x, 2) +
                            Math.pow(rightOuterCorner.y - leftOuterCorner.y, 2)
                        );

                        if (!neutralEyeSpan) {
                            neutralEyeSpan = span;
                        } else {
                            neutralEyeSpan += (span - neutralEyeSpan) * SPAN_ADAPT_ALPHA;
                        }

                        var spanRatio = neutralEyeSpan > 0 ? (neutralEyeSpan / span) : 1;
                        var distanceCompensation = clamp(Math.pow(spanRatio, 0.35), 0.9, 1.12);
                        var gW = span * GAFAS_WIDTH_MULTIPLIER * distanceCompensation;

                        // Puente nasal robusto: promedia varios puntos de la nariz para evitar saltos.
                        var noseBridgePoint = averagePoints([
                            nose[0],
                            nose[1],
                            nose[2],
                            innerEyeMid,
                        ]);
                        var noseLowerPoint = nose[6] || nose[3] || noseBridgePoint;
                        var noseBridgeDepth = Math.max(0, noseLowerPoint.y - noseBridgePoint.y);

                        var leftBridgeDist = distance(leftInnerCorner, noseBridgePoint);
                        var rightBridgeDist = distance(rightInnerCorner, noseBridgePoint);
                        var yawDen = Math.max(1, leftBridgeDist + rightBridgeDist);
                        var yawOffset = clamp((rightBridgeDist - leftBridgeDist) / yawDen, -0.18, 0.18);

                        var cx = noseBridgePoint.x + (yawOffset * span * 0.06);
                        var leftBrowCenter = averagePoints(lb);
                        var rightBrowCenter = averagePoints(rb);
                        var browMid = midpoint(leftBrowCenter, rightBrowCenter);

                        var browBridgeDelta = Math.max(0, noseBridgePoint.y - browMid.y);
                        var browAwareY = browMid.y + (browBridgeDelta * GAFAS_BROW_BRIDGE_BLEND);
                        var eyeAwareY = eyeMid.y + ((noseBridgePoint.y - eyeMid.y) * GAFAS_EYELINE_BLEND);
                        var mixedTargetY = (browAwareY * 0.68) + (eyeAwareY * 0.32);
                        var browMaxY = browMid.y + (browBridgeDelta * GAFAS_MAX_BELOW_BROW_RATIO);
                        var cy = Math.min(mixedTargetY, browMaxY) - (noseBridgeDepth * GAFAS_CENTER_BIAS) - (span * GAFAS_UPLIFT_FACTOR);

                        if (!smoothedPose) {
                            smoothedPose = { cx: cx, cy: cy, gW: gW, angle: angle };
                        } else {
                            var dx = cx - smoothedPose.cx;
                            var dy = cy - smoothedPose.cy;
                            var movement = Math.sqrt((dx * dx) + (dy * dy));
                            var positionAlpha = alphaFromDelta(movement, POSE_BASE_ALPHA, POSE_MAX_ALPHA, IS_MOBILE_CAMERA ? 42 : 32);
                            var sizeAlpha = alphaFromDelta(gW - smoothedPose.gW, SIZE_BASE_ALPHA, SIZE_MAX_ALPHA, IS_MOBILE_CAMERA ? 56 : 44);
                            var angleDelta = Math.abs(angle - smoothedPose.angle);
                            var angleAlpha = alphaFromDelta(angleDelta, ANGLE_BASE_ALPHA, ANGLE_MAX_ALPHA, 0.75);

                            smoothedPose.cx += dx * positionAlpha;
                            smoothedPose.cy += dy * positionAlpha;
                            smoothedPose.gW += (gW - smoothedPose.gW) * sizeAlpha;
                            smoothedPose.angle = smoothAngle(smoothedPose.angle, angle, angleAlpha);
                        }

                    });

                    if (running) requestAnimationFrame(detectLoop);
                })
                .catch(function () {
                    if (running) requestAnimationFrame(detectLoop);
                })
                .finally(function () {
                    detectInFlight = false;
                });
        }

        function hideBackgroundNav() {
            backgroundNavElements.forEach(function (el) {
                if (!el) return;
                el.dataset.camPrevVisibility = el.style.visibility || '';
                el.dataset.camPrevOpacity = el.style.opacity || '';
                el.dataset.camPrevPointerEvents = el.style.pointerEvents || '';
                el.style.visibility = 'hidden';
                el.style.opacity = '0';
                el.style.pointerEvents = 'none';
            });
        }

        function restoreBackgroundNav() {
            backgroundNavElements.forEach(function (el) {
                if (!el) return;
                el.style.visibility = el.dataset.camPrevVisibility || '';
                el.style.opacity = el.dataset.camPrevOpacity || '';
                el.style.pointerEvents = el.dataset.camPrevPointerEvents || '';
                delete el.dataset.camPrevVisibility;
                delete el.dataset.camPrevOpacity;
                delete el.dataset.camPrevPointerEvents;
            });
        }

        function hideNavbarWhileCameraOpen() {
            navbarElements.forEach(function (el) {
                if (!el) return;
                el.dataset.camPrevVisibility = el.style.visibility || '';
                el.dataset.camPrevOpacity = el.style.opacity || '';
                el.style.visibility = 'hidden';
                el.style.opacity = '0';
            });
        }

        function restoreNavbarAfterCameraClose() {
            navbarElements.forEach(function (el) {
                if (!el) return;
                el.style.visibility = el.dataset.camPrevVisibility || '';
                el.style.opacity = el.dataset.camPrevOpacity || '';
                delete el.dataset.camPrevVisibility;
                delete el.dataset.camPrevOpacity;
            });
        }

        function abrirCamara() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            hideBackgroundNav();
            
            // Limpiar intervalo de validación de iOS
            if (contextValidationIntervalId) {
                clearInterval(contextValidationIntervalId);
                contextValidationIntervalId = 0;
            }
            
            hideNavbarWhileCameraOpen();
            errorMsg.classList.add('hidden');
            if (noFaceMsg) noFaceMsg.classList.add('hidden');
            noFaceFrames = 0;

            // Al abrir, alinear la gafa virtual con el color seleccionado en la ficha.
            if (sidebarColorLabel) {
                syncCameraColorByName(sidebarColorLabel.textContent || '');
            }

            setStatus('Cargando detección facial…');

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                mostrarError('Tu navegador no soporta acceso a la cámara.');
                return;
            }

            var videoConstraints;
            if (IOS_SAFARI_PERF_MODE) {
                videoConstraints = {
                    facingMode: 'user',
                    width: { ideal: 720, max: 1280 },
                    height: { ideal: 960, max: 1280 },
                    aspectRatio: { ideal: 0.75 },
                    frameRate: { ideal: 30, max: 30 }
                };
            } else {
                videoConstraints = {
                    facingMode: 'user',
                    width: { ideal: IS_MOBILE_CAMERA ? 720 : 1280, max: IS_MOBILE_CAMERA ? 1280 : 1920 },
                    height: { ideal: IS_MOBILE_CAMERA ? 960 : 720, max: IS_MOBILE_CAMERA ? 1280 : 1080 },
                    frameRate: IS_MOBILE_CAMERA ? { ideal: (IS_ANDROID_DEVICE ? 24 : 30), max: 30 } : { ideal: 30, max: 30 }
                };
                if (IS_MOBILE_CAMERA) {
                    videoConstraints.aspectRatio = { ideal: 0.75 };
                }
            }
            currentVideoConstraints = videoConstraints;

            if (video) {
                video.style.opacity = '1';
                video.style.visibility = 'visible';
                bindVideoRecoveryHandlers();
            }

            var camPromise = navigator.mediaDevices
                .getUserMedia({
                    video: videoConstraints,
                    audio: false
                })
                .then(function (s) {
                    stream = s;
                    video.srcObject = s;
                    return new Promise(function (res, rej) {
                        var done = false;
                        var timeoutId = window.setTimeout(function () {
                            if (done) return;
                            done = true;
                            rej(new Error('No se pudo iniciar la cámara a tiempo.'));
                        }, 8000);

                        video.onloadedmetadata = function () {
                            if (done) return;
                            revealVideoSurface();
                            var playPromise = video.play();
                            if (playPromise && typeof playPromise.then === 'function') {
                                playPromise.then(function () {
                                    if (done) return;
                                    revealVideoSurface();
                                    done = true;
                                    window.clearTimeout(timeoutId);
                                    res();
                                }).catch(function () {
                                    if (done) return;
                                    revealVideoSurface();
                                    done = true;
                                    window.clearTimeout(timeoutId);
                                    res();
                                });
                                return;
                            }

                            revealVideoSurface();
                            done = true;
                            window.clearTimeout(timeoutId);
                            res();
                        };
                    });
                });

            var modelPromise = cargarScript().then(cargarModelos);

            Promise.all([camPromise, modelPromise])
                .then(function () {
                    ocultarStatus();
                    running = true;
                    lastRenderAt = 0;
                    
                    // En iOS, validar contexto continuamente
                    if (IOS_SAFARI_PERF_MODE || IS_SAFARI_BROWSER) {
                        if (contextValidationIntervalId) clearInterval(contextValidationIntervalId);
                        contextValidationIntervalId = setInterval(validateAndRestoreContext, 200);
                    }
                    
                    // Agregar listeners para cambios de orientación en Android
                    if (IS_MOBILE_CAMERA && !window.__cameraOrientationListenerAdded) {
                        window.__cameraOrientationListenerAdded = true;
                        window.addEventListener('orientationchange', function () {
                            overlayCtx = null;
                            overlayCtx = initCanvasContext();
                            syncOverlaySize();
                        });
                        window.addEventListener('resize', function () {
                            if (running && IS_MOBILE_CAMERA) {
                                syncOverlaySize();
                            }
                        }, { passive: true });
                    }
                    
                    if (!renderFrameId) {
                        renderFrameId = requestAnimationFrame(renderLoop);
                    }
                    detectLoop();
                })
                .catch(function (err) {
                    var msgs = {
                        NotAllowedError: 'Permiso denegado. Permite el acceso a la cámara en tu navegador.',
                        NotFoundError: 'No se encontró ninguna cámara en este dispositivo.',
                        NotReadableError: 'La cámara está siendo usada por otra aplicación.',
                    };
                    mostrarError(msgs[err.name] || 'Error: ' + (err.message || err));
                });
        }

        function cerrarCamara() {
            running = false;
            detectInFlight = false;
            lastDetectAt = 0;
            lastRenderAt = 0;
            smoothedPose = null;
            renderPose = null;
            neutralEyeSpan = null;
            lostPoseFrames = 0;
            noFaceFrames = 0;
            clearCameraRecovery();
            isRecoveringCamera = false;
            if (renderFrameId) {
                cancelAnimationFrame(renderFrameId);
                renderFrameId = 0;
            }
            if (noFaceMsg) noFaceMsg.classList.add('hidden');
            if (stream) {
                stream.getTracks().forEach(function (t) { t.stop(); });
                stream = null;
            }
            video.srcObject = null;
            video.style.opacity = '1';
            video.style.visibility = 'visible';
            resetViewportAspect();
            var ctx = overlay.getContext('2d');
            ctx.clearRect(0, 0, overlay.width, overlay.height);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            restoreBackgroundNav();
            restoreNavbarAfterCameraClose();
        }

        camColorButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var src = btn.getAttribute('data-cam-color-image') || '';
                setGafasOverlaySource(src);
                setCamActiveColor(btn);
            });
        });

        mainColorButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var selectedName = btn.getAttribute('data-color-name') || '';
                syncCameraColorByName(selectedName);
            });
        });

        var initialCamColorBtn = camColorButtons.find(function (b) {
            return b.dataset.active === 'true';
        }) || camColorButtons[0];
        if (initialCamColorBtn) {
            setGafasOverlaySource(initialCamColorBtn.getAttribute('data-cam-color-image') || '');
            setCamActiveColor(initialCamColorBtn);
        }

        btnAbrir.addEventListener('click', abrirCamara);
        btnCerrar.addEventListener('click', cerrarCamara);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) cerrarCamara();
        });
    })();
    </script>
<?php endif; ?>

<script>
    (() => {
        const blockedCombo = (event) => {
            const key = String(event.key || '').toLowerCase();
            const ctrlOrCmd = event.ctrlKey || event.metaKey;

            if (key === 'f12') {
                return true;
            }

            if (ctrlOrCmd && event.shiftKey && ['i', 'j', 'c'].includes(key)) {
                return true;
            }

            if (ctrlOrCmd && key === 'u') {
                return true;
            }

            return false;
        };

        document.addEventListener('keydown', (event) => {
            if (!blockedCombo(event)) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            window.alert('Acción no disponible en esta página.');
        }, true);

        document.addEventListener('contextmenu', (event) => {
            event.preventDefault();
            event.stopPropagation();
        }, true);
    })();
</script>
</body>
</html>
<?php /**PATH C:\Users\norla\Desktop\Proyectos con Ronaldo\copia_optica\resources\views/gafas/show.blade.php ENDPATH**/ ?>