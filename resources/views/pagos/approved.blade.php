<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago aprobado - Optica S.A.S</title>

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
<body class="bg-zinc-50 text-zinc-900 antialiased font-sans">
@include('partials.store-navbar')

<main class="mx-auto max-w-3xl px-4 py-8 sm:py-14">
    <section class="rounded-3xl border border-zinc-200 bg-white p-6 text-center shadow-sm sm:p-10">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M20 7L10 17L4.5 11.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

        <h1 class="mt-5 text-2xl font-extrabold tracking-tight sm:text-3xl">¡Tu pago fue aprobado!</h1>
        <p class="mt-2 text-sm text-zinc-600 sm:text-base">Te estaremos redirigiendo a la página principal en <span id="countdown">10</span> segundos.</p>
        <p class="mt-1 text-xs text-zinc-500 sm:text-sm">
            @if(isset($pago) && $pago)
                Referencia: {{ $pago->referencia }}
            @else
                Referencia no disponible
            @endif
        </p>

        <div class="mt-8">
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800">
                Regresar al landing
            </a>
        </div>
        <script>
            let seconds = 10;
            const countdown = document.getElementById('countdown');
            const interval = setInterval(() => {
                seconds--;
                countdown.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(interval);
                    window.location.href = '/';
                }
            }, 1000);
        </script>
    </section>
</main>
</body>
</html>
