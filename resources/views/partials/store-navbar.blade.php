@php
    $landingUrl = \Illuminate\Support\Facades\Route::has('landing') ? route('landing') : url('/');
    $loginUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login');
    $favoritesUrl = \Illuminate\Support\Facades\Route::has('favoritos.index') ? route('favoritos.index') : url('/favoritos');
    $trackingUrl = \Illuminate\Support\Facades\Route::has('pedido.tracking') ? route('pedido.tracking') : url('/seguimiento-pedido');
    $helpUrl = \Illuminate\Support\Facades\Route::has('gafas-deportivas.index') ? route('gafas-deportivas.index') : url('/gafas-deportivas');
    $offersUrl = $landingUrl . '#contacto';
    $gafasIndexUrl = \Illuminate\Support\Facades\Route::has('gafas.index') ? route('gafas.index') : url('/gafas');
    $aboutUrlFallback = $gafasIndexUrl;
    $landingFooterForNavbar = \App\Services\LandingFooterContent::load();
    $aboutPdfUrl = trim((string) ($landingFooterForNavbar['about_pdf_urls'][0] ?? ''));
    $aboutUrlRaw = $aboutPdfUrl !== '' ? $aboutPdfUrl : $aboutUrlFallback;
    $aboutIsExternal = preg_match('/^https?:\/\//i', $aboutUrlRaw) === 1;
    $aboutIsPdf = preg_match('/\.pdf($|\?)/i', $aboutUrlRaw) === 1;
    $aboutUrl = ($aboutIsExternal && $aboutIsPdf)
        ? ('https://docs.google.com/gview?embedded=1&url=' . urlencode($aboutUrlRaw))
        : $aboutUrlRaw;
    $aboutOpensNewTab = $aboutPdfUrl !== '';
    $allGafasUrl = $gafasIndexUrl;
    $gafasDropdownOptions = [
        ['label' => 'Todas las gafas', 'href' => $allGafasUrl],
        ['label' => 'Niños', 'href' => route('gafas.index', ['categories' => ['ninos', 'ninas']])],
        ['label' => 'Hombre', 'href' => route('gafas.index', ['categories' => ['hombre']])],
        ['label' => 'Mujeres', 'href' => route('gafas.index', ['categories' => ['mujeres']])],
        ['label' => 'Deportivas', 'href' => route('gafas.index', ['categories' => ['deportivas']])],
        ['label' => 'Polarizadas', 'href' => route('gafas.index', ['categories' => ['polarizadas']])],
        ['label' => 'Progresivos', 'href' => route('gafas.index', ['progresivos' => 1])],
    ];
    $storeNavbarRoleId = (int) (\Illuminate\Support\Facades\Auth::user()?->rol_id ?? 0);
    $storeNavbarIsAdmin = in_array($storeNavbarRoleId, [2, 3, 4], true);
    $storeNavbarHasConfigRoute = \Illuminate\Support\Facades\Route::has('configuracion.index');
    $storeNavbarHasDashboardRoute = \Illuminate\Support\Facades\Route::has('dashboard');
    $storeNavbarHasAdminRoute = \Illuminate\Support\Facades\Route::has('admin');
    $storeNavbarIsGafasListingRoute = request()->routeIs(
        'gafas.index',
        'gafas-mujeres.index',
        'gafas-hombre.index',
        'gafas-ninos.index',
        'gafas-ninas.index',
        'gafas-polarizadas.index',
        'polarizadas.index',
        'hombre.index',
        'gafas-deportivas.index',
        'gafas-descanso.index'
    );
@endphp

<div class="relative z-[70] bg-[#c9a96e] px-3 py-1.5 text-center text-[13px] font-semibold text-[#0f1117] md:text-[16px]">
    coloca tu texto
</div>

<header class="relative z-[70] border-b border-[#1a2e4a] border-t-2 border-t-[#c9a96e] bg-[#0f1b2d] text-white">
    <div class="w-full px-2 py-2 md:px-3">
        <div class="flex items-center gap-2 md:gap-4">
            <a href="{{ $landingUrl }}" class="shrink-0" aria-label="Inicio Optica">
                <img
                    src="{{ asset('images/navbar.png') }}"
                    alt="Optica"
                    class="h-12 w-auto md:h-20"
                    decoding="async"
                >
            </a>

            <nav class="ml-2 hidden flex-1 items-center justify-center gap-3 text-[14px] font-semibold md:ml-4 md:flex md:gap-6 md:text-[17px] md:leading-tight">
                <div class="relative" id="js-store-gafas-menu-wrap">
                    <button
                        id="js-store-gafas-menu-toggle"
                        type="button"
                        class="inline-flex items-center gap-1 whitespace-nowrap px-1 py-1 text-white hover:text-[#c9a96e]"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="js-store-gafas-menu-panel"
                    >
                        <span>Gafas formuladas</span>
                        <svg data-gafas-chevron viewBox="0 0 24 24" class="h-4 w-4 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m6 15 6-6 6 6"></path>
                        </svg>
                    </button>

                    <div id="js-store-gafas-menu-panel" class="pointer-events-none absolute left-0 top-[calc(100%+0.5rem)] z-[85] w-56 scale-95 rounded-2xl border border-zinc-200 bg-white p-2 opacity-0 shadow-[0_18px_35px_-18px_rgba(0,0,0,0.45)] transition-all duration-200" aria-hidden="true">
                        <p class="px-2 pb-1 pt-0.5 text-[10px] font-extrabold uppercase tracking-[0.16em] text-zinc-500">Categorías</p>
                        @foreach($gafasDropdownOptions as $option)
                            <a href="{{ $option['href'] }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-semibold text-zinc-800 transition-colors hover:bg-zinc-100">
                                <img src="{{ asset('images/BotonCategoria.png') }}" alt="" class="h-4 w-4 shrink-0" loading="lazy" decoding="async" aria-hidden="true">
                                <span>{{ $option['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ url('/gafas-polarizadas') }}" class="whitespace-nowrap px-1 py-1 text-white hover:text-[#c9a96e]">Gafas de sol</a>
                <a href="{{ $helpUrl }}" data-force-nav="1" class="whitespace-nowrap px-1 py-1 text-white hover:text-[#c9a96e]">Gafas deportivas</a>
                <a
                    href="{{ $aboutUrl }}"
                    data-about-link="1"
                    class="whitespace-nowrap px-1 py-1 text-white hover:text-[#c9a96e]"
                    @if($aboutOpensNewTab) target="_blank" rel="noopener noreferrer" @endif
                >Sobre nosotros</a>
            </nav>

            <div class="ml-auto flex items-center gap-1 md:ml-3 md:gap-2">
                @if($storeNavbarIsAdmin)
                    <div class="relative hidden md:block" id="js-store-admin-menu-wrap">
                        <button
                            id="js-store-admin-menu-toggle"
                            type="button"
                            class="inline-flex h-9 items-center gap-1.5 rounded-full border border-[#8ed3d8] bg-gradient-to-r from-[#eefaf9] to-[#f5fcff] px-3.5 text-[12px] font-extrabold tracking-[0.02em] text-[#0f6065] shadow-[0_6px_18px_-12px_rgba(31,144,150,0.75)] transition-all duration-200 hover:-translate-y-[1px] hover:shadow-[0_10px_24px_-12px_rgba(31,144,150,0.65)]"
                            aria-haspopup="true"
                            aria-expanded="false"
                            aria-controls="js-store-admin-menu-panel"
                        >
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#1f9096] text-[10px] font-black text-white">A</span>
                            <span>Admin</span>
                            <svg data-admin-chevron viewBox="0 0 24 24" class="h-4 w-4 text-[#0f6065] transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </button>

                        <div id="js-store-admin-menu-panel" class="pointer-events-none absolute right-0 top-[calc(100%+0.55rem)] z-[85] w-52 scale-95 rounded-2xl border border-[#cceef0] bg-white/95 p-2 opacity-0 shadow-[0_18px_35px_-18px_rgba(15,96,101,0.6)] backdrop-blur-sm transition-all duration-200" aria-hidden="true">
                            <p class="px-3 pb-1 pt-0.5 text-[10px] font-black uppercase tracking-[0.16em] text-[#4b9196]">Panel rapido</p>
                            @if($storeNavbarHasConfigRoute)
                                <a href="{{ route('configuracion.index') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 transition-colors hover:bg-[#edf8f8] hover:text-[#0f6065]">Configuracion</a>
                            @endif
                            @if($storeNavbarHasDashboardRoute)
                                <a href="{{ route('dashboard') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 transition-colors hover:bg-[#edf8f8] hover:text-[#0f6065]">Dashboard</a>
                            @endif
                            @if(in_array($storeNavbarRoleId, [3, 4], true) && $storeNavbarHasAdminRoute)
                                <a href="{{ route('admin') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 transition-colors hover:bg-[#edf8f8] hover:text-[#0f6065]">Admin</a>
                            @endif
                        </div>
                    </div>
                @endif

                <button type="button" data-open-store-search="1" class="hidden h-9 w-9 items-center justify-center rounded-full text-white hover:bg-white/10 md:inline-flex" aria-label="Buscar">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5"></path>
                    </svg>
                </button>

                <a href="{{ $favoritesUrl }}" class="hidden h-9 w-9 items-center justify-center rounded-full text-white hover:bg-white/10 md:inline-flex" aria-label="Favoritos">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20.5s-6.8-4.3-9-8.6c-1.9-3.7.3-6.9 3.7-6.9 2 0 3.6 1.1 5.3 3 1.7-1.9 3.3-3 5.3-3 3.4 0 5.6 3.2 3.7 6.9-2.2 4.3-9 8.6-9 8.6z"></path>
                    </svg>
                </a>


                <a href="{{ $trackingUrl }}" class="hidden h-9 w-9 items-center justify-center rounded-full text-white hover:bg-white/10 md:inline-flex" aria-label="Seguimiento de pedido">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h11v8H3z"></path>
                        <path d="M14 9h3.6l3 3v2h-6.6z"></path>
                        <circle cx="7" cy="18" r="1"></circle>
                        <circle cx="18" cy="18" r="1"></circle>
                    </svg>
                </a>

                <button id="js-store-mobile-menu-open" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-white hover:bg-white/10 md:hidden" aria-label="Abrir menú" aria-controls="js-store-mobile-menu" aria-expanded="false">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 6h18"></path>
                        <path d="M3 12h18"></path>
                        <path d="M3 18h18"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>

<div id="js-store-mobile-menu" class="pointer-events-none fixed inset-0 z-[75] opacity-0 transition-opacity duration-200 md:hidden" aria-hidden="true">
    <button id="js-store-mobile-menu-backdrop" type="button" class="absolute inset-0 bg-black/35" aria-label="Cerrar menú"></button>
    <aside id="js-store-mobile-menu-drawer" class="absolute right-0 top-0 h-dvh w-[min(21rem,86vw)] translate-x-full overflow-y-auto border-l border-zinc-200 bg-white p-5 shadow-xl transition-transform duration-200">
        <div class="flex items-center justify-between">
            <p class="text-sm font-extrabold uppercase tracking-[0.14em] text-zinc-500">Menú</p>
            <button id="js-store-mobile-menu-close" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-zinc-700 hover:bg-black/5" aria-label="Cerrar menú">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
            </button>
        </div>

        <nav class="mt-5 grid gap-2 text-[15px]">
            <div class="rounded-xl border border-zinc-200 px-2 py-2">
                <button id="js-store-mobile-gafas-toggle" type="button" class="flex w-full items-center justify-between rounded-lg px-2 py-2 text-left font-semibold text-zinc-900 hover:bg-zinc-50" aria-expanded="false" aria-controls="js-store-mobile-gafas-panel">
                    <span>Gafas formuladas</span>
                    <svg viewBox="0 0 24 24" class="h-4 w-4 text-zinc-700 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m6 15 6-6 6 6"></path>
                    </svg>
                </button>
                <div id="js-store-mobile-gafas-panel" class="mt-1 hidden gap-1.5 pl-1 pr-1 pb-1">
                    @foreach($gafasDropdownOptions as $option)
                        <a href="{{ $option['href'] }}" class="flex items-center gap-2.5 rounded-lg border border-zinc-200 px-3 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50">
                            <img src="{{ asset('images/BotonCategoria.png') }}" alt="" class="h-4 w-4 shrink-0" loading="lazy" decoding="async" aria-hidden="true">
                            <span>{{ $option['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <a href="{{ url('/gafas-polarizadas') }}" class="rounded-xl border border-zinc-200 px-4 py-3 font-semibold text-zinc-900 hover:bg-zinc-50">Gafas de sol</a>
            <a href="{{ $helpUrl }}" data-force-nav="1" class="rounded-xl border border-zinc-200 px-4 py-3 font-semibold text-zinc-900 hover:bg-zinc-50">Gafas deportivas</a>
            <a
                href="{{ $aboutUrl }}"
                data-about-link="1"
                class="rounded-xl border border-zinc-200 px-4 py-3 font-semibold text-zinc-900 hover:bg-zinc-50"
                @if($aboutOpensNewTab) target="_blank" rel="noopener noreferrer" @endif
            >Sobre nosotros</a>
            @if($storeNavbarIsAdmin)
                <div class="rounded-2xl border border-[#bfe9ec] bg-gradient-to-r from-[#effafa] to-[#f6fcff] px-3 py-2">
                    <button id="js-store-mobile-admin-toggle" type="button" class="flex w-full items-center justify-between rounded-xl px-2 py-2 text-left font-extrabold tracking-[0.02em] text-[#0f6065] hover:bg-white/70" aria-expanded="false" aria-controls="js-store-mobile-admin-panel">
                        <span class="inline-flex items-center gap-2">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#1f9096] text-[10px] font-black text-white">A</span>
                            <span>Admin</span>
                        </span>
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-[#0f6065] transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </button>
                    <div id="js-store-mobile-admin-panel" class="mt-2 hidden gap-2 pl-2">
                        @if($storeNavbarHasConfigRoute)
                            <a href="{{ route('configuracion.index') }}" class="block rounded-lg border border-[#caeaed] bg-white px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-[#edf8f8]">Configuracion</a>
                        @endif
                        @if($storeNavbarHasDashboardRoute)
                            <a href="{{ route('dashboard') }}" class="block rounded-lg border border-[#caeaed] bg-white px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-[#edf8f8]">Dashboard</a>
                        @endif
                        @if(in_array($storeNavbarRoleId, [3, 4], true) && $storeNavbarHasAdminRoute)
                            <a href="{{ route('admin') }}" class="block rounded-lg border border-[#caeaed] bg-white px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-[#edf8f8]">Admin</a>
                        @endif
                    </div>
                </div>
            @endif
            <button type="button" data-open-store-search="1" class="flex items-center justify-between rounded-xl border border-zinc-200 px-4 py-3 font-semibold text-zinc-900 hover:bg-zinc-50">
                <span>Buscar una gafa o sección</span>
                <svg viewBox="0 0 24 24" class="h-5 w-5 text-zinc-700" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m20 20-3.5-3.5"></path>
                </svg>
            </button>
            <a href="{{ $favoritesUrl }}" class="flex items-center justify-between rounded-xl border border-zinc-200 px-4 py-3 font-semibold text-zinc-900 hover:bg-zinc-50">
                <span>Favoritos</span>
                <svg viewBox="0 0 24 24" class="h-5 w-5 text-zinc-700" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 20.5s-6.8-4.3-9-8.6c-1.9-3.7.3-6.9 3.7-6.9 2 0 3.6 1.1 5.3 3 1.7-1.9 3.3-3 5.3-3 3.4 0 5.6 3.2 3.7 6.9-2.2 4.3-9 8.6-9 8.6z"></path>
                </svg>
            </a>

            <a href="{{ $trackingUrl }}" class="flex items-center justify-between rounded-xl border border-zinc-200 px-4 py-3 font-semibold text-zinc-900 hover:bg-zinc-50">
                <span>Rastrear pedido</span>
                <svg viewBox="0 0 24 24" class="h-5 w-5 text-zinc-700" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 6h11v8H3z"></path>
                    <path d="M14 9h3.6l3 3v2h-6.6z"></path>
                    <circle cx="7" cy="18" r="1"></circle>
                    <circle cx="18" cy="18" r="1"></circle>
                </svg>
            </a>
        </nav>
    </aside>
</div>

@php
    $hideStoreBannerForRoute = request()->routeIs('pagos.dummy') || request()->routeIs('pagos.dummy.confirm');
@endphp
@if(($showStoreBanner ?? true) && !$hideStoreBannerForRoute)
    <section aria-label="Banner principal" class="w-full">
        @php
            $resolvedStoreBannerImages = [];
            if (is_array($storeBannerImages ?? null)) {
                foreach ($storeBannerImages as $candidateImage) {
                    $candidateImage = trim((string) $candidateImage);
                    if ($candidateImage !== '' && !in_array($candidateImage, $resolvedStoreBannerImages, true)) {
                        $resolvedStoreBannerImages[] = $candidateImage;
                    }
                }
            }

            if ($resolvedStoreBannerImages === []) {
                $resolvedStoreBannerImage = trim((string) ($storeBannerImage ?? ''));
                $resolvedStoreBannerImage = $resolvedStoreBannerImage !== '' ? $resolvedStoreBannerImage : asset('images/borrardespues.png');
                $resolvedStoreBannerImages[] = $resolvedStoreBannerImage;
            }

            $resolvedStoreBannerImage = $resolvedStoreBannerImages[0];
        @endphp
        @if(count($resolvedStoreBannerImages) > 1)
            <style>
                .store-banner-carousel {
                    width: 100%;
                    overflow: hidden;
                }

                .store-banner-carousel-track {
                    display: flex;
                    width: 100%;
                    transform: translateX(0%);
                    transition: transform 700ms ease;
                    will-change: transform;
                }

                .store-banner-carousel-slide {
                    position: relative;
                    flex: 0 0 100%;
                    width: 100%;
                }

                .store-banner-carousel-slide img {
                    display: block;
                    width: 100%;
                    height: auto;
                    object-fit: contain;
                    decoding: async;
                }
            </style>

            @if($storeNavbarIsGafasListingRoute)
                <style>
                    .store-banner-carousel {
                        height: clamp(11rem, 40.47vw, 747px);
                        max-height: 747px;
                    }

                    .store-banner-carousel-track,
                    .store-banner-carousel-slide {
                        height: 100%;
                    }

                    .store-banner-carousel-slide img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        object-position: center;
                    }
                </style>
            @endif

            <div class="store-banner-carousel" data-store-banner-carousel="1">
                <div class="store-banner-carousel-track" data-store-banner-track="1">
                    @foreach($resolvedStoreBannerImages as $bannerImage)
                        <div class="store-banner-carousel-slide" data-store-banner-slide="1">
                            <img
                                src="{{ $bannerImage }}"
                                alt="Banner principal"
                                loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                fetchpriority="{{ $loop->first ? 'high' : 'low' }}"
                                decoding="async"
                            >
                        </div>
                    @endforeach
                </div>
            </div>

            <script>
                (() => {
                    const track = document.querySelector('[data-store-banner-track="1"]');
                    if (!track) return;

                    const slides = Array.from(track.querySelectorAll('[data-store-banner-slide="1"]'));
                    if (slides.length < 2) return;

                    const firstClone = slides[0].cloneNode(true);
                    track.appendChild(firstClone);

                    let index = 0;

                    const advance = () => {
                        index += 1;
                        track.style.transition = 'transform 700ms ease';
                        track.style.transform = `translateX(-${index * 100}%)`;
                    };

                    track.addEventListener('transitionend', () => {
                        if (index !== slides.length) {
                            return;
                        }

                        track.style.transition = 'none';
                        index = 0;
                        track.style.transform = 'translateX(0%)';
                        void track.offsetHeight;
                    });

                    setInterval(advance, 7000);
                })();
            </script>
        @else
            <div class="store-banner-image-wrap">
                <img
                    src="{{ $resolvedStoreBannerImage }}"
                    alt="Banner principal"
                    class="store-banner-image block w-full h-auto object-contain"
                    decoding="async"
                >
            </div>

            @if($storeNavbarIsGafasListingRoute)
                <style>
                    .store-banner-image-wrap {
                        height: clamp(11rem, 40.47vw, 747px);
                        max-height: 747px;
                        overflow: hidden;
                    }

                    .store-banner-image {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        object-position: center;
                    }
                </style>
            @endif
        @endif
    </section>
@endif

@guest
    <div id="js-need-account-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-950/60" data-close="1"></div>
        <div class="relative mx-auto flex min-h-dvh max-w-lg items-center justify-center px-4">
            <div class="w-full rounded-3xl border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p id="js-need-account-title" class="text-sm font-semibold text-zinc-900">Necesitas una cuenta</p>
                        <p id="js-need-account-desc" class="mt-1 text-sm text-zinc-600">Para continuar con el pago debes iniciar sesión o registrarte.</p>
                    </div>
                    <button type="button" class="rounded-2xl px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100" data-close="1">Cerrar</button>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <a href="{{ $loginUrl }}" class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-4 py-3 text-sm font-semibold text-white hover:bg-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-400">Iniciar sesión</a>
                    <a href="{{ route('register.show') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-900 hover:bg-zinc-50">Registrarme</a>
                </div>
            </div>
        </div>
    </div>
@endguest

@include('partials.store_search_modal')

<script>
(() => {
    const isAuthed = Boolean(@json(\Illuminate\Support\Facades\Auth::check()));

    const needAccountModal = document.getElementById('js-need-account-modal');
    const needAccountDesc = document.getElementById('js-need-account-desc');
    const mobileMenu = document.getElementById('js-store-mobile-menu');
    const mobileMenuDrawer = document.getElementById('js-store-mobile-menu-drawer');
    const mobileMenuOpenBtn = document.getElementById('js-store-mobile-menu-open');
    const mobileMenuCloseBtn = document.getElementById('js-store-mobile-menu-close');
    const mobileMenuBackdrop = document.getElementById('js-store-mobile-menu-backdrop');
    const adminMenuWrap = document.getElementById('js-store-admin-menu-wrap');
    const adminMenuToggle = document.getElementById('js-store-admin-menu-toggle');
    const adminMenuPanel = document.getElementById('js-store-admin-menu-panel');
    const gafasMenuWrap = document.getElementById('js-store-gafas-menu-wrap');
    const gafasMenuToggle = document.getElementById('js-store-gafas-menu-toggle');
    const gafasMenuPanel = document.getElementById('js-store-gafas-menu-panel');
    const mobileAdminToggle = document.getElementById('js-store-mobile-admin-toggle');
    const mobileAdminPanel = document.getElementById('js-store-mobile-admin-panel');
    const mobileGafasToggle = document.getElementById('js-store-mobile-gafas-toggle');
    const mobileGafasPanel = document.getElementById('js-store-mobile-gafas-panel');

    function closeDesktopAdminMenu() {
        if (!adminMenuToggle || !adminMenuPanel) return;
        adminMenuToggle.setAttribute('aria-expanded', 'false');
        adminMenuPanel.classList.add('pointer-events-none', 'opacity-0', 'scale-95');
        adminMenuPanel.classList.remove('pointer-events-auto', 'opacity-100', 'scale-100');
        adminMenuPanel.setAttribute('aria-hidden', 'true');
        const chevron = adminMenuToggle.querySelector('[data-admin-chevron]');
        chevron?.classList.remove('rotate-180');
    }

    function openDesktopAdminMenu() {
        if (!adminMenuToggle || !adminMenuPanel) return;
        adminMenuToggle.setAttribute('aria-expanded', 'true');
        adminMenuPanel.classList.remove('pointer-events-none', 'opacity-0', 'scale-95');
        adminMenuPanel.classList.add('pointer-events-auto', 'opacity-100', 'scale-100');
        adminMenuPanel.setAttribute('aria-hidden', 'false');
        const chevron = adminMenuToggle.querySelector('[data-admin-chevron]');
        chevron?.classList.add('rotate-180');
    }

    function closeDesktopGafasMenu() {
        if (!gafasMenuToggle || !gafasMenuPanel) return;
        gafasMenuToggle.setAttribute('aria-expanded', 'false');
        gafasMenuPanel.classList.add('pointer-events-none', 'opacity-0', 'scale-95');
        gafasMenuPanel.classList.remove('pointer-events-auto', 'opacity-100', 'scale-100');
        gafasMenuPanel.setAttribute('aria-hidden', 'true');
        const chevron = gafasMenuToggle.querySelector('[data-gafas-chevron]');
        if (chevron instanceof Element) {
            chevron.style.transform = 'rotate(0deg)';
        }
    }

    function openDesktopGafasMenu() {
        if (!gafasMenuToggle || !gafasMenuPanel) return;
        gafasMenuToggle.setAttribute('aria-expanded', 'true');
        gafasMenuPanel.classList.remove('pointer-events-none', 'opacity-0', 'scale-95');
        gafasMenuPanel.classList.add('pointer-events-auto', 'opacity-100', 'scale-100');
        gafasMenuPanel.setAttribute('aria-hidden', 'false');
        const chevron = gafasMenuToggle.querySelector('[data-gafas-chevron]');
        if (chevron instanceof Element) {
            chevron.style.transform = 'rotate(180deg)';
        }
    }

    function closeMobileAdminMenu() {
        if (!mobileAdminToggle || !mobileAdminPanel) return;
        mobileAdminToggle.setAttribute('aria-expanded', 'false');
        mobileAdminPanel.classList.add('hidden');
        const chevron = mobileAdminToggle.querySelector('svg');
        chevron?.classList.remove('rotate-180');
    }

    function toggleMobileAdminMenu() {
        if (!mobileAdminToggle || !mobileAdminPanel) return;
        const isOpen = mobileAdminToggle.getAttribute('aria-expanded') === 'true';
        if (isOpen) {
            closeMobileAdminMenu();
            return;
        }

        mobileAdminToggle.setAttribute('aria-expanded', 'true');
        mobileAdminPanel.classList.remove('hidden');
        const chevron = mobileAdminToggle.querySelector('svg');
        chevron?.classList.add('rotate-180');
    }

    function closeMobileGafasMenu() {
        if (!mobileGafasToggle || !mobileGafasPanel) return;
        mobileGafasToggle.setAttribute('aria-expanded', 'false');
        mobileGafasPanel.classList.add('hidden');
        const chevron = mobileGafasToggle.querySelector('svg');
        if (chevron instanceof Element) {
            chevron.style.transform = 'rotate(0deg)';
        }
    }

    function toggleMobileGafasMenu() {
        if (!mobileGafasToggle || !mobileGafasPanel) return;
        const isOpen = mobileGafasToggle.getAttribute('aria-expanded') === 'true';
        if (isOpen) {
            closeMobileGafasMenu();
            return;
        }

        mobileGafasToggle.setAttribute('aria-expanded', 'true');
        mobileGafasPanel.classList.remove('hidden');
        const chevron = mobileGafasToggle.querySelector('svg');
        if (chevron instanceof Element) {
            chevron.style.transform = 'rotate(180deg)';
        }
    }

    function openMobileMenu() {
        if (!mobileMenu || !mobileMenuDrawer || !mobileMenuOpenBtn) return;
        mobileMenu.classList.remove('pointer-events-none', 'opacity-0');
        mobileMenu.classList.add('pointer-events-auto', 'opacity-100');
        mobileMenuDrawer.classList.remove('translate-x-full');
        mobileMenuOpenBtn.setAttribute('aria-expanded', 'true');
        mobileMenu.setAttribute('aria-hidden', 'false');
        document.documentElement.classList.add('mobile-nav-open');
        if (!document.body.dataset.prevOverflow) document.body.dataset.prevOverflow = document.body.style.overflow || '';
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        if (!mobileMenu || !mobileMenuDrawer || !mobileMenuOpenBtn) return;
        closeMobileAdminMenu();
        closeMobileGafasMenu();
        mobileMenuDrawer.classList.add('translate-x-full');
        mobileMenu.classList.remove('pointer-events-auto', 'opacity-100');
        mobileMenu.classList.add('pointer-events-none', 'opacity-0');
        mobileMenuOpenBtn.setAttribute('aria-expanded', 'false');
        mobileMenu.setAttribute('aria-hidden', 'true');
        document.documentElement.classList.remove('mobile-nav-open');
        const prev = document.body.dataset.prevOverflow;
        document.body.style.overflow = prev ?? '';
        delete document.body.dataset.prevOverflow;
    }

    if (mobileMenuOpenBtn) {
        mobileMenuOpenBtn.addEventListener('click', () => openMobileMenu());
    }
    if (mobileMenuCloseBtn) {
        mobileMenuCloseBtn.addEventListener('click', () => closeMobileMenu());
    }
    if (mobileMenuBackdrop) {
        mobileMenuBackdrop.addEventListener('click', () => closeMobileMenu());
    }
    if (mobileAdminToggle) {
        mobileAdminToggle.addEventListener('click', () => toggleMobileAdminMenu());
    }
    if (mobileGafasToggle) {
        mobileGafasToggle.addEventListener('click', () => toggleMobileGafasMenu());
    }
    if (mobileMenu) {
        mobileMenu.querySelectorAll('a[href]').forEach((a) => {
            a.addEventListener('click', () => closeMobileMenu());
        });
    }

    // Fallback: fuerza navegación en enlaces críticos del menú.
    document.querySelectorAll('a[data-force-nav="1"], a[data-about-link="1"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const href = link.getAttribute('href');
            if (!href) return;
            const target = link.getAttribute('target');
            // Si el enlace debe abrir en otra pestaña, no intervenimos.
            if (target === '_blank' || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
                return;
            }

            event.preventDefault();
            window.location.assign(href);
        });
    });

    if (adminMenuToggle) {
        adminMenuToggle.addEventListener('click', () => {
            const isOpen = adminMenuToggle.getAttribute('aria-expanded') === 'true';
            if (isOpen) {
                closeDesktopAdminMenu();
                return;
            }
            openDesktopAdminMenu();
        });
    }
    if (gafasMenuToggle) {
        gafasMenuToggle.addEventListener('click', () => {
            const isOpen = gafasMenuToggle.getAttribute('aria-expanded') === 'true';
            if (isOpen) {
                closeDesktopGafasMenu();
                return;
            }
            openDesktopGafasMenu();
        });
    }
    document.addEventListener('click', (e) => {
        if (!adminMenuWrap || !adminMenuToggle || !adminMenuPanel) return;
        const target = e.target;
        if (!(target instanceof Node)) return;
        if (adminMenuWrap.contains(target)) return;
        closeDesktopAdminMenu();
    });
    document.addEventListener('click', (e) => {
        if (!gafasMenuWrap || !gafasMenuToggle || !gafasMenuPanel) return;
        const target = e.target;
        if (!(target instanceof Node)) return;
        if (gafasMenuWrap.contains(target)) return;
        closeDesktopGafasMenu();
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            closeMobileMenu();
        } else {
            closeDesktopAdminMenu();
            closeDesktopGafasMenu();
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeMobileMenu();
            closeDesktopAdminMenu();
            closeDesktopGafasMenu();
        }
    });

    function openNeedAccountModal(mode) {
        if (!needAccountModal) return;
        if (needAccountDesc) {
            needAccountDesc.textContent = mode === 'carrito'
                ? 'Para continuar con el carrito debes iniciar sesión o registrarte.'
                : 'Para continuar con el pago debes iniciar sesión o registrarte.';
        }
        needAccountModal.classList.remove('hidden');
        needAccountModal.setAttribute('aria-hidden', 'false');
    }

    function closeNeedAccountModal() {
        if (!needAccountModal) return;
        needAccountModal.classList.add('hidden');
        needAccountModal.setAttribute('aria-hidden', 'true');
    }

    if (needAccountModal) {
        needAccountModal.addEventListener('click', (e) => {
            const target = e.target;
            if (!(target instanceof HTMLElement)) return;
            if (target.dataset.close === '1') closeNeedAccountModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeNeedAccountModal();
        });
    }

    function getCartEls() {
        return {
            link: document.getElementById('js-cart-link'),
            badge: document.getElementById('js-cart-count'),
        };
    }

    function flyOne(fromButton) {
        const { link } = getCartEls();
        if (!fromButton || !link) return;

        const b = fromButton.getBoundingClientRect();
        const c = link.getBoundingClientRect();
        if (!b.width || !b.height || !c.width || !c.height) return;

        const startX = b.left + b.width / 2;
        const startY = b.top + b.height / 2;
        const endX = c.left + c.width / 2;
        const endY = c.top + c.height / 2;

        const bubble = document.createElement('div');
        bubble.textContent = '1';
        bubble.className = 'fixed z-[60] pointer-events-none flex h-7 w-7 items-center justify-center rounded-full bg-zinc-950 text-xs font-bold text-white shadow-sm';
        bubble.style.left = `${startX}px`;
        bubble.style.top = `${startY}px`;
        bubble.style.transform = 'translate(-50%, -50%) scale(1)';
        bubble.style.opacity = '1';
        document.body.appendChild(bubble);

        const dx = endX - startX;
        const dy = endY - startY;
        const arc = Math.min(200, Math.max(110, Math.abs(dy) * 0.55));

        const anim = bubble.animate(
            [
                { transform: 'translate(-50%, -50%) scale(1)', opacity: 1 },
                { transform: `translate(calc(-50% + ${dx * 0.25}px), calc(-50% + ${dy * 0.25 - arc}px)) scale(0.98)`, opacity: 1 },
                { transform: `translate(calc(-50% + ${dx * 0.55}px), calc(-50% + ${dy * 0.55 - arc * 0.65}px)) scale(0.85)`, opacity: 0.9 },
                { transform: `translate(calc(-50% + ${dx * 0.8}px), calc(-50% + ${dy * 0.8 - arc * 0.25}px)) scale(0.7)`, opacity: 0.65 },
                { transform: `translate(calc(-50% + ${dx}px), calc(-50% + ${dy}px)) scale(0.55)`, opacity: 0.25 },
            ],
            {
                duration: 2100,
                easing: 'ease-in-out',
                fill: 'forwards',
            }
        );

        const cleanup = () => bubble.remove();
        anim.addEventListener('finish', cleanup);
        setTimeout(cleanup, 2600);
    }

    function enhanceAddToCart() {
        const forms = document.querySelectorAll('form[data-cart-add-form]');
        if (!forms.length) return;

        for (const form of forms) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                if (!isAuthed) {
                    openNeedAccountModal('carrito');
                    return;
                }

                const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                if (submitBtn) {
                    flyOne(submitBtn);
                }

                try {
                    const formData = new FormData(form);
                    const res = await fetch(form.action, {
                        method: (form.method || 'POST').toUpperCase(),
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (!res.ok) {
                        form.submit();
                        return;
                    }

                    const data = await res.json().catch(() => null);
                    if (!data || data.ok !== true) {
                        form.submit();
                        return;
                    }
                } catch (_) {
                    form.submit();
                }
            });
        }
    }

    function enhanceFavorites() {
        const forms = document.querySelectorAll('form[data-favorite-form]');
        if (!forms.length) return;

        for (const form of forms) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const btn = form.querySelector('[data-favorite-button]');
                const icon = btn ? btn.querySelector('svg') : null;
                if (!btn || !icon) {
                    form.submit();
                    return;
                }

                btn.disabled = true;

                try {
                    const formData = new FormData(form);
                    const res = await fetch(form.action, {
                        method: (form.method || 'POST').toUpperCase(),
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (!res.ok) {
                        form.submit();
                        return;
                    }

                    const data = await res.json().catch(() => null);
                    if (!data || data.ok !== true || typeof data.favorited !== 'boolean') {
                        form.submit();
                        return;
                    }

                    const nowFav = data.favorited;
                    icon.classList.toggle('text-rose-500', nowFav);
                    icon.classList.toggle('text-zinc-400', !nowFav);
                    icon.setAttribute('fill', nowFav ? 'currentColor' : 'none');

                    const ariaPressed = nowFav ? 'true' : 'false';
                    btn.setAttribute('aria-pressed', ariaPressed);
                    const label = nowFav ? 'Quitar de favoritos' : 'Agregar a favoritos';
                    btn.setAttribute('aria-label', label);

                    const container = form.closest('[data-favorite-card]');
                    if (container && form.dataset.favoriteContext === 'list-favoritos' && !nowFav) {
                        container.remove();
                    }
                } catch (_) {
                    form.submit();
                } finally {
                    btn.disabled = false;
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        enhanceAddToCart();
        enhanceFavorites();

        const payBtn = document.getElementById('needAuthPay');
        if (payBtn && !isAuthed) {
            payBtn.addEventListener('click', () => openNeedAccountModal('pago'));
        }
    });
})();
</script>
