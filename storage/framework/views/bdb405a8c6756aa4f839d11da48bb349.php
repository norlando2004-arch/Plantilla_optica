<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
        $browserTitle = $browserTitle ?? 'Gafas — Óptica';
        $pageTitle = $pageTitle ?? 'Gafas';
        $pageSubtitle = $pageSubtitle ?? 'Explora todas las colecciones';
    ?>
    <title><?php echo e($browserTitle); ?></title>

    <?php
        $viteHot = public_path('hot');
        $viteManifest = public_path('build/manifest.json');
        $hasViteAssets = file_exists($viteHot) || file_exists($viteManifest);
    ?>

    <?php if($hasViteAssets): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php endif; ?>

    <?php if(!$hasViteAssets || app()->isLocal()): ?>
        <script src="https://cdn.tailwindcss.com"></script>
    <?php endif; ?>

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

        .landing-global-loading-spinner {
            width: 5rem;
            height: 5rem;
            border-radius: 9999px;
            border: 5px solid rgba(63, 127, 95, 0.18);
            border-top-color: #3f7f5f;
            animation: landing-spinner-spin 0.75s linear infinite;
            margin: 0 auto 0.25rem;
            filter: drop-shadow(0 4px 10px rgba(28, 18, 8, 0.14));
        }

        @keyframes landing-spinner-spin {
            to { transform: rotate(360deg); }
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

            .landing-global-loading-spinner {
                width: 4rem;
                height: 4rem;
            }

            .landing-global-loading-label {
                font-size: 0.98rem;
            }
        }
    </style>
</head>
<body class="overflow-x-hidden bg-white text-zinc-900 antialiased font-sans">
<div id="landingGlobalLoading"
     class="landing-global-loading"
     aria-hidden="true"
     role="status"
     aria-live="polite"
     data-loading-overlay>
    <div class="landing-global-loading-content">
        <div class="landing-global-loading-spinner" aria-hidden="true"></div>
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
<?php
    $routeName = request()->route()?->getName();
    $minPrice = request()->query('min_price');
    $maxPrice = request()->query('max_price');
    $routePreselect = [
        'gafas-mujeres.index' => ['mujeres'],
        'gafas-hombre.index' => ['hombre'],
        'gafas-ninos.index' => ['ninos'],
        'gafas-ninas.index' => ['ninas'],
        'gafas-polarizadas.index' => ['polarizadas'],
        'gafas-deportivas.index' => ['deportivas'],
        'gafas-descanso.index' => ['deportivas'],
    ];
    $selectedCategories = (is_array($selectedCategories ?? null) && count($selectedCategories) > 0)
        ? $selectedCategories
        : ($routePreselect[$routeName] ?? []);
    $progresivosFilterActive = (bool) ($progresivosFilter ?? false);
    $sliderMax = (int) ceil((float) ($maxAvailablePrice ?? 0));
    $sliderMax = $sliderMax > 0 ? $sliderMax : 200000;
    $sliderMinValue = $minPriceFilter !== null ? (int) $minPriceFilter : 0;
    $sliderMaxValue = $maxPriceFilter !== null ? (int) $maxPriceFilter : $sliderMax;
    $sliderMinValue = max(0, min($sliderMinValue, $sliderMax));
    $sliderMaxValue = max(0, min($sliderMaxValue, $sliderMax));
    if ($sliderMinValue > $sliderMaxValue) {
        $sliderMinValue = $sliderMaxValue;
    }
    $sliderStep = max(1000, (int) round($sliderMax / 200));
?>

<?php echo $__env->make('partials.store-navbar', [
    'storeBannerImage' => $storeBannerImage ?? null,
    'storeBannerImages' => $storeBannerImages ?? [],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<main class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-6 text-center">
        <p class="text-base font-semibold text-zinc-500">Óptica</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-4xl"><?php echo e($pageTitle); ?></h1>
        <p class="mt-2 text-base text-zinc-500"><?php echo e($pageSubtitle); ?></p>
    </div>

    <div class="mb-4">
        <button
            type="button"
            data-filter-toggle
            aria-controls="mobile-filters-panel"
            aria-expanded="false"
            class="inline-flex items-center gap-2 rounded-full border-[3px] border-zinc-700 bg-white px-5 py-2.5 text-base font-semibold leading-none text-zinc-800 md:px-6 md:py-3 md:text-lg"
        >
            <svg viewBox="0 0 24 24" class="h-6 w-6 md:h-7 md:w-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 5h18l-7 8v5l-4 2v-7z"></path>
            </svg>
            <span>Filtros</span>
        </button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.querySelector('[data-filter-toggle]');
            const panel = document.getElementById('mobile-filters-panel');
            const closeBtn = panel ? panel.querySelector('[data-filter-close]') : null;
            const filterCard = panel ? panel.querySelector('[data-filters-card]') : null;
            if (!toggle || !panel) return;

            const fitDesktopFilterCard = () => {
                if (!filterCard) return;

                filterCard.style.transform = '';
                filterCard.style.transformOrigin = '';

                if (window.matchMedia('(max-width: 767px)').matches) {
                    return;
                }

                const panelStyle = window.getComputedStyle(panel);
                const padTop = parseFloat(panelStyle.paddingTop) || 0;
                const padBottom = parseFloat(panelStyle.paddingBottom) || 0;
                const padLeft = parseFloat(panelStyle.paddingLeft) || 0;
                const padRight = parseFloat(panelStyle.paddingRight) || 0;

                const availableHeight = Math.max(240, window.innerHeight - padTop - padBottom - 8);
                const availableWidth = Math.max(260, window.innerWidth - padLeft - padRight - 8);
                const naturalHeight = Math.max(1, filterCard.scrollHeight);
                const naturalWidth = Math.max(1, filterCard.scrollWidth);

                const scale = Math.min(1, availableHeight / naturalHeight, availableWidth / naturalWidth);
                if (scale < 1) {
                    filterCard.style.transformOrigin = 'top center';
                    filterCard.style.transform = `scale(${scale})`;
                }
            };

            const open = () => {
                panel.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                window.requestAnimationFrame(fitDesktopFilterCard);
                sync();
            };

            const close = () => {
                panel.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                sync();
            };

            const sync = () => {
                const isOpen = !panel.classList.contains('hidden');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            };

            toggle.addEventListener('click', () => {
                if (panel.classList.contains('hidden')) {
                    open();
                } else {
                    close();
                }
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', close);
            }

            panel.addEventListener('click', (e) => {
                if (e.target === panel) close();
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') close();
            });

            window.addEventListener('resize', () => {
                if (panel.classList.contains('hidden')) return;
                fitDesktopFilterCard();
            });

            sync();
        });
    </script>

    <div class="grid gap-6">
        <aside id="mobile-filters-panel" class="hidden fixed inset-0 z-[90] items-start justify-center overflow-hidden bg-black/35 p-2.5 sm:p-4 md:items-center" style="padding-top: max(0.625rem, env(safe-area-inset-top)); padding-right: max(0.625rem, env(safe-area-inset-right)); padding-bottom: max(0.625rem, env(safe-area-inset-bottom)); padding-left: max(0.625rem, env(safe-area-inset-left));">
            <div data-filters-card class="relative flex w-full max-w-[22rem] flex-col overflow-hidden rounded-[1.75rem] border border-zinc-200 bg-white p-3.5 sm:max-w-sm sm:rounded-3xl sm:p-5">
                <div class="mb-1.5 flex items-center justify-between gap-3 sm:mb-2">
                    <p class="text-sm font-semibold text-zinc-900">Filtrar</p>
                    <button type="button" data-filter-close class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50 sm:h-9 sm:w-9" aria-label="Cerrar filtros">✕</button>
                </div>

                <form id="gafasFiltersForm" method="GET" action="<?php echo e(url()->current()); ?>" class="mt-3 pr-1 sm:mt-4 md:pr-2" data-auto-submit-filters>
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Precio</p>
                    <div class="mt-2.5 grid gap-2.5 sm:mt-3 sm:gap-3">
                        <div data-price-filter-form>
                            <div class="grid gap-2.5 sm:gap-3">
                                <div>
                                    <div class="flex items-center justify-between gap-3">
                                        <label for="priceMin" class="text-xs font-semibold text-zinc-700">Desde</label>
                                        <span id="priceMinValue" class="text-xs font-semibold text-zinc-900"><?php echo e(number_format($sliderMinValue, 0, ',', '.')); ?></span>
                                    </div>
                                    <input
                                        id="priceMin"
                                        type="range"
                                        name="min_price"
                                        min="0"
                                        max="<?php echo e($sliderMax); ?>"
                                        step="<?php echo e($sliderStep); ?>"
                                        value="<?php echo e($sliderMinValue); ?>"
                                        class="mt-2 w-full accent-zinc-900 sm:mt-3"
                                        aria-label="Filtrar por precio mínimo"
                                    />
                                </div>
                                <div>
                                    <div class="flex items-center justify-between gap-3">
                                        <label for="priceMax" class="text-xs font-semibold text-zinc-700">Hasta</label>
                                        <span id="priceMaxValue" class="text-xs font-semibold text-zinc-900"><?php echo e(number_format($sliderMaxValue, 0, ',', '.')); ?></span>
                                    </div>
                                    <input
                                        id="priceMax"
                                        type="range"
                                        name="max_price"
                                        min="0"
                                        max="<?php echo e($sliderMax); ?>"
                                        step="<?php echo e($sliderStep); ?>"
                                        value="<?php echo e($sliderMaxValue); ?>"
                                        class="mt-2 w-full accent-zinc-900 sm:mt-3"
                                        aria-label="Filtrar por precio máximo"
                                    />
                                </div>

                                <p class="text-xs text-zinc-500">Mueve las barras para filtrar.</p>
                            </div>
                        </div>
                    </div>

                <script>
                    (() => {
                        const wrapper = document.querySelector('[data-price-filter-form]');
                        if (!wrapper) return;

                        const form = wrapper.closest('form');
                        const minRange = wrapper.querySelector('#priceMin');
                        const maxRange = wrapper.querySelector('#priceMax');
                        const outMin = wrapper.querySelector('#priceMinValue');
                        const outMax = wrapper.querySelector('#priceMaxValue');
                        if (!form || !minRange || !maxRange || !outMin || !outMax) return;

                        const fmt = (n) => new Intl.NumberFormat('es-CO').format(n);

                        const clampPair = () => {
                            const minV = Number(minRange.value || 0);
                            const maxV = Number(maxRange.value || 0);
                            if (minV > maxV) {
                                // Keep the last changed range, but ensure min <= max.
                                if (document.activeElement === minRange) {
                                    maxRange.value = String(minV);
                                } else {
                                    minRange.value = String(maxV);
                                }
                            }
                        };

                        const onInput = () => {
                            clampPair();
                            outMin.textContent = fmt(Number(minRange.value || 0));
                            outMax.textContent = fmt(Number(maxRange.value || 0));
                        };

                        minRange.addEventListener('input', onInput);
                        maxRange.addEventListener('input', onInput);

                        onInput();

                        form.addEventListener('submit', (e) => {
                            const btn = e.submitter;
                            if (!btn || !btn.matches('[data-loading]')) return;
                            btn.disabled = true;
                            btn.textContent = 'Cargando...';
                        });
                    })();
                </script>

                <div class="mt-4 border-t border-zinc-200 pt-4 sm:mt-6 sm:pt-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Categorías</p>
                    <?php
                        $cats = [
                            ['label' => 'Niños', 'value' => 'ninos'],
                            ['label' => 'Hombre', 'value' => 'hombre'],
                            ['label' => 'Mujeres', 'value' => 'mujeres'],
                            ['label' => 'Deportivas', 'value' => 'deportivas'],
                            ['label' => 'Polarizadas', 'value' => 'polarizadas'],
                        ];
                    ?>

                    <div class="mt-2.5 grid gap-2 sm:mt-3">
                        <?php $__currentLoopData = $cats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isActive = in_array($cat['value'], $selectedCategories, true);
                            ?>
                            <label class="block cursor-pointer">
                                <input type="checkbox" name="categories[]" value="<?php echo e($cat['value']); ?>" class="peer sr-only" <?php echo e($isActive ? 'checked' : ''); ?>>
                                <span class="flex items-center gap-2.5 rounded-[1.15rem] border border-zinc-200 bg-white px-3 py-2.5 text-[13px] font-semibold text-zinc-900 transition hover:bg-zinc-50 sm:gap-3 sm:rounded-2xl sm:px-4 sm:py-3 sm:text-sm peer-checked:border-zinc-950 peer-checked:bg-zinc-950 peer-checked:text-white peer-checked:[&_.js-filter-check]:border-zinc-900 peer-checked:[&_.js-filter-check]:text-zinc-900">
                                    <span class="js-filter-check inline-flex h-4 w-4 items-center justify-center rounded border border-zinc-300 bg-white text-transparent text-[11px] font-bold leading-none">
                                        ✔
                                    </span>
                                    <span><?php echo e($cat['label']); ?></span>
                                </span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <div class="mt-4 border-t border-zinc-200 pt-4 sm:mt-5 sm:pt-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Compatibilidad</p>
                        <div class="mt-2.5 grid gap-2 sm:mt-3">
                            <label class="block cursor-pointer">
                                <input type="checkbox" name="progresivos" value="1" class="peer sr-only" <?php echo e($progresivosFilterActive ? 'checked' : ''); ?>>
                                <span class="flex items-center gap-2.5 rounded-[1.15rem] border border-zinc-200 bg-white px-3 py-2.5 text-[13px] font-semibold text-zinc-900 transition hover:bg-zinc-50 sm:gap-3 sm:rounded-2xl sm:px-4 sm:py-3 sm:text-sm peer-checked:border-zinc-950 peer-checked:bg-zinc-950 peer-checked:text-white peer-checked:[&_.js-filter-check]:border-zinc-900 peer-checked:[&_.js-filter-check]:text-zinc-900">
                                    <span class="js-filter-check inline-flex h-4 w-4 items-center justify-center rounded border border-zinc-300 bg-white text-transparent text-[11px] font-bold leading-none">
                                        ✔
                                    </span>
                                    <span>Progresivos (Sí)</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2 sm:mt-5">
                        <button type="submit" data-loading class="inline-flex items-center justify-center rounded-[1.15rem] bg-zinc-950 px-3 py-2.5 text-[13px] font-semibold text-white hover:bg-zinc-900 sm:rounded-2xl sm:px-4 sm:py-3 sm:text-sm">
                            Aplicar filtros
                        </button>
                        <a href="<?php echo e(route('gafas.index')); ?>" class="inline-flex items-center justify-center rounded-[1.15rem] border border-zinc-200 bg-white px-3 py-2.5 text-[13px] font-semibold text-zinc-900 hover:bg-zinc-50 sm:rounded-2xl sm:px-4 sm:py-3 sm:text-sm">
                            Limpiar
                        </a>
                    </div>
                </form>
                </div>
            </div>
        </aside>

        <section class="relative">
            <img
                src="<?php echo e(asset('images/derchaimagen.png')); ?>"
                alt=""
                class="pointer-events-none absolute right-[calc((100vw-100%)/-2)] -top-28 z-20 w-[52px] sm:-top-32 sm:w-[62px] md:-top-36 md:w-[74px]"
                aria-hidden="true"
                loading="lazy"
                decoding="async"
            >

            <img
                src="<?php echo e(asset('images/izquierdaimagen.png')); ?>"
                alt=""
                class="pointer-events-none absolute left-[calc((100vw-100%)/-2)] top-[58%] z-20 w-[52px] sm:w-[62px] md:w-[74px]"
                aria-hidden="true"
                loading="lazy"
                decoding="async"
            >

            <img
                src="<?php echo e(asset('images/derechaimagen2.png')); ?>"
                alt=""
                class="pointer-events-none absolute right-[calc((100vw-100%)/-2)] top-[58%] z-20 w-[46px] sm:w-[56px] md:w-[66px]"
                aria-hidden="true"
                loading="lazy"
                decoding="async"
            >

            <?php if($productos->count() === 0): ?>
                <div class="rounded-3xl border border-zinc-200 bg-white p-8 text-center">
                    <p class="text-base font-semibold">No encontramos productos</p>
                    <p class="mt-2 text-sm text-zinc-500">Prueba ajustando el rango de precios.</p>
                </div>
            <?php else: ?>
                <div class="mb-4 flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-zinc-700">
                        Mostrando <span class="text-zinc-900"><?php echo e($productos->count()); ?></span> de <span class="text-zinc-900"><?php echo e($productos->total()); ?></span>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-x-3 gap-y-8 sm:grid-cols-3 sm:gap-x-5">
                    <?php $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $meta = is_array($producto->meta) ? $producto->meta : [];
                            $img = (string) ($meta['imagen_url'] ?? '');
                            $img2 = (string) ($meta['imagen_url_2'] ?? '');
                            $hasSecond = $img2 !== '';

                            $midZoom = 0.99;
                            $posX = (float) ($meta['image_pos_x'] ?? 50);
                            $posY = (float) ($meta['image_pos_y'] ?? 50);
                            $zoom = (float) ($meta['image_zoom'] ?? $midZoom);
                            $zoom = max(0.5, min(1, $zoom));
                            $t = max(0, min(1, ($zoom - 0.5) / 0.5));
                            $posXEff = 50 + (($posX - 50) * $t);
                            $posYEff = 50 + (($posY - 50) * $t);

                            $precio = $producto->precio;
                            $precioOferta = $producto->precio_oferta;
                            $isFavorited = in_array($producto->id, $favoriteProductIds ?? []);
                        ?>

                        <?php
                            $colorVariants = [];
                            $rawSwatches = $meta['colores'] ?? ($meta['color_variants'] ?? null);
                            $primaryColor = trim((string) ($meta['color'] ?? ($producto->color ?? '')));
                            $primaryImage = trim((string) ($img ?? ''));

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

                            $pushVariant = static function (array $entry) use (&$colorVariants, $namedColorHex): void {
                                $name = trim((string) ($entry['name'] ?? ''));
                                $hex = trim((string) ($entry['hex'] ?? ''));
                                $image = trim((string) ($entry['image'] ?? ''));

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

                                if (preg_match('/^#[0-9A-Fa-f]{6}$/', $hex) !== 1) {
                                    return;
                                }

                                $keyBase = strtolower(trim($name));
                                $keyImage = strtolower($image);
                                $key = $keyImage !== '' ? ($keyBase . '||' . $keyImage) : $keyBase;

                                if (!isset($colorVariants[$key])) {
                                    $colorVariants[$key] = [
                                        'name' => $name,
                                        'hex' => $hex,
                                        'image' => $image,
                                    ];
                                }
                            };

                            $hasStructuredVariants = is_array($meta['color_variants'] ?? null)
                                && collect($meta['color_variants'])->contains(fn ($variant) => is_array($variant));

                            if (! $hasStructuredVariants && $primaryColor !== '') {
                                $pushVariant([
                                    'name' => $primaryColor,
                                    'hex' => $namedColorHex[strtolower($primaryColor)] ?? '#cec9bc',
                                    'image' => $primaryImage,
                                ]);
                            }

                            if (is_array($meta['color_variants'] ?? null)) {
                                foreach (($meta['color_variants'] ?? []) as $variant) {
                                    if (!is_array($variant)) {
                                        continue;
                                    }

                                    $name = trim((string) ($variant['name'] ?? ''));
                                    $variantHex = trim((string) ($variant['hex'] ?? ''));
                                    $variantImage = trim((string) ($variant['image'] ?? ''));
                                    $variantImages = [];
                                    foreach ((array) ($variant['images'] ?? []) as $candidate) {
                                        $candidate = trim((string) $candidate);
                                        if ($candidate !== '' && !in_array($candidate, $variantImages, true)) {
                                            $variantImages[] = $candidate;
                                        }
                                    }
                                    if ($variantImage === '' && $variantImages !== []) {
                                        $variantImage = $variantImages[0];
                                    }

                                    $variantNames = preg_split('/\s*,\s*/', $name) ?: [];
                                    $variantNames = array_values(array_filter(array_map(static fn ($part) => trim((string) $part), $variantNames), static fn ($part) => $part !== ''));

                                    if ($variantNames === []) {
                                        $variantNames = [$name !== '' ? $name : 'Color'];
                                    }

                                    $isMultiNameVariant = count($variantNames) > 1;
                                    foreach ($variantNames as $variantName) {
                                        $resolvedHex = $isMultiNameVariant
                                            ? ($namedColorHex[strtolower($variantName)] ?? ($variantHex !== '' ? $variantHex : '#d9d3c5'))
                                            : ($variantHex !== '' ? $variantHex : ($namedColorHex[strtolower($variantName)] ?? '#d9d3c5'));

                                        $pushVariant([
                                            'name' => $variantName,
                                            'hex' => $resolvedHex,
                                            'image' => $variantImage,
                                        ]);
                                    }
                                }
                            } elseif (is_string($rawSwatches) && trim($rawSwatches) !== '') {
                                $parts = preg_split('/[,\n|]+/', $rawSwatches) ?: [];
                                foreach ($parts as $part) {
                                    $c = ltrim(trim((string) $part), '#');
                                    if ($c !== '') {
                                        $pushVariant([
                                            'name' => 'Color',
                                            'hex' => '#' . $c,
                                            'image' => '',
                                        ]);
                                    }
                                }
                            } elseif (is_array($rawSwatches)) {
                                foreach ($rawSwatches as $part) {
                                    $c = ltrim(trim((string) $part), '#');
                                    if ($c !== '') {
                                        $pushVariant([
                                            'name' => 'Color',
                                            'hex' => '#' . $c,
                                            'image' => '',
                                        ]);
                                    }
                                }
                            }

                            $colorVariants = array_values(array_slice($colorVariants, 0, 20));

                            if (count($colorVariants) === 0) {
                                $fallbackName = $primaryColor !== '' ? $primaryColor : 'Gris';
                                $fallbackHex = $namedColorHex[strtolower($fallbackName)] ?? '#cec9bc';
                                $colorVariants = [
                                    ['name' => $fallbackName, 'hex' => $fallbackHex, 'image' => $img],
                                ];
                            }

                            // Agrupar variantes por imagen: colores que comparten la misma imagen son una combinación
                            $colorGroups = [];
                            foreach ($colorVariants as $variant) {
                                $imgKey = ($variant['image'] ?? '') !== '' ? $variant['image'] : ('__solo__' . ($variant['hex'] ?? ''));
                                if (!isset($colorGroups[$imgKey])) {
                                    $colorGroups[$imgKey] = ['colors' => [], 'image' => $variant['image'] ?? ''];
                                }
                                $colorGroups[$imgKey]['colors'][] = $variant;
                            }
                            $colorGroups = array_values($colorGroups);

                            $swatchCount = max(1, count($colorGroups));
                            $swatchWrapClass = $swatchCount > 7 ? 'flex-wrap' : 'flex-nowrap';
                            $swatchSizeClass = $swatchCount >= 13
                                ? 'h-3.5 w-3.5 sm:h-4 sm:w-4'
                                : ($swatchCount >= 9
                                    ? 'h-4 w-4 sm:h-[1.05rem] sm:w-[1.05rem]'
                                    : 'h-5 w-5');
                            $swatchGapClass = $swatchCount >= 13
                                ? 'gap-1'
                                : ($swatchCount >= 9 ? 'gap-1.5' : 'gap-3');
                            
                            $colorVariantsJson = json_encode($colorVariants);
                        ?>

                        <div class="group relative mx-auto w-full max-w-[290px]">

                            <div class="relative w-full">
                                <a href="<?php echo e(route('gafas.show', ['producto' => $producto->slug])); ?>" class="relative block aspect-[16/9] w-full" data-gafa-link aria-label="Ver <?php echo e($producto->nombre); ?>">
                                    <?php if($img): ?>
                                        <img
                                            src="<?php echo e($img); ?>"
                                            alt="<?php echo e(e($producto->nombre)); ?>"
                                            class="absolute inset-0 h-full w-full object-contain gafa-color-image transition-opacity duration-200"
                                            data-gafa-main-image
                                            data-default-src="<?php echo e($img); ?>"
                                            data-color-variants="<?php echo e(e($colorVariantsJson)); ?>"
                                            loading="lazy"
                                            decoding="async"
                                            fetchpriority="low"
                                            style="object-position: <?php echo e((int) round($posXEff)); ?>% <?php echo e((int) round($posYEff)); ?>%; transform-origin: <?php echo e((int) round($posXEff)); ?>% <?php echo e((int) round($posYEff)); ?>%; transform: scale(<?php echo e(number_format($zoom, 2, '.', '')); ?>);"
                                        />
                                    <?php else: ?>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <p class="text-xs font-semibold text-zinc-500">Sin imagen</p>
                                        </div>
                                    <?php endif; ?>
                                </a>

                                <div class="absolute right-1 top-1 z-10">
                                    <form method="POST" action="<?php echo e(route('favoritos.toggle', ['producto' => $producto->slug])); ?>" data-favorite-form>
                                        <?php echo csrf_field(); ?>
                                        <?php
                                            $heartClasses = $isFavorited ? 'text-rose-500' : 'text-zinc-400';
                                        ?>
                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-sm font-semibold shadow-sm hover:text-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/70" aria-label="<?php echo e($isFavorited ? 'Quitar de favoritos' : 'Agregar a favoritos'); ?>" aria-pressed="<?php echo e($isFavorited ? 'true' : 'false'); ?>" data-favorite-button>
                                            <svg viewBox="0 0 24 24" class="h-6 w-6 <?php echo e($heartClasses); ?>" fill="<?php echo e($isFavorited ? 'currentColor' : 'none'); ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 21s-7-4.4-9.4-8.9C.4 8.2 2.7 5 6.3 5c2 0 3.4 1 4.7 2.4C12.3 6 13.7 5 15.7 5c3.6 0 5.9 3.2 3.7 7.1C19 16.6 12 21 12 21Z"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <a href="<?php echo e(route('gafas.show', ['producto' => $producto->slug])); ?>" class="block" data-gafa-link>
                                <div class="pt-2 text-center">
                                    <p class="truncate text-[1.15rem] font-semibold leading-tight text-zinc-900 sm:text-[1.2rem]"><?php echo e($producto->nombre); ?></p>

                                    <div class="mt-1">
                                        <?php if($precioOferta): ?>
                                            <div class="flex items-end justify-center gap-2">
                                                <p class="text-[1.1rem] font-medium text-zinc-900 sm:text-[1.15rem]">$ <?php echo e(number_format((float)$precioOferta, 0, ',', '.')); ?></p>
                                                <?php if($precio !== null): ?>
                                                    <p class="text-xs font-medium text-zinc-500 line-through"><?php echo e(number_format((float)$precio, 0, ',', '.')); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-[1.1rem] font-medium text-zinc-900 sm:text-[1.15rem]">$ <?php echo e($precio !== null ? number_format((float)$precio, 0, ',', '.') : '—'); ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mt-2 flex <?php echo e($swatchWrapClass); ?> items-center justify-center <?php echo e($swatchGapClass); ?> overflow-visible py-0.5">
                                        <?php $__currentLoopData = $colorGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $groupColors = $group['colors'];
                                                $groupImage  = $group['image'];
                                                $groupName   = implode(', ', array_column($groupColors, 'name'));
                                                $firstHex    = $groupColors[0]['hex'];
                                                $isMulti     = count($groupColors) > 1;
                                                if ($isMulti) {
                                                    $hexes = array_column($groupColors, 'hex');
                                                    $n = count($hexes);
                                                    $sz = 20;
                                                    $paths = '';
                                                    $angleStep = 360 / $n;
                                                    for ($ci = 0; $ci < $n; $ci++) {
                                                        $sa = $angleStep * $ci - 90;
                                                        $ea = $angleStep * ($ci + 1) - 90;
                                                        $x1 = round($sz/2 + ($sz/2) * cos(M_PI * $sa / 180), 3);
                                                        $y1 = round($sz/2 + ($sz/2) * sin(M_PI * $sa / 180), 3);
                                                        $x2 = round($sz/2 + ($sz/2) * cos(M_PI * $ea / 180), 3);
                                                        $y2 = round($sz/2 + ($sz/2) * sin(M_PI * $ea / 180), 3);
                                                        $la = $angleStep > 180 ? 1 : 0;
                                                        $paths .= '<path d="M'.($sz/2).','.($sz/2).' L'.$x1.','.$y1.' A'.($sz/2).','.($sz/2).' 0 '.$la.' 1 '.$x2.','.$y2.' Z" fill="'.e($hexes[$ci]).'" />';
                                                    }
                                                }
                                            ?>
                                            <button
                                                type="button"
                                                class="gafa-color-swatch inline-block shrink-0 <?php echo e($swatchSizeClass); ?> rounded-full border-2 border-white shadow-sm transition overflow-hidden p-0 data-[active=true]:scale-125 data-[active=true]:shadow-md"
                                                style="<?php echo e(!$isMulti ? 'background-color: ' . $firstHex . ';' : ''); ?>"
                                                data-color-hex="<?php echo e($firstHex); ?>"
                                                data-color-image="<?php echo e($groupImage); ?>"
                                                data-color-name="<?php echo e($groupName); ?>"
                                                data-active="<?php echo e($i === 0 ? 'true' : 'false'); ?>"
                                                aria-label="Color <?php echo e($groupName); ?>"
                                                aria-pressed="<?php echo e($i === 0 ? 'true' : 'false'); ?>"
                                            ><?php if($isMulti): ?><svg width="100%" height="100%" viewBox="0 0 <?php echo e($sz); ?> <?php echo e($sz); ?>" style="display:block;"><?php echo $paths; ?></svg><?php endif; ?></button>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="mt-10 flex items-center justify-center gap-4">
                    <img
                        src="<?php echo e(asset('images/pasarpagina.png')); ?>"
                        alt=""
                        class="hidden h-auto w-[82px] sm:block md:w-[98px]"
                        aria-hidden="true"
                        loading="lazy"
                        decoding="async"
                    >

                    <div class="flex items-center justify-center gap-2">
                        <a href="<?php echo e($productos->previousPageUrl() ?: '#'); ?>"
                           class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[#cfe6e4] bg-white text-[#2b9aa0] hover:bg-[#f2fbfa] <?php echo e($productos->onFirstPage() ? 'pointer-events-none opacity-50' : ''); ?>"
                           aria-label="Anterior">
                            ←
                        </a>
                        <div class="rounded-2xl border border-[#cfe6e4] bg-white px-4 py-2 text-sm font-semibold text-[#2b9aa0]">
                            Página <?php echo e($productos->currentPage()); ?> de <?php echo e($productos->lastPage()); ?>

                        </div>
                        <a href="<?php echo e($productos->nextPageUrl() ?: '#'); ?>"
                           class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[#cfe6e4] bg-white text-[#2b9aa0] hover:bg-[#f2fbfa] <?php echo e($productos->hasMorePages() ? '' : 'pointer-events-none opacity-50'); ?>"
                           aria-label="Siguiente">
                            →
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>


<section class="py-10 sm:py-14 bg-zinc-50">
    <div class="mx-auto max-w-7xl px-4">
        <div class="text-center mb-6">
            <span class="text-xs font-semibold uppercase tracking-widest text-zinc-500 block mb-2">NUEVA COLECCIÓN</span>
            <h2 class="text-[2.4rem] font-extrabold leading-[1.05] tracking-tight text-black sm:text-[3rem]">
                Estilo que <span style="color:#c9a96e;">te define</span>
            </h2>
            <p class="mt-3 text-zinc-500 text-base max-w-xl mx-auto">Descubre monturas diseñadas para cada ocasión y estilo de vida.</p>
        </div>
    </div>
</section>


<section class="py-10 sm:py-14 bg-white">
    <div class="mx-auto max-w-7xl px-4">
        <div class="flex items-end justify-between mb-8">
            <div>
                <span class="text-xs font-semibold uppercase tracking-widest text-zinc-500 block mb-2" style="text-align:left;">PARA TI</span>
                <h2 class="text-left text-[2.4rem] font-extrabold leading-[1.05] tracking-tight text-black sm:text-[3rem]">
                    También te puede<br>
                    <span style="color:#c9a96e;">gustar</span>
                </h2>
            </div>
            <a href="<?php echo e(url('/gafas')); ?>" class="hidden md:inline-flex items-center gap-2 rounded-full border-2 border-zinc-800 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-800 hover:bg-zinc-950 hover:text-white transition">Ver colección completa</a>
        </div>

        <?php
            try {
                $sugerenciasProductos = \App\Models\Producto::query()
                    ->whereIn('tipo', ['gafas', 'gafas_polarizadas'])
                    ->where('esta_activo', true)
                    ->inRandomOrder()
                    ->limit(24)
                    ->get()
                    ->filter(function ($p) {
                        $m = is_array($p->meta) ? $p->meta : [];
                        return !empty($m['imagen_url']);
                    })
                    ->take(8)
                    ->values();
            } catch (\Throwable $e) {
                $sugerenciasProductos = collect();
            }
        ?>

        <?php if($sugerenciasProductos->isNotEmpty()): ?>
            <div class="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">
                <?php $__currentLoopData = $sugerenciasProductos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sug): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $sugMeta = is_array($sug->meta) ? $sug->meta : [];
                        $sugImg  = (string) ($sugMeta['imagen_url'] ?? '');
                        $sugPrice = $sug->precio_oferta ?? $sug->precio;
                    ?>
                    <a href="<?php echo e(route('gafas.show', ['producto' => $sug->slug])); ?>" class="block group" title="Ver <?php echo e($sug->nombre); ?>">
                        <?php if($sugImg !== ''): ?>
                            <div class="mx-auto aspect-[5/2.6] w-full overflow-hidden rounded-[1rem]">
                                <img src="<?php echo e($sugImg); ?>" alt="<?php echo e(e($sug->nombre)); ?>" class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-105" loading="lazy" draggable="false">
                            </div>
                        <?php else: ?>
                            <div class="mx-auto aspect-[5/2.6] w-full rounded-[1rem] bg-zinc-100 flex items-center justify-center">
                                <span class="text-xs text-zinc-400">Sin imagen</span>
                            </div>
                        <?php endif; ?>
                        <h3 class="mt-2 line-clamp-1 text-[0.95rem] font-extrabold text-black sm:mt-3 sm:text-[1.05rem]"><?php echo e($sug->nombre); ?></h3>
                        <p class="text-[0.95rem] text-zinc-700 sm:text-[1.05rem]"><?php echo e($sugPrice !== null ? '$ ' . number_format((float) $sugPrice, 0, ',', '.') : '—'); ?></p>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">
                <?php $__currentLoopData = [['Montura Clásica Premium','$ 289.000'],['Urban Square Edition','$ 195.000'],['Sport Half Frame Pro','$ 320.000'],['Minimal Round Bold','$ 175.000']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$phName, $phPrice]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(url('/gafas')); ?>" class="block">
                        <div class="mx-auto aspect-[5/2.6] w-full rounded-[1rem] bg-zinc-100 flex items-center justify-center">
                            <span class="text-xs text-zinc-400">Espacio imagen</span>
                        </div>
                        <h3 class="mt-2 text-[0.95rem] font-extrabold text-black sm:mt-3 sm:text-[1.05rem]"><?php echo e($phName); ?></h3>
                        <p class="text-[0.95rem] text-zinc-700 sm:text-[1.05rem]"><?php echo e($phPrice); ?></p>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="<?php echo e(url('/gafas')); ?>" class="inline-flex items-center gap-2 rounded-full border-2 border-zinc-800 bg-white px-6 py-3 text-sm font-semibold text-zinc-800 hover:bg-zinc-950 hover:text-white transition md:hidden">Ver colección completa</a>
        </div>
    </div>
</section>

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
        // Manejar clicks en swatches de color
        document.addEventListener('click', (e) => {
            const swatch = e.target.closest('.gafa-color-swatch');
            if (!swatch) return;

            // Evita que el click en el swatch dispare el enlace de la tarjeta.
            e.preventDefault();
            e.stopPropagation();

            const container = swatch.closest('.group');
            if (!container) return;

            const mainImg = container.querySelector('img[data-gafa-main-image]');
            if (!mainImg) return;

            const colorImage = swatch.getAttribute('data-color-image');
            const defaultSrc = mainImg.getAttribute('data-default-src');
            const newSrc = colorImage && colorImage.trim() !== '' ? colorImage : defaultSrc;

            // Cambiar imagen
            if (mainImg.src !== newSrc) {
                mainImg.src = newSrc;
            }

            // Actualizar estado activo
            const allSwatches = container.querySelectorAll('.gafa-color-swatch');
            allSwatches.forEach((s) => {
                const isActive = s === swatch;
                s.setAttribute('data-active', isActive ? 'true' : 'false');
                s.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        });

        // Prefetch setup (código original)
        const links = Array.from(document.querySelectorAll('a[data-gafa-link]'));
        if (!links.length) return;

        const prefetched = new Set();

        const prefetch = (href) => {
            if (!href || prefetched.has(href)) return;
            prefetched.add(href);

            const l = document.createElement('link');
            l.rel = 'prefetch';
            l.as = 'document';
            l.href = href;
            document.head.appendChild(l);
        };

        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        const isConstrainedNetwork = Boolean(connection && (connection.saveData || /2g|3g/.test(String(connection.effectiveType || ''))));
        const isLowPowerDevice = navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4;
        const shouldWarmEarly = !isConstrainedNetwork && !isLowPowerDevice;

        // Calienta solo un par de detalles si el dispositivo/red lo permite.
        if (shouldWarmEarly) {
            links.slice(0, 2).forEach((a) => prefetch(a.href));
        }

        links.forEach((a) => {
            const warm = () => prefetch(a.href);
            a.addEventListener('mouseenter', warm, { passive: true });
            a.addEventListener('focus', warm, { passive: true });
            a.addEventListener('touchstart', warm, { passive: true });
        });

        if (!('IntersectionObserver' in window)) return;

        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const a = entry.target;
                if (a instanceof HTMLAnchorElement) {
                    prefetch(a.href);
                }
                io.unobserve(entry.target);
            });
        }, { rootMargin: '220px 0px' });

        links.forEach((a) => io.observe(a));
    })();
</script>
</body>
</html>
<?php /**PATH C:\Users\norla\Desktop\Proyectos con Ronaldo\copia_optica\resources\views/gafas/index.blade.php ENDPATH**/ ?>