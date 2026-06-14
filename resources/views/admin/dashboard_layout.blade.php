<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Óptica</title>

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
<div class="min-h-screen">
    <div class="flex min-h-screen">
        <aside class="w-72 shrink-0 bg-zinc-950 text-white">
            <div class="flex items-center gap-3 px-5 py-5">
                <a href="{{ route('configuracion.index') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 hover:bg-white/20" aria-label="Inicio">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 10.5 12 3l9 7.5" />
                        <path d="M5 10v10h14V10" />
                    </svg>
                </a>
                <div>
                    <p class="text-sm font-semibold">Óptica</p>
                    <p class="text-xs text-white/70">Panel admin</p>
                </div>
            </div>

            <div class="px-5 pb-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-white/70">Configuración</p>
                <div class="mt-3 grid gap-2">
                    <a data-loader-link href="{{ route('configuracion.hero-banners.index') }}" class="rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold hover:bg-white/15">Banners principales</a>
                    <a data-loader-link href="{{ route('configuracion.landing-benefit-strip.edit') }}" class="rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold hover:bg-white/15">Franja de beneficios</a>
                    <a data-loader-link href="{{ route('configuracion.landing-category-photos.edit') }}" class="rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold hover:bg-white/15">Fotos categorías</a>
                    <a data-loader-link href="{{ route('configuracion.secondary-banners.index') }}" class="rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold hover:bg-white/15">Banner secundario</a>
                    <a data-loader-link href="{{ route('configuracion.landing-location.edit') }}" class="rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold hover:bg-white/15">Ubicación</a>
                    <a data-loader-link href="{{ route('configuracion.landing-footer-faq.edit') }}" class="rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold hover:bg-white/15">Preguntas Frecuentes</a>
                </div>

                @auth
                    @php($rolId = (int) (auth()->user()->rol_id ?? 0))
                    @php($isConfiguracionArea = request()->routeIs('configuracion.*'))
                    @php($isDashboardArea = request()->routeIs('dashboard') || request()->routeIs('dashboard.*'))
                    @php($isAdminArea = request()->routeIs('admin') || request()->routeIs('admin.*'))

                    <div class="mt-6 border-t border-white/10 pt-6">
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/60">Paneles</p>
                        <div class="mt-3 grid gap-2">
                            @if($rolId === 2)
                                @if($isConfiguracionArea)
                                    <a href="{{ route('dashboard') }}" class="rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold hover:bg-white/15">Ir a Dashboard</a>
                                @elseif($isDashboardArea)
                                    <a href="{{ route('configuracion.index') }}" class="rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold hover:bg-white/15">Ir a Configuración</a>
                                @endif
                            @elseif(in_array($rolId, [3, 4], true))
                                @if(!$isDashboardArea)
                                    <a href="{{ route('dashboard') }}" class="rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold hover:bg-white/15">Ir a Dashboard</a>
                                @endif

                                @if(!$isConfiguracionArea)
                                    <a href="{{ route('configuracion.index') }}" class="rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold hover:bg-white/15">Ir a Configuración</a>
                                @endif

                                @if(!$isAdminArea)
                                    <a href="{{ route('admin') }}" class="rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold hover:bg-white/15">Ir a Admin</a>
                                @endif
                            @endif
                        </div>
                    </div>
                @endauth

                <div class="mt-6 border-t border-white/10 pt-6 text-sm">
                    <a data-loader-link href="{{ url('/') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold text-white/80 hover:bg-white/10 hover:text-white">
                        <span class="h-2 w-2 rounded-full bg-white/30"></span>
                        Ir al Landing
                    </a>
                </div>
            </div>
        </aside>

        <main class="min-w-0 flex-1 bg-white">
            <div class="px-6 py-6">
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
            </div>
        </main>
    </div>
</div>

<div id="pageLoader" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-white/70"></div>
    <div class="absolute inset-0 flex items-center justify-center p-6">
        <div class="rounded-2xl bg-zinc-950 px-5 py-4 text-white shadow-xl">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                </svg>
                <span class="text-sm font-semibold">Cargando…</span>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var loader = document.getElementById('pageLoader');
        if (!loader) return;

        function showLoader() {
            loader.classList.remove('hidden');
        }

        function hideLoader() {
            loader.classList.add('hidden');
        }

        function shouldShowForLink(link, event) {
            if (!link) return false;
            if (event && event.defaultPrevented) return false;
            if (event && event.button !== 0) return false;
            if (event && (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey)) return false;
            if (link.hasAttribute('download')) return false;
            if (link.target && link.target !== '_self') return false;

            var href = link.getAttribute('href') || '';
            if (!href || href === '#') return false;
            if (href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return false;

            try {
                var url = new URL(href, window.location.href);
                if (url.protocol !== 'http:' && url.protocol !== 'https:') return false;

                // Si es solo un cambio de hash en la misma URL, no mostramos loader.
                var samePath = (url.origin === window.location.origin) &&
                    (url.pathname === window.location.pathname) &&
                    (url.search === window.location.search);
                if (samePath && url.hash && url.hash !== window.location.hash) return false;

                return true;
            } catch (e) {
                return false;
            }
        }

        document.addEventListener('click', function (e) {
            var link = e.target && e.target.closest ? e.target.closest('a') : null;
            if (!shouldShowForLink(link, e)) return;
            showLoader();
        }, true);

        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (!form || !form.tagName || form.tagName.toLowerCase() !== 'form') return;
            if (form.target && form.target !== '_self') return;
            showLoader();
        });

        // Para refresh/back/forward.
        window.addEventListener('beforeunload', function () {
            showLoader();
        });

        // Si el navegador restaura desde bfcache, ocultar.
        window.addEventListener('pageshow', function () {
            hideLoader();
        });
    })();
</script>
</body>
</html>
