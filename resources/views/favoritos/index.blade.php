<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $browserTitle = $browserTitle ?? 'Favoritos — Óptica';
        $pageTitle = $pageTitle ?? 'Tus Favoritos';
        $pageSubtitle = $pageSubtitle ?? 'Encuentra tus gafas favoritas en un solo lugar';
    @endphp
    <title>{{ $browserTitle }}</title>

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

        .favorites-hero {
            text-align: center;
        }

        .favorites-kicker {
            color: #7b93a8;
            letter-spacing: 0.08em;
        }

        .favorites-shell {
            background: transparent;
            box-shadow: none;
            border: 0;
        }

        .favorite-summary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background: transparent;
            color: #4b9196;
            box-shadow: none;
        }

        .favorite-card {
            background: transparent;
            box-shadow: none;
            transition: transform 180ms ease;
        }

        .favorite-card:hover {
            transform: translateY(-2px);
            box-shadow: none;
        }

        .favorite-image-stage {
            background: transparent;
        }

        .favorite-name {
            color: #35507a;
        }

        .favorite-price {
            color: #5d9fb0;
        }

        .favorite-old-price {
            color: #95a5b4;
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

@include('partials.store-navbar', [
    'showStoreBanner' => false,
])

<main class="mx-auto max-w-7xl px-4 py-8 sm:py-10">
    <div class="favorites-hero mb-8">
        <p class="favorites-kicker text-sm font-semibold">Óptica</p>
        <h1 class="mt-2 text-3xl font-extrabold text-[#35507a] sm:text-5xl">{{ $pageTitle }}</h1>
        <p class="mx-auto mt-3 max-w-2xl text-sm text-[#5b6f86] sm:text-xl">{{ $pageSubtitle }}</p>
    </div>

    <section class="favorites-shell relative px-2 py-4 sm:px-2 sm:py-6">
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
            <div class="mx-auto max-w-2xl p-8 text-center">
                <p class="text-xl font-bold text-[#35507a]">Aún no tienes favoritos</p>
                <p class="mt-2 text-sm text-[#6c7f92]">Explora las gafas y toca el corazón para agregarlas aquí.</p>
            </div>
        @else
            <div class="mb-6 flex items-center justify-center">
                <p class="favorite-summary px-4 py-2 text-sm font-semibold">
                    Mostrando <span class="mx-1 text-[#35507a]">{{ $productos->count() }}</span> de <span class="mx-1 text-[#35507a]">{{ $productos->total() }}</span> favoritos
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
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
                        $colorVariants = [];
                        $rawSwatches = $meta['colores'] ?? ($meta['color_variants'] ?? null);
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

                        $pushColorVariant = static function (string $name, array $hexes, string $image = '') use (&$colorVariants): void {
                            $name = trim($name);
                            $image = trim($image);
                            $cleanHexes = [];

                            foreach ($hexes as $hex) {
                                $hex = trim((string) $hex);
                                if (preg_match('/^#?[0-9A-Fa-f]{6}$/', $hex) !== 1) {
                                    continue;
                                }

                                $resolvedHex = str_starts_with($hex, '#') ? $hex : "#{$hex}";
                                if (!in_array($resolvedHex, $cleanHexes, true)) {
                                    $cleanHexes[] = $resolvedHex;
                                }
                            }

                            if ($name === '' || $cleanHexes === []) {
                                return;
                            }

                            $colorVariants[] = [
                                'name' => $name,
                                'hex' => $cleanHexes[0],
                                'hexes' => $cleanHexes,
                                'image' => $image,
                            ];
                        };

                        $hasStructuredVariants = is_array($rawSwatches)
                            && collect($rawSwatches)->contains(fn ($variant) => is_array($variant));

                        if (! $hasStructuredVariants && $primaryColor !== '') {
                            $primaryHex = $namedColorHex[strtolower($primaryColor)] ?? '#cec9bc';
                            $primaryImage = trim((string) ($img ?? ''));
                            $pushColorVariant($primaryColor, [$primaryHex], $primaryImage);
                        }

                        if (is_array($rawSwatches) && $hasStructuredVariants) {
                            foreach ($rawSwatches as $variant) {
                                if (!is_array($variant)) {
                                    continue;
                                }

                                $hexVal = trim((string) ($variant['hex'] ?? ''));
                                $nameVal = trim((string) ($variant['name'] ?? ''));
                                $imageVal = trim((string) ($variant['image'] ?? ''));

                                if ($nameVal === '' && $hexVal === '') {
                                    continue;
                                }

                                $variantNames = preg_split('/\s*,\s*/', $nameVal) ?: [];
                                $variantNames = array_values(array_filter(array_map(
                                    static fn ($part) => trim((string) $part),
                                    $variantNames
                                ), static fn ($part) => $part !== ''));

                                if ($variantNames === []) {
                                    $variantNames = [$nameVal !== '' ? $nameVal : 'Color'];
                                }

                                $variantHexes = [];
                                if (count($variantNames) > 1) {
                                    foreach ($variantNames as $variantName) {
                                        $variantHexes[] = $namedColorHex[strtolower($variantName)] ?? ($hexVal !== '' ? $hexVal : '#d9d3c5');
                                    }
                                } else {
                                    $variantHexes[] = $hexVal !== ''
                                        ? $hexVal
                                        : ($namedColorHex[strtolower($variantNames[0])] ?? '#d9d3c5');
                                }

                                $pushColorVariant(implode(', ', $variantNames), $variantHexes, $imageVal);
                            }
                        } elseif ($primaryColor === '' && is_string($rawSwatches) && trim($rawSwatches) !== '') {
                            $parts = preg_split('/[,\n|]+/', $rawSwatches) ?: [];
                            foreach ($parts as $part) {
                                $c = trim((string) $part);
                                if (preg_match('/^#?[0-9A-Fa-f]{6}$/', $c) === 1) {
                                    $pushColorVariant('Color', [$c], '');
                                }
                            }
                        }

                        $seenVariantKeys = [];
                        $colorVariants = array_values(array_filter($colorVariants, static function ($variant) use (&$seenVariantKeys) {
                            $name = strtolower(trim((string) ($variant['name'] ?? '')));
                            $image = strtolower(trim((string) ($variant['image'] ?? '')));
                            $hexes = is_array($variant['hexes'] ?? null) ? $variant['hexes'] : [];
                            $hexesKey = strtolower(implode('|', array_map(static fn ($h) => trim((string) $h), $hexes)));
                            $key = ($name !== '' ? $name : 'color') . '|' . ($image !== '' ? $image : $hexesKey);

                            if ($hexesKey === '' || isset($seenVariantKeys[$key])) {
                                return false;
                            }

                            $seenVariantKeys[$key] = true;
                            return true;
                        }));

                        if (count($colorVariants) === 0) {
                            $fallbackName = $primaryColor !== '' ? $primaryColor : 'Gris';
                            $fallbackHex = $namedColorHex[strtolower($fallbackName)] ?? '#cec9bc';
                            $colorVariants = [
                                ['name' => $fallbackName, 'hex' => $fallbackHex, 'hexes' => [$fallbackHex], 'image' => $img],
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

                    <div class="favorite-card group relative mx-auto w-full max-w-[340px] p-1">
                        <div class="relative w-full">
                            <a href="{{ route('gafas.show', ['producto' => $producto->slug]) }}" class="favorite-image-stage relative block aspect-[16/9] w-full overflow-hidden" data-gafa-link aria-label="Ver {{ $producto->nombre }}">
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
                                    <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-transparent text-sm font-semibold text-[#4b9196] hover:text-rose-500 focus:outline-none focus:ring-2 focus:ring-[#7cc8cd]" aria-label="{{ $isFavorited ? 'Quitar de favoritos' : 'Agregar a favoritos' }}" aria-pressed="{{ $isFavorited ? 'true' : 'false' }}" data-favorite-button>
                                        <svg viewBox="0 0 24 24" class="h-6 w-6 {{ $heartClasses }}" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 21s-7-4.4-9.4-8.9C.4 8.2 2.7 5 6.3 5c2 0 3.4 1 4.7 2.4C12.3 6 13.7 5 15.7 5c3.6 0 5.9 3.2 3.7 7.1C19 16.6 12 21 12 21Z"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <a href="{{ route('gafas.show', ['producto' => $producto->slug]) }}" class="block" data-gafa-link>
                            <div class="pt-3 text-center">
                                <p class="favorite-name truncate text-[1.22rem] font-bold leading-tight sm:text-[1.3rem]">{{ $producto->nombre }}</p>

                                <div class="mt-1">
                                    @if($precioOferta)
                                        <div class="flex items-end justify-center gap-2">
                                            <p class="favorite-price text-[1.18rem] font-bold sm:text-[1.22rem]">$ {{ number_format((float)$precioOferta, 0, ',', '.') }}</p>
                                            @if($precio !== null)
                                                <p class="favorite-old-price text-xs font-medium line-through">{{ number_format((float)$precio, 0, ',', '.') }}</p>
                                            @endif
                                        </div>
                                    @else
                                        <p class="favorite-price text-[1.18rem] font-bold sm:text-[1.22rem]">$ {{ $precio !== null ? number_format((float)$precio, 0, ',', '.') : '—' }}</p>
                                    @endif
                                </div>

                                <div class="mt-2 flex {{ $swatchWrapClass }} items-center justify-center {{ $swatchGapClass }} px-1">
                                    @foreach($colorVariants as $i => $variant)
                                        @php
                                            $variantHexes = array_values(array_filter((array) ($variant['hexes'] ?? []), static fn ($hex) => is_string($hex) && trim($hex) !== ''));
                                            if ($variantHexes === []) {
                                                $variantHexes = [(string) ($variant['hex'] ?? '#cec9bc')];
                                            }

                                            $isMulti = count($variantHexes) > 1;
                                            $bgStyle = 'background-color: ' . $variantHexes[0] . ';';
                                            if ($isMulti) {
                                                $parts = [];
                                                $step = 100 / count($variantHexes);
                                                foreach ($variantHexes as $idx => $hex) {
                                                    $start = $step * $idx;
                                                    $end = $step * ($idx + 1);
                                                    $parts[] = $hex . ' ' . rtrim(rtrim(number_format($start, 4, '.', ''), '0'), '.') . '% ' . rtrim(rtrim(number_format($end, 4, '.', ''), '0'), '.') . '%';
                                                }
                                                $bgStyle = 'background: conic-gradient(' . implode(', ', $parts) . ');';
                                            }
                                        @endphp
                                        <button
                                            type="button"
                                            class="gafa-color-swatch inline-block shrink-0 {{ $swatchSizeClass }} rounded-full transition data-[active=true]:scale-125"
                                            style="{{ $bgStyle }}"
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

                <div class="flex items-center justify-center gap-4 text-[#2b9aa0]">
                    <a href="{{ $productos->previousPageUrl() ?: '#' }}"
                       class="inline-flex h-10 w-10 items-center justify-center {{ $productos->onFirstPage() ? 'pointer-events-none opacity-50' : '' }}"
                       aria-label="Anterior">
                        ←
                    </a>
                    <div class="px-2 py-1 text-sm font-semibold">
                        Página {{ $productos->currentPage() }} de {{ $productos->lastPage() }}
                    </div>
                    <a href="{{ $productos->nextPageUrl() ?: '#' }}"
                       class="inline-flex h-10 w-10 items-center justify-center {{ $productos->hasMorePages() ? '' : 'pointer-events-none opacity-50' }}"
                       aria-label="Siguiente">
                        →
                    </a>
                </div>
            </div>
        @endif
    </section>
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
        document.addEventListener('click', (e) => {
            const swatch = e.target.closest('.gafa-color-swatch');
            if (!swatch) return;

            e.preventDefault();
            e.stopPropagation();

            const container = swatch.closest('.group');
            if (!container) return;

            const mainImg = container.querySelector('img[data-gafa-main-image]');
            if (!mainImg) return;

            const colorImage = swatch.getAttribute('data-color-image');
            const defaultSrc = mainImg.getAttribute('data-default-src');
            const newSrc = colorImage && colorImage.trim() !== '' ? colorImage : defaultSrc;

            if (mainImg.src !== newSrc) {
                mainImg.src = newSrc;
            }

            const allSwatches = container.querySelectorAll('.gafa-color-swatch');
            allSwatches.forEach((s) => {
                const isActive = s === swatch;
                s.setAttribute('data-active', isActive ? 'true' : 'false');
                s.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        });

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
