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

    <style>
        @media (max-width: 767px) {
            .mobile-drawer-page img {
                max-width: 100%;
                height: auto;
            }

            .mobile-drawer-page .overflow-x-auto {
                -webkit-overflow-scrolling: touch;
            }

            #dashboardSidebar {
                width: min(19rem, 82vw);
            }

            #dashboardSidebar > div:first-child {
                padding-top: 0.9rem;
                padding-bottom: 0.9rem;
            }

            #dashboardSidebar nav {
                padding-left: 0.65rem;
                padding-right: 0.65rem;
                padding-bottom: 0.8rem;
                font-size: 0.92rem;
            }

            #dashboardSidebar nav a,
            #dashboardSidebar .mx-3 a {
                margin-top: 0.3rem;
                padding-top: 0.7rem;
                padding-bottom: 0.7rem;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
                border-radius: 1rem;
                font-size: 0.92rem;
                line-height: 1.2rem;
            }

            #dashboardSidebar .mx-3 {
                margin-left: 0.65rem;
                margin-right: 0.65rem;
                padding-top: 0.8rem;
                padding-bottom: 0.8rem;
            }

            #dashboardSidebar .h-10.w-10 {
                height: 2.25rem;
                width: 2.25rem;
            }

            #dashboardSidebar .h-5.w-5 {
                height: 1rem;
                width: 1rem;
            }

            #dashboardSidebar .text-sm.font-semibold:first-child {
                font-size: 1.1rem;
                line-height: 1.35rem;
            }
        }

        @media (max-width: 767px) and (max-height: 760px) {
            #dashboardSidebar {
                width: min(18rem, 80vw);
            }

            #dashboardSidebar > div:first-child {
                padding-top: 0.75rem;
                padding-bottom: 0.75rem;
            }

            #dashboardSidebar nav a,
            #dashboardSidebar .mx-3 a {
                margin-top: 0.22rem;
                padding-top: 0.58rem;
                padding-bottom: 0.58rem;
                font-size: 0.86rem;
                line-height: 1.1rem;
            }

            #dashboardSidebar nav {
                padding-bottom: 0.55rem;
                font-size: 0.86rem;
            }

            #dashboardSidebar .mx-3 {
                padding-top: 0.6rem;
                padding-bottom: 0.6rem;
            }
        }

    </style>
</head>
@php($allowMobileDashboard = request()->routeIs('dashboard') || request()->routeIs('dashboard.*'))
@php($allowMobileAdminUsers = request()->routeIs('admin') || request()->routeIs('admin.empleados') || request()->routeIs('admin.company-emails') || request()->routeIs('admin.informacion*'))
@php($allowMobileAdminCatalog = request()->routeIs('admin.gafas-mujeres.*') || request()->routeIs('admin.gafas-descanso.*') || request()->routeIs('admin.gafas-ninas.*') || request()->routeIs('admin.gafas-ninos.*') || request()->routeIs('admin.gafas-polarizadas.*') || request()->routeIs('admin.gafas-hombre.*'))
@php($allowMobileView = $allowMobileAdminUsers || $allowMobileDashboard || $allowMobileAdminCatalog)
@php($useMobileDrawer = $allowMobileView)

<body class="bg-zinc-100 text-zinc-900 antialiased font-sans {{ $allowMobileView ? 'mobile-drawer-page overflow-auto' : 'overflow-hidden lg:overflow-auto' }}">
@if(! $allowMobileView)
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-zinc-950/80 px-6 py-10 text-center lg:hidden">
        <div class="w-full max-w-md rounded-3xl border border-white/10 bg-white p-6 shadow-sm">
            <p class="text-base font-semibold text-zinc-900">Diríjase a una PC para ver el dashboard.</p>
        </div>
    </div>
@endif

@php($loginUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login'))

@if($useMobileDrawer)
    <div id="dashboardSidebarBackdrop" class="fixed inset-0 z-40 hidden bg-zinc-950/45 md:hidden"></div>
@endif

<div class="min-h-screen w-full md:flex">
    <aside id="dashboardSidebar" class="{{ $useMobileDrawer ? 'fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] -translate-x-full overflow-y-auto bg-zinc-950 text-white transition-transform duration-200 ease-out md:sticky md:top-0 md:h-screen md:w-72 md:shrink-0 md:translate-x-0 md:overflow-y-auto' : 'w-full bg-zinc-950 text-white md:sticky md:top-0 md:h-screen md:w-72 md:shrink-0 md:overflow-y-auto' }}">
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
                <p class="mt-0.5 text-xs text-white/60">Dashboard</p>
            </div>
        </div>

        <nav class="px-3 pb-6 text-sm">
            @php($isAdminArea = request()->routeIs('admin') || request()->routeIs('admin.*'))
            @php($isAdminUsersArea = request()->routeIs('admin') || request()->routeIs('admin.empleados') || request()->routeIs('admin.usuarios.*') || request()->routeIs('admin.company-emails*') || request()->routeIs('admin.informacion*'))
            @php($isConfiguracionArea = request()->routeIs('configuracion.*'))
            @php($isDashboardArea = request()->routeIs('dashboard') || request()->routeIs('dashboard.*'))

            @if($isAdminUsersArea)
                @php($isUsuarios = request()->routeIs('admin') || request()->routeIs('admin.usuarios.*'))
                <a href="{{ route('admin') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isUsuarios ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isUsuarios ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    Usuarios
                </a>

                @php($isEmpleados = request()->routeIs('admin.empleados'))
                <a href="{{ route('admin.empleados') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isEmpleados ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isEmpleados ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    Empleados
                </a>

                @php($isCompanyEmails = request()->routeIs('admin.company-emails*'))
                <a href="{{ route('admin.company-emails') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isCompanyEmails ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isCompanyEmails ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    Correos empresa
                </a>

                @php($isInformacion = request()->routeIs('admin.informacion*'))
                <a href="{{ route('admin.informacion') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isInformacion ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isInformacion ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    Información
                </a>
            @else
                @php($isEnvios = request()->routeIs('dashboard.proximos-envios'))
                <a href="{{ route('dashboard.proximos-envios') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isEnvios ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isEnvios ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    Próximos envíos
                </a>

                @php($isPreciosNaraTodo = request()->routeIs('dashboard.precios-naratodo.*'))
                <a href="{{ route('dashboard.precios-naratodo.edit') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isPreciosNaraTodo ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isPreciosNaraTodo ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    PreciosOptica
                </a>

                @php($isPromoGafas = request()->routeIs('dashboard'))
                <a href="{{ route('dashboard') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isPromoGafas ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isPromoGafas ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    Promo gafas
                </a>

                @php($isFormulaImages = request()->routeIs('dashboard.formula-images') || request()->routeIs('dashboard.formula-images.*'))
                <a href="{{ route('dashboard.formula-images') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isFormulaImages ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isFormulaImages ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    Imagenes de formulas
                </a>

                @php($isGafasMujer = request()->routeIs('admin.gafas-mujeres.*'))
                <a href="{{ route('admin.gafas-mujeres.index') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isGafasMujer ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isGafasMujer ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    Gafas mujer
                </a>

                @php($isGafasDescanso = request()->routeIs('admin.gafas-descanso.*'))
                <a href="{{ route('admin.gafas-descanso.index') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isGafasDescanso ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isGafasDescanso ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    Gafas deportivas
                </a>

                @php($isGafasNinas = request()->routeIs('admin.gafas-ninas.*'))
                <a href="{{ route('admin.gafas-ninas.index') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isGafasNinas ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isGafasNinas ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    Gafas niñas
                </a>

                @php($isGafasNinos = request()->routeIs('admin.gafas-ninos.*'))
                <a href="{{ route('admin.gafas-ninos.index') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isGafasNinos ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isGafasNinos ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    Gafas niños
                </a>

                @php($isGafasPolarizadas = request()->routeIs('admin.gafas-polarizadas.*'))
                <a href="{{ route('admin.gafas-polarizadas.index') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isGafasPolarizadas ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isGafasPolarizadas ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    Gafas polarizadas
                </a>

                @php($isGafasHombre = request()->routeIs('admin.gafas-hombre.*'))
                <a href="{{ route('admin.gafas-hombre.index') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold {{ $isGafasHombre ? 'bg-white/10 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <span class="h-2 w-2 rounded-full {{ $isGafasHombre ? 'bg-violet-400' : 'bg-white/30' }}"></span>
                    Gafas hombre
                </a>
            @endif
        </nav>

        @auth
            @php($rolId = (int) (auth()->user()->rol_id ?? 0))
            <div class="mx-3 border-t border-white/15 pt-4 pb-6 text-sm">
                @if($rolId === 2)
                    @if($isDashboardArea)
                        <a href="{{ route('configuracion.index') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold text-white/80 hover:bg-white/10 hover:text-white">
                            <span class="h-2 w-2 rounded-full bg-white/30"></span>
                            Ir a Configuración
                        </a>
                    @elseif($isConfiguracionArea)
                        <a href="{{ route('dashboard') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold text-white/80 hover:bg-white/10 hover:text-white">
                            <span class="h-2 w-2 rounded-full bg-white/30"></span>
                            Ir a Dashboard
                        </a>
                    @endif
                @elseif(in_array($rolId, [3, 4], true))
                    @if(!$isDashboardArea)
                        <a href="{{ route('dashboard') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold text-white/80 hover:bg-white/10 hover:text-white">
                            <span class="h-2 w-2 rounded-full bg-white/30"></span>
                            Ir a Dashboard
                        </a>
                    @endif

                    @if(!$isAdminUsersArea)
                        <a href="{{ route('admin') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold text-white/80 hover:bg-white/10 hover:text-white">
                            <span class="h-2 w-2 rounded-full bg-white/30"></span>
                            Ir a Admin
                        </a>
                    @endif

                    @if(!$isConfiguracionArea)
                        <a href="{{ route('configuracion.index') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold text-white/80 hover:bg-white/10 hover:text-white">
                            <span class="h-2 w-2 rounded-full bg-white/30"></span>
                            Ir a Configuración
                        </a>
                    @endif
                @endif
            </div>
        @endauth

        <div class="mx-3 border-t border-white/15 pt-4 pb-6 text-sm">
            <a href="{{ url('/') }}" class="mt-2 flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold text-white/80 hover:bg-white/10 hover:text-white">
                <span class="h-2 w-2 rounded-full bg-white/30"></span>
                Ir al Landing
            </a>
        </div>
    </aside>

    <main class="w-full min-w-0 flex-1 bg-white">
        <header class="border-b border-zinc-200 bg-white">
            <div class="flex items-center justify-between gap-3 px-4 py-4 md:px-8">
                <div class="flex items-center gap-2">
                    @if($useMobileDrawer)
                        <button id="dashboardSidebarToggle" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-zinc-200 text-zinc-700 hover:bg-zinc-50 md:hidden" aria-label="Abrir menú" aria-expanded="false" aria-controls="dashboardSidebar">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 6h16" />
                                <path d="M4 12h16" />
                                <path d="M4 18h16" />
                            </svg>
                        </button>
                    @endif

                    <div>
                    <h1 class="text-base font-semibold text-zinc-900">@yield('heading', 'Dashboard')</h1>
                    <p class="mt-0.5 text-sm text-zinc-500">Panel principal</p>
                    </div>
                </div>

                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-900">
                                Cerrar sesión
                            </button>
                        </form>
                    @else
                        <a href="{{ $loginUrl }}" class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-900">
                            Volver al login
                        </a>
                    @endauth
            </div>
        </header>

        <div class="px-4 py-8 md:px-8">
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

@if($useMobileDrawer)
    <script>
        (function () {
            var sidebar = document.getElementById('dashboardSidebar');
            var toggle = document.getElementById('dashboardSidebarToggle');
            var backdrop = document.getElementById('dashboardSidebarBackdrop');

            if (!sidebar || !toggle || !backdrop) {
                return;
            }

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
                toggle.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }

            toggle.addEventListener('click', function () {
                if (toggle.getAttribute('aria-expanded') === 'true') {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            backdrop.addEventListener('click', closeSidebar);

            Array.prototype.forEach.call(sidebar.querySelectorAll('a[href]'), function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 768) {
                        closeSidebar();
                    }
                });
            });

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 768) {
                    backdrop.classList.add('hidden');
                    toggle.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                }
            });
        })();
    </script>
@endif
<script>
    (function () {
        var forms = document.querySelectorAll('form[data-single-submit]');

        Array.prototype.forEach.call(forms, function (form) {
            var submitButton = form.querySelector('[data-submit-button]');

            if (!submitButton) {
                return;
            }

            form.addEventListener('submit', function () {
                if (form.dataset.isSubmitting === 'true') {
                    return false;
                }

                form.dataset.isSubmitting = 'true';
                submitButton.disabled = true;
                submitButton.setAttribute('aria-disabled', 'true');
                submitButton.textContent = 'Guardando...';
            });
        });
    })();
</script>
</body>
</html>
