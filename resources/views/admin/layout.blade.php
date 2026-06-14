<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — Óptica</title>

    @php($viteHot = public_path('hot'))
    @php($viteManifest = public_path('build/manifest.json'))
    @php($hasViteAssets = file_exists($viteHot) || file_exists($viteManifest))

    @if($hasViteAssets)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @if(!$hasViteAssets || app()->isLocal())
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="bg-zinc-100 text-zinc-900 antialiased font-sans overflow-hidden lg:overflow-auto">
<div class="fixed inset-0 z-[100] flex items-center justify-center bg-zinc-950/80 px-6 py-10 text-center lg:hidden">
    <div class="w-full max-w-md rounded-3xl border border-white/10 bg-white p-6 shadow-sm">
        <p class="text-base font-semibold text-zinc-900">Diríjase a una PC para poder configurar su página.</p>
    </div>
</div>
<div class="min-h-screen w-full md:flex">
    <aside class="w-full bg-zinc-900 text-zinc-100 md:sticky md:top-0 md:h-screen md:w-72 md:shrink-0 md:overflow-y-auto">
        <div class="flex items-center gap-3 px-4 py-5">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-white">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 14c0-2.2 1.8-4 4-4h2c2.2 0 4 1.8 4 4v1c0 2.2-1.8 4-4 4H8c-2.2 0-4-1.8-4-4v-1Z"/>
                    <path d="M14 14c0-2.2 1.8-4 4-4h2"/>
                    <path d="M10 14h4"/>
                </svg>
            </span>
            <div>
                <p class="text-sm font-semibold">Óptica</p>
                <p class="mt-0.5 text-xs text-white/60">Panel admin</p>
            </div>
        </div>

        <nav class="px-3 pb-6 text-sm">
            <p class="px-2 pt-2 text-xs font-semibold uppercase tracking-wide text-white/50">Accesos rápidos</p>

            @php($isDashboard = request()->routeIs('configuracion.index'))
            <a href="{{ route('configuracion.index') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isDashboard ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isDashboard ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Configuración
            </a>

            @php($isHero = request()->routeIs('configuracion.hero-banners.*'))
            <a href="{{ route('configuracion.hero-banners.index') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isHero ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isHero ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Hero Banners (Landing)
            </a>

            @php($isLandingIntro = request()->routeIs('configuracion.landing-intro.*'))
            <a href="{{ route('configuracion.landing-intro.edit') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isLandingIntro ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isLandingIntro ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Contenido landing
            </a>

            @php($isLandingBenefits = request()->routeIs('configuracion.landing-benefits.*'))
            <a href="{{ route('configuracion.landing-benefits.edit') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isLandingBenefits ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isLandingBenefits ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Beneficios landing
            </a>

            @php($isLandingCategories = request()->routeIs('configuracion.landing-categories.*'))
            <a href="{{ route('configuracion.landing-categories.edit') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isLandingCategories ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isLandingCategories ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Categorías landing
            </a>

            @php($isLandingPromoBanners = request()->routeIs('configuracion.landing-promo-banners.*'))
            <a href="{{ route('configuracion.landing-promo-banners.edit') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isLandingPromoBanners ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isLandingPromoBanners ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Banners promo (Landing)
            </a>

            @php($isLandingServices = request()->routeIs('configuracion.landing-services.*'))
            <a href="{{ route('configuracion.landing-services.edit') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isLandingServices ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isLandingServices ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Servicios landing
            </a>

            @php($isLandingHighlights = request()->routeIs('configuracion.landing-highlights.*'))
            <a href="{{ route('configuracion.landing-highlights.edit') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isLandingHighlights ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isLandingHighlights ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Destacados (Landing)
            </a>

            @php($isLandingHowItWorks = request()->routeIs('configuracion.landing-how-it-works.*'))
            <a href="{{ route('configuracion.landing-how-it-works.edit') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isLandingHowItWorks ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isLandingHowItWorks ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Cómo funciona (Landing)
            </a>

            @php($isLandingEssentialBenefits = request()->routeIs('configuracion.landing-essential-benefits.*'))
            <a href="{{ route('configuracion.landing-essential-benefits.edit') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isLandingEssentialBenefits ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isLandingEssentialBenefits ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Beneficios (Sección)
            </a>

            @php($isLandingFaq = request()->routeIs('configuracion.landing-faq.*'))
            <a href="{{ route('configuracion.landing-faq.edit') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isLandingFaq ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isLandingFaq ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Preguntas frecuentes (FAQ)
            </a>

            @php($isLandingLocation = request()->routeIs('configuracion.landing-location.*'))
            <a href="{{ route('configuracion.landing-location.edit') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isLandingLocation ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isLandingLocation ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Ubicación (Mapa)
            </a>

            @php($isLandingNewsletter = request()->routeIs('configuracion.landing-newsletter.*'))
            <a href="{{ route('configuracion.landing-newsletter.edit') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isLandingNewsletter ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isLandingNewsletter ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Newsletter (Landing)
            </a>

            @php($isLandingContact = request()->routeIs('configuracion.landing-contact.*'))
            <a href="{{ route('configuracion.landing-contact.edit') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isLandingContact ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isLandingContact ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Contacto (Landing)
            </a>

            @php($isLandingFooter = request()->routeIs('configuracion.landing-footer.*'))
            <a href="{{ route('configuracion.landing-footer.edit') }}" class="mt-1 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold {{ $isLandingFooter ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <span class="h-2 w-2 rounded-full {{ $isLandingFooter ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                Footer (Landing)
            </a>

            <a href="{{ url('/') }}" class="mt-4 flex items-center gap-3 rounded-2xl px-3 py-2 font-semibold text-white/80 hover:bg-white/10 hover:text-white">
                <span class="h-2 w-2 rounded-full bg-white/30"></span>
                Ver landing
            </a>
        </nav>
    </aside>

    <main class="w-full min-w-0 flex-1 bg-white px-4 py-8 md:px-8">
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

        @yield('content')
    </main>
</div>
</body>
</html>
