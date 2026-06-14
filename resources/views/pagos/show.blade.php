<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago {{ $pago->referencia }} — Óptica</title>

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
<body class="bg-zinc-50 text-zinc-900 antialiased font-sans">
@include('partials.store-navbar')

<main class="mx-auto max-w-3xl px-4 py-6 sm:py-10">
    @php($metaPago = is_array($pago->meta) ? $pago->meta : [])
    @if($pago->estado === 'rechazado' && is_array($metaPago['stock_conflict'] ?? null))
        <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            {{ (string) ($metaPago['stock_conflict']['message'] ?? 'Lo sentimos, esta gafa se acaba de ocupar en otra compra y ya no tiene stock disponible.') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold">Óptica</p>
            <h1 class="mt-0.5 text-base font-semibold">Estado del pago</h1>
        </div>
        <a href="{{ url('/') }}" class="rounded-2xl px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">Volver a la landing</a>
    </div>
    <div class="rounded-3xl border border-zinc-200 bg-white p-4 sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Referencia</p>
                <p class="mt-1 text-sm font-semibold text-zinc-900">{{ $pago->referencia }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Estado</p>
                @php($badge = match($pago->estado) {
                    'aprobado' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'label' => 'Aprobado'],
                    'rechazado' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'label' => 'Rechazado'],
                    default => ['bg' => 'bg-zinc-100', 'text' => 'text-zinc-700', 'label' => ucfirst($pago->estado)],
                })
                <span class="mt-1 inline-flex rounded-full {{ $badge['bg'] }} px-3 py-1 text-xs font-semibold {{ $badge['text'] }}">{{ $badge['label'] }}</span>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Total</p>
            <p class="mt-1 text-xl font-semibold text-zinc-900 sm:text-2xl">{{ number_format((float)$pago->monto, 0, ',', '.') }} {{ $pago->moneda }}</p>
        </div>

        @php($items = $pago->carrito && $pago->carrito->items ? $pago->carrito->items : collect())
        @if($items->isNotEmpty())
            <div class="mt-6">
                <p class="text-sm font-semibold text-zinc-900">Artículos</p>
                <div class="mt-2 grid gap-2">
                    @foreach($items as $item)
                        @php($linea = ((float) $item->precio_unitario) * ((int) $item->cantidad))
                        <div class="flex items-start justify-between gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 px-3 py-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-zinc-900">{{ $item->nombre_producto }}</p>
                                <p class="mt-0.5 text-xs text-zinc-600">x{{ (int) $item->cantidad }}</p>
                            </div>
                            <p class="shrink-0 text-sm font-semibold text-zinc-900">{{ number_format((float)$linea, 0, ',', '.') }} {{ $item->moneda }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($pago->estado === 'pendiente')
            <div class="mt-8 grid gap-2 sm:flex sm:flex-wrap">
                @if($pago->pasarela === 'dummy')
                    <a href="{{ route('pagos.dummy', $pago) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-900 sm:w-auto">
                        Pagar ahora
                    </a>
                @else
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-700">
                        Pasarela "{{ $pago->pasarela }}" aún no configurada.
                    </div>
                @endif
            </div>
        @endif
    </div>
</main>
</body>
</html>
