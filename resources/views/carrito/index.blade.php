<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carrito de compras — Óptica</title>

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
@include('partials.store-navbar', ['showStoreBanner' => false])

<main class="mx-auto max-w-5xl px-4 py-6 sm:py-10">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm font-semibold">Óptica</p>
            <h1 class="mt-0.5 text-base font-semibold">Tu carrito</h1>
        </div>
        <a href="{{ url('/') }}" class="w-full rounded-2xl px-3 py-2 text-center text-sm font-semibold text-zinc-700 hover:bg-zinc-100 sm:w-auto">Seguir comprando</a>
    </div>
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

    @php($items = $items ?? collect())
    @php($carritoMoneda = $carrito?->moneda ?? ($items->first()->moneda ?? 'COP'))

    <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">
        <section class="rounded-3xl border border-zinc-200 bg-white p-4 sm:p-5 lg:col-span-2">
            <p class="text-sm font-semibold text-zinc-900">Artículos</p>

            @if($items->isEmpty())
                <div class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-700">
                    Tu carrito está vacío.
                </div>
                <div class="mt-5">
                    <a href="{{ route('gafas.index') }}" data-disable-once class="inline-flex w-full items-center justify-center rounded-2xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-900 sm:w-auto">
                        Ver productos
                    </a>
                </div>
            @else
                <div class="mt-4 grid gap-3">
                    @foreach($items as $item)
                        @php($producto = $item->producto)
                        @php($slug = $producto?->slug ?? (is_array($item->meta) ? ($item->meta['slug'] ?? null) : null))
                        @php($link = $slug ? route('gafas.show', ['producto' => $slug]) : null)
                        @php($linea = ((float) $item->precio_unitario) * ((int) $item->cantidad))

                        <div class="rounded-3xl border border-zinc-200 bg-white p-3 sm:p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-zinc-900">
                                        @if($link)
                                            <a href="{{ $link }}" class="hover:underline">{{ $item->nombre_producto }}</a>
                                        @else
                                            {{ $item->nombre_producto }}
                                        @endif
                                    </p>
                                    <p class="mt-1 text-xs text-zinc-500">Precio unitario: {{ number_format((float)$item->precio_unitario, 0, ',', '.') }} {{ $item->moneda }}</p>
                                </div>

                                <form action="{{ route('carrito.item.remove', ['item' => $item->id]) }}" method="POST" data-disable-once-form>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" data-disable-once class="w-full rounded-2xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-zinc-50 sm:w-auto">
                                        Quitar
                                    </button>
                                </form>
                            </div>

                            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
                                <form class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-end" action="{{ route('carrito.item.update', ['item' => $item->id]) }}" method="POST" data-disable-once-form>
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label class="text-xs font-semibold text-zinc-700">Cantidad</label>
                                        <input name="cantidad" type="number" min="1" max="99" value="{{ (int) $item->cantidad }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400 sm:w-24" />
                                    </div>
                                    <button type="submit" data-disable-once class="h-[46px] w-full rounded-2xl border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-900 hover:bg-zinc-50 sm:w-auto">
                                        Actualizar
                                    </button>
                                </form>

                                <div class="text-left sm:text-right">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Subtotal</p>
                                    <p class="mt-1 text-base font-semibold text-zinc-900">{{ number_format((float)$linea, 0, ',', '.') }} {{ $item->moneda }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="rounded-3xl border border-zinc-200 bg-white p-4 sm:p-5 lg:col-span-1">
            <p class="text-sm font-semibold text-zinc-900">Resumen</p>

            <div class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                <div class="flex items-center justify-between gap-4 text-sm">
                    <span class="text-zinc-600">Subtotal</span>
                    <span class="font-semibold text-zinc-900">{{ number_format((float)($carrito?->subtotal ?? 0), 0, ',', '.') }} {{ $carritoMoneda }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between gap-4 text-sm">
                    <span class="text-zinc-600">Descuento</span>
                    <span class="font-semibold text-zinc-900">{{ number_format((float)($carrito?->total_descuento ?? 0), 0, ',', '.') }} {{ $carritoMoneda }}</span>
                </div>
                <div class="mt-4 border-t border-zinc-200 pt-4">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-zinc-900">Total</span>
                        <span class="text-xl font-semibold text-zinc-900">{{ number_format((float)($carrito?->total ?? 0), 0, ',', '.') }} {{ $carritoMoneda }}</span>
                    </div>
                </div>
            </div>

            @if(!$items->isEmpty())
                <div class="mt-6 grid gap-2">
                    <a href="{{ route('checkout.carrito') }}" data-disable-once class="inline-flex w-full items-center justify-center rounded-2xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-900">
                        Ir a pagar
                    </a>
                    <p class="text-center text-xs text-zinc-500">Te pediremos tus datos antes de pagar.</p>
                </div>
            @endif
        </aside>
    </div>
</main>

<script>
(() => {
    const disableClasses = ['opacity-60', 'cursor-not-allowed', 'pointer-events-none'];

    function markDisabled(el) {
        if (!el) return;
        el.dataset.onceDisabled = '1';
        el.classList.add(...disableClasses);
        el.setAttribute('aria-disabled', 'true');
        if (el instanceof HTMLButtonElement || el instanceof HTMLInputElement) {
            el.disabled = true;
        }
    }

    document.addEventListener('click', (e) => {
        const target = e.target instanceof Element ? e.target.closest('[data-disable-once]') : null;
        if (!target) return;

        if (target.dataset.onceDisabled === '1') {
            e.preventDefault();
            e.stopPropagation();
            return;
        }

        // Deja que el primer click navegue/submita, pero bloquea los siguientes
        markDisabled(target);
    }, true);

    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (!form.hasAttribute('data-disable-once-form')) return;

        if (form.dataset.onceSubmitted === '1') {
            e.preventDefault();
            e.stopPropagation();
            return;
        }

        form.dataset.onceSubmitted = '1';

        const controls = form.querySelectorAll('button, input[type="submit"], a[data-disable-once]');
        controls.forEach(markDisabled);
    }, true);
})();
</script>
</body>
</html>
