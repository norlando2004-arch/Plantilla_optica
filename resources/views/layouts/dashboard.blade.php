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
<body class="bg-zinc-100 text-zinc-900 antialiased font-sans">
<header class="border-b border-zinc-200 bg-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4">
        <div class="flex items-center gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-white">
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 14c0-2.2 1.8-4 4-4h2c2.2 0 4 1.8 4 4v1c0 2.2-1.8 4-4 4H8c-2.2 0-4-1.8-4-4v-1Z"/>
                    <path d="M14 14c0-2.2 1.8-4 4-4h2"/>
                    <path d="M10 14h4"/>
                </svg>
            </span>
            <div>
                <p class="text-sm font-semibold">Óptica</p>
                <p class="mt-0.5 text-xs text-zinc-500">Dashboard</p>
            </div>
        </div>

        <nav class="flex flex-wrap items-center gap-2 text-sm">
            <a href="{{ route('configuracion.index') }}" class="rounded-xl px-3 py-2 font-semibold text-zinc-700 hover:bg-zinc-50">Configuración</a>
            <a href="{{ route('configuracion.hero-banners.index') }}" class="rounded-xl px-3 py-2 font-semibold text-zinc-700 hover:bg-zinc-50">Hero banners</a>
            <a href="{{ route('configuracion.landing-intro.edit') }}" class="rounded-xl px-3 py-2 font-semibold text-zinc-700 hover:bg-zinc-50">Contenido landing</a>
            <a href="{{ url('/') }}" class="rounded-xl px-3 py-2 font-semibold text-zinc-700 hover:bg-zinc-50">Ver landing</a>
        </nav>
    </div>
</header>

<main class="mx-auto max-w-7xl px-4 py-8">
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
</body>
</html>
