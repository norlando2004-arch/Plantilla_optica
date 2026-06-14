<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gafas mujer — Óptica</title>

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
</head>
<body class="overflow-x-hidden bg-white text-zinc-900 antialiased font-sans">
@php
    $routeName = request()->route()?->getName();
    $minPrice = request()->query('min_price');
    $maxPrice = request()->query('max_price');
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
@endphp

@include('partials.store-navbar', ['storeBannerImage' => $storeBannerImage ?? null])

<main class="mx-auto max-w-7xl px-4 py-8">
    <div class="mb-6 text-center">
        <p class="text-base font-semibold text-zinc-500">Óptica</p>
        <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-zinc-900 sm:text-4xl">Gafas mujer</h1>
        <p class="mt-2 text-base text-zinc-500">Colección para mujer</p>
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
            if (!toggle || !panel) return;

            const open = () => {
                panel.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
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

            sync();
        });
    </script>

    <div class="grid gap-6">
        <aside id="mobile-filters-panel" class="hidden fixed inset-0 z-50 bg-black/35 p-3 sm:p-4">
            <div class="relative ml-0 w-full max-w-sm max-h-[calc(100dvh-1.5rem)] overflow-y-auto rounded-3xl border border-zinc-200 bg-white p-4 sm:p-5">
                <div class="mb-2 flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-zinc-900">Filtrar</p>
                    <button type="button" data-filter-close class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-zinc-300 bg-white text-zinc-700 hover:bg-zinc-50" aria-label="Cerrar filtros">✕</button>
                </div>

                <form method="GET" action="{{ url()->current() }}" class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Precio</p>
                    <div class="mt-3 grid gap-3">
                        <div data-price-filter-form>
                            <div class="grid gap-3">
                                <div>
                                    <div class="flex items-center justify-between gap-3">
                                        <label for="priceMin" class="text-xs font-semibold text-zinc-700">Desde</label>
                                        <span id="priceMinValue" class="text-xs font-semibold text-zinc-900">{{ number_format($sliderMinValue, 0, ',', '.') }}</span>
                                    </div>
                                    <input
                                        id="priceMin"
                                        type="range"
                                        name="min_price"
                                        min="0"
                                        max="{{ $sliderMax }}"
                                        step="{{ $sliderStep }}"
                                        value="{{ $sliderMinValue }}"
                                        class="mt-3 w-full accent-zinc-900"
                                        aria-label="Filtrar por precio mínimo"
                                    />
                                </div>
                                <div>
                                    <div class="flex items-center justify-between gap-3">
                                        <label for="priceMax" class="text-xs font-semibold text-zinc-700">Hasta</label>
                                        <span id="priceMaxValue" class="text-xs font-semibold text-zinc-900">{{ number_format($sliderMaxValue, 0, ',', '.') }}</span>
                                    </div>
                                    <input
                                        id="priceMax"
                                        type="range"
                                        name="max_price"
                                        min="0"
                                        max="{{ $sliderMax }}"
                                        step="{{ $sliderStep }}"
                                        value="{{ $sliderMaxValue }}"
                                        class="mt-3 w-full accent-zinc-900"
                                        aria-label="Filtrar por precio máximo"
                                    />
                                </div>

                                <p class="text-xs text-zinc-500">Mueve las barras para filtrar.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="submit" data-loading class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white hover:bg-zinc-900">
                                Aplicar
                            </button>
                            <a href="{{ url()->current() }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 hover:bg-zinc-50">
                                Limpiar
                            </a>
                        </div>
                    </div>
                </form>

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

                        let timer = null;
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
                            if (timer) clearTimeout(timer);
                            timer = setTimeout(() => form.submit(), 250);
                        };

                        minRange.addEventListener('input', onInput);
                        maxRange.addEventListener('input', onInput);

                        form.addEventListener('submit', (e) => {
                            const btn = e.submitter;
                            if (!btn || !btn.matches('[data-loading]')) return;
                            btn.disabled = true;
                            btn.textContent = 'Cargando...';
                        });
                    })();
                </script>

                <form method="GET" action="{{ route('gafas.index') }}" class="mt-6 border-t border-zinc-200 pt-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Categorías</p>
                    @php
                        $filterCats = [
                            ['label' => 'Niños', 'value' => 'ninos'],
                            ['label' => 'Hombre', 'value' => 'hombre'],
                            ['label' => 'Mujeres', 'value' => 'mujeres'],
                            ['label' => 'Deportivas', 'value' => 'deportivas'],
                            ['label' => 'Polarizadas', 'value' => 'polarizadas'],
                        ];
                    @endphp
                    <div class="mt-3 grid gap-2">
                        @foreach($filterCats as $cat)
                            @php
                                $isActive = $cat['value'] === 'mujeres';
                            @endphp
                            <label class="block cursor-pointer">
                                <input type="checkbox" name="categories[]" value="{{ $cat['value'] }}" class="peer sr-only" {{ $isActive ? 'checked' : '' }}>
                                <span class="flex items-center gap-3 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50 peer-checked:border-zinc-950 peer-checked:bg-zinc-950 peer-checked:text-white">
                                    <span class="js-filter-check inline-flex h-4 w-4 items-center justify-center rounded border border-zinc-300 bg-white text-transparent text-[11px] font-bold leading-none">✔</span>
                                    <span>{{ $cat['label'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-2">
                        <button type="submit" data-loading class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white hover:bg-zinc-900">Aplicar filtros</button>
                        <a href="{{ route('gafas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 hover:bg-zinc-50">Limpiar</a>
                    </div>
                </form>
            </div>
        </aside>

        <section class="relative">
            <img
                src="{{ asset('images/derchaimagen.png') }}"
                alt=""
                class="pointer-events-none absolute right-[calc((100vw-100%)/-2)] -top-28 z-20 w-[52px] sm:-top-32 sm:w-[62px] md:-top-36 md:w-[74px]"
                aria-hidden="true"
                decoding="async"
            >

            <img
                src="{{ asset('images/izquierdaimagen.png') }}"
                alt=""
                class="pointer-events-none absolute left-[calc((100vw-100%)/-2)] top-[58%] z-20 w-[52px] sm:w-[62px] md:w-[74px]"
                aria-hidden="true"
                decoding="async"
            >

            <img
                src="{{ asset('images/derechaimagen2.png') }}"
                alt=""
                class="pointer-events-none absolute right-[calc((100vw-100%)/-2)] top-[58%] z-20 w-[46px] sm:w-[56px] md:w-[66px]"
                aria-hidden="true"
                decoding="async"
            >

            @if($productos->count() === 0)
                <div class="rounded-3xl border border-zinc-200 bg-white p-8 text-center">
                    <p class="text-base font-semibold">No encontramos productos</p>
                    <p class="mt-2 text-sm text-zinc-500">Prueba ajustando el rango de precios.</p>
                </div>
            @else
                <div class="mb-4 flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-zinc-700">
                        Mostrando <span class="text-zinc-900">{{ $productos->count() }}</span> de <span class="text-zinc-900">{{ $productos->total() }}</span>
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-x-3 gap-y-8 sm:gap-x-5 sm:grid-cols-3">
                    @foreach($productos as $producto)
                        @php
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
                        @endphp

                        @php
                            $swatches = [];
                            $colorVariants = [];
                            $rawSwatches = $meta['colores'] ?? ($meta['color_variants'] ?? null);
                            
                            // Primero agregar el color principal (si existe)
                            $primaryColor = trim((string) ($meta['color'] ?? ($producto->color ?? '')));
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
                            
                            if ($primaryColor !== '') {
                                $primaryHex = $namedColorHex[strtolower($primaryColor)] ?? '#cec9bc';
                                $primaryImage = trim((string) ($img ?? ''));
                                if (preg_match('/^#?[0-9A-Fa-f]{6}$/', str_replace('#', '', $primaryHex)) === 1) {
                                    $hex = str_starts_with($primaryHex, '#') ? $primaryHex : "#{$primaryHex}";
                                    $swatches[] = $hex;
                                    $colorVariants[] = [
                                        'name' => $primaryColor,
                                        'hex' => $hex,
                                        'image' => $primaryImage,
                                    ];
                                }
                            }

                            // Luego procesar color_variants si existen (estructura nuevo)
                            if (is_array($rawSwatches) && count($rawSwatches) > 0 && is_array($rawSwatches[0] ?? null)) {
                                foreach ($rawSwatches as $variant) {
                                    if (is_array($variant) && isset($variant['hex'])) {
                                        $hexVal = trim((string) $variant['hex']);
                                        $nameVal = trim((string) ($variant['name'] ?? 'Color'));
                                        $imageVal = trim((string) ($variant['image'] ?? ''));
                                        if ($primaryColor !== '' && $imageVal === '') {
                                            continue;
                                        }
                                        
                                        if (preg_match('/^#?[0-9A-Fa-f]{6}$/', $hexVal) === 1) {
                                            $hex = str_starts_with($hexVal, '#') ? $hexVal : "#{$hexVal}";
                                            $swatches[] = $hex;
                                            $colorVariants[] = [
                                                'name' => $nameVal,
                                                'hex' => $hex,
                                                'image' => $imageVal,
                                            ];
                                        }
                                    }
                                }
                            } elseif ($primaryColor === '' && is_string($rawSwatches) && trim($rawSwatches) !== '') {
                                // Fallback: string legacy format
                                $parts = preg_split('/[,\n|]+/', $rawSwatches) ?: [];
                                foreach ($parts as $part) {
                                    $c = trim((string) $part);
                                    if (preg_match('/^#?[0-9A-Fa-f]{6}$/', $c) === 1) {
                                        $hex = str_starts_with($c, '#') ? $c : "#{$c}";
                                        $swatches[] = $hex;
                                        $colorVariants[] = [
                                            'name' => 'Color',
                                            'hex' => $hex,
                                            'image' => '',
                                        ];
                                    }
                                }
                            }
                            
                            $swatches = array_values(array_unique($swatches));
                            $colorVariants = array_values(array_filter($colorVariants, static fn ($v) => in_array($v['hex'], $swatches)));
                            $seenVariantKeys = [];
                            $colorVariants = array_values(array_filter($colorVariants, static function ($variant) use (&$seenVariantKeys) {
                                $name = strtolower(trim((string) ($variant['name'] ?? '')));
                                $hex = strtolower(trim((string) ($variant['hex'] ?? '')));
                                $key = $name !== '' ? $name : $hex;
                                $key = $key . '|' . $hex;

                                if ($hex === '' || isset($seenVariantKeys[$key])) {
                                    return false;
                                }

                                $seenVariantKeys[$key] = true;
                                return true;
                            }));

                            if (count($swatches) === 0) {
                                $fallbackName = $primaryColor !== '' ? $primaryColor : 'Gris';
                                $fallbackHex = $namedColorHex[strtolower($fallbackName)] ?? '#cec9bc';
                                $swatches = [$fallbackHex];
                                $colorVariants = [
                                    ['name' => $fallbackName, 'hex' => $fallbackHex, 'image' => $img],
                                ];
                            }

                            $swatchCount = max(1, count($colorVariants));
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
                        @endphp

                        <div class="group relative mx-auto w-full max-w-[290px]">

                            <div class="relative w-full">
                                <a href="{{ route('gafas.show', ['producto' => $producto->slug]) }}" class="relative block aspect-[16/9] w-full" data-gafa-link aria-label="Ver {{ $producto->nombre }}">
                                    @if($img)
                                        <img
                                            src="{{ $img }}"
                                            alt="{{ e($producto->nombre) }}"
                                            class="absolute inset-0 h-full w-full object-contain gafa-color-image transition-opacity duration-200"
                                            data-gafa-main-image
                                            data-default-src="{{ $img }}"
                                            data-color-variants="{{ e($colorVariantsJson) }}"
                                            style="object-position: {{ (int) round($posXEff) }}% {{ (int) round($posYEff) }}%; transform-origin: {{ (int) round($posXEff) }}% {{ (int) round($posYEff) }}%; transform: scale({{ number_format($zoom, 2, '.', '') }});"
                                        />
                                    @else
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <p class="text-xs font-semibold text-zinc-500">Sin imagen</p>
                                        </div>
                                    @endif
                                </a>

                                <div class="absolute right-1 top-1 z-10">
                                    <form method="POST" action="{{ route('favoritos.toggle', ['producto' => $producto->slug]) }}" data-favorite-form>
                                        @csrf
                                        @php
                                            $heartClasses = $isFavorited ? 'text-rose-500' : 'text-zinc-400';
                                        @endphp
                                        <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-sm font-semibold shadow-sm hover:text-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/70" aria-label="{{ $isFavorited ? 'Quitar de favoritos' : 'Agregar a favoritos' }}" aria-pressed="{{ $isFavorited ? 'true' : 'false' }}" data-favorite-button>
                                            <svg viewBox="0 0 24 24" class="h-6 w-6 {{ $heartClasses }}" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 21s-7-4.4-9.4-8.9C.4 8.2 2.7 5 6.3 5c2 0 3.4 1 4.7 2.4C12.3 6 13.7 5 15.7 5c3.6 0 5.9 3.2 3.7 7.1C19 16.6 12 21 12 21Z"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <a href="{{ route('gafas.show', ['producto' => $producto->slug]) }}" class="block" data-gafa-link>
                                <div class="pt-2 text-center">
                                    <p class="truncate text-[1.15rem] font-semibold leading-tight text-zinc-900 sm:text-[1.2rem]">{{ $producto->nombre }}</p>

                                    <div class="mt-1">
                                        @if($precioOferta)
                                            <div class="flex items-end justify-center gap-2">
                                                <p class="text-[1.1rem] font-medium text-zinc-900 sm:text-[1.15rem]">$ {{ number_format((float)$precioOferta, 0, ',', '.') }}</p>
                                                @if($precio !== null)
                                                    <p class="text-xs font-medium text-zinc-500 line-through">{{ number_format((float)$precio, 0, ',', '.') }}</p>
                                                @endif
                                            </div>
                                        @else
                                            <p class="text-[1.1rem] font-medium text-zinc-900 sm:text-[1.15rem]">$ {{ $precio !== null ? number_format((float)$precio, 0, ',', '.') : '—' }}</p>
                                        @endif
                                    </div>

                                    <div class="mt-2 flex {{ $swatchWrapClass }} items-center justify-center {{ $swatchGapClass }} overflow-hidden">
                                        @foreach($colorVariants as $i => $variant)
                                            <button
                                                type="button"
                                                class="gafa-color-swatch inline-block shrink-0 {{ $swatchSizeClass }} rounded-full border-2 border-white shadow-sm transition data-[active=true]:scale-125 data-[active=true]:shadow-md"
                                                style="background-color: {{ $variant['hex'] }};"
                                                data-color-hex="{{ $variant['hex'] }}"
                                                data-color-image="{{ $variant['image'] }}"
                                                data-color-name="{{ $variant['name'] }}"
                                                data-active="{{ $i === 0 ? 'true' : 'false' }}"
                                                aria-label="Color {{ $variant['name'] }}"
                                                aria-pressed="{{ $i === 0 ? 'true' : 'false' }}"
                                            ></button>
                                        @endforeach
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 flex items-center justify-center gap-4">
                    <img
                        src="{{ asset('images/pasarpagina.png') }}"
                        alt=""
                        class="hidden h-auto w-[82px] sm:block md:w-[98px]"
                        aria-hidden="true"
                        decoding="async"
                    >

                    <div class="flex items-center justify-center gap-2">
                        <a href="{{ $productos->previousPageUrl() ?: '#' }}"
                           class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[#cfe6e4] bg-white text-[#2b9aa0] hover:bg-[#f2fbfa] {{ $productos->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}"
                           aria-label="Anterior">
                            ←
                        </a>
                        <div class="rounded-2xl border border-[#cfe6e4] bg-white px-4 py-2 text-sm font-semibold text-[#2b9aa0]">
                            Página {{ $productos->currentPage() }} de {{ $productos->lastPage() }}
                        </div>
                        <a href="{{ $productos->nextPageUrl() ?: '#' }}"
                           class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[#cfe6e4] bg-white text-[#2b9aa0] hover:bg-[#f2fbfa] {{ $productos->hasMorePages() ? '' : 'pointer-events-none opacity-50' }}"
                           aria-label="Siguiente">
                            →
                        </a>
                    </div>
                </div>
            @endif
        </section>
    </div>
</main>

{{-- Banner destacado de colección --}}
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

{{-- Más sugerencias de gafas --}}
<section class="py-10 sm:py-14 bg-white">
    <div class="mx-auto max-w-7xl px-4">
        <div class="flex items-end justify-between mb-8">
            <div>
                <span class="text-xs font-semibold uppercase tracking-widest text-zinc-500 block mb-2">PARA TI</span>
                <h2 class="text-left text-[2.4rem] font-extrabold leading-[1.05] tracking-tight text-black sm:text-[3rem]">
                    También te puede<br>
                    <span style="color:#c9a96e;">gustar</span>
                </h2>
            </div>
            <a href="{{ url('/gafas') }}" class="hidden md:inline-flex items-center gap-2 rounded-full border-2 border-zinc-800 bg-white px-5 py-2.5 text-sm font-semibold text-zinc-800 hover:bg-zinc-950 hover:text-white transition">Ver colección completa</a>
        </div>
        @php
            try {
                $sugerenciasProductos = \App\Models\Producto::query()
                    ->whereIn('tipo', ['gafas', 'gafas_polarizadas'])
                    ->where('esta_activo', true)
                    ->inRandomOrder()->limit(24)->get()
                    ->filter(fn($p) => !empty((is_array($p->meta) ? $p->meta : [])['imagen_url']))
                    ->take(8)->values();
            } catch (\Throwable $e) { $sugerenciasProductos = collect(); }
        @endphp
        @if($sugerenciasProductos->isNotEmpty())
            <div class="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-4">
                @foreach($sugerenciasProductos as $sug)
                    @php $sugMeta = is_array($sug->meta) ? $sug->meta : []; $sugImg = (string)($sugMeta['imagen_url'] ?? ''); $sugPrice = $sug->precio_oferta ?? $sug->precio; @endphp
                    <a href="{{ route('gafas.show', ['producto' => $sug->slug]) }}" class="block group">
                        @if($sugImg !== '')
                            <div class="mx-auto aspect-[5/2.6] w-full overflow-hidden rounded-[1rem]">
                                <img src="{{ $sugImg }}" alt="{{ e($sug->nombre) }}" class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-105" loading="lazy">
                            </div>
                        @else
                            <div class="mx-auto aspect-[5/2.6] w-full rounded-[1rem] bg-zinc-100 flex items-center justify-center"><span class="text-xs text-zinc-400">Sin imagen</span></div>
                        @endif
                        <h3 class="mt-2 line-clamp-1 text-[0.95rem] font-extrabold text-black sm:mt-3 sm:text-[1.05rem]">{{ $sug->nombre }}</h3>
                        <p class="text-[0.95rem] text-zinc-700 sm:text-[1.05rem]">{{ $sugPrice !== null ? '$ '.number_format((float)$sugPrice,0,',','.') : '—' }}</p>
                    </a>
                @endforeach
            </div>
        @endif
        <div class="mt-8 text-center">
            <a href="{{ url('/gafas') }}" class="inline-flex items-center gap-2 rounded-full border-2 border-zinc-800 bg-white px-6 py-3 text-sm font-semibold text-zinc-800 hover:bg-zinc-950 hover:text-white transition md:hidden">Ver colección completa</a>
        </div>
    </div>
</section>

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

        // Calienta los primeros detalles visibles para mejorar el primer clic.
        links.slice(0, 8).forEach((a) => prefetch(a.href));

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
