@extends('layouts.dashboard_empty_sidebar')

@section('title', 'Gafas mujer')
@section('heading', 'Gafas mujer')

@section('content')
    @php
        $promoPreview = (string) ($promoImage['image_url'] ?? asset('images/borrardespues.png'));
        $promoImages = is_array($promoImage['image_urls'] ?? null) ? $promoImage['image_urls'] : [];
        $promoImageNames = is_array($promoImage['uploaded_names'] ?? null) ? $promoImage['uploaded_names'] : [];
        $promoAssets = is_array($promoImage['promo_assets'] ?? null) ? $promoImage['promo_assets'] : [];
        if ($promoAssets === [] && $promoImages !== []) {
            $promoAssets = array_map(static fn (string $url): array => ['id' => null, 'url' => $url], $promoImages);
        }
    @endphp

    <div class="mb-6 rounded-3xl border border-zinc-200 bg-white p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-zinc-900">Imagen promo de mujeres en /gafas</h3>
                <p class="mt-1 text-sm text-zinc-500">Se mostrará cuando entren a /gafas con el filtro Mujeres.</p>
            </div>
        </div>

        <form class="mt-4" action="{{ route('admin.gafas-mujeres.promo-image.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
                <div>
                    <label for="gafasMujeresPromoImage" class="text-sm font-semibold text-zinc-800">Subir nuevas imágenes</label>
                    <input id="gafasMujeresPromoImage" name="promo_image_files[]" type="file" accept="image/*" multiple class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">
                    @if($promoImageNames !== [])
                        <p class="mt-2 text-xs text-zinc-500">Actuales ({{ count($promoImageNames) }}): {{ implode(', ', $promoImageNames) }}</p>
                    @else
                        <p class="mt-2 text-xs text-zinc-500">Actual: imagen por defecto (images/borrardespues.png)</p>
                    @endif
                    <p class="mt-1 text-xs text-zinc-500">Las imágenes subidas se intercalan automáticamente en /gafas cuando el filtro Mujeres está activo.</p>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <button type="submit" class="rounded-2xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-900">Guardar imágenes promo</button>
                        @if(!empty($promoImage['has_uploaded_image']))
                            <button type="submit" name="remove_promo_image" value="1" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Quitar imágenes subidas</button>
                        @endif
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50">
                    <img src="{{ $promoPreview }}" alt="Vista previa banner mujeres" class="h-56 w-full object-cover" loading="lazy">
                </div>
            </div>

            @if($promoAssets !== [])
                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Vista previa banners mujeres</p>
                    <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach($promoAssets as $promoAsset)
                            @php($promoImageUrl = (string) ($promoAsset['url'] ?? ''))
                            <div class="relative overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50">
                                @if(!empty($promoAsset['id']))
                                    <button type="submit" name="remove_promo_image_id" value="{{ (int) $promoAsset['id'] }}" class="absolute right-1.5 top-1.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-black/75 text-sm font-bold text-white hover:bg-black" title="Quitar imagen" aria-label="Quitar imagen">×</button>
                                @endif
                                <img src="{{ $promoImageUrl }}" alt="Banner mujeres" class="h-24 w-full object-cover" loading="lazy">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900">Listado</h2>
            <p class="mt-1 text-sm text-zinc-500">Administra tus gafas para mujer y sus imágenes.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.gafas-mujeres.import.show') }}" class="inline-flex items-center justify-center rounded-2xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                ⬆ Importar Excel
            </a>
            <a href="{{ route('admin.gafas-mujeres.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-900">
                + Agregar
            </a>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-3xl border border-zinc-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                <tr>
                    <th class="px-5 py-4">Producto</th>
                    <th class="px-5 py-4">Precio</th>
                    <th class="px-5 py-4">Estado</th>
                    <th class="px-5 py-4 text-right">Acciones</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                @forelse($productos as $producto)
                    @php($meta = is_array($producto->meta) ? $producto->meta : [])
                    @php($img = (string)($meta['imagen_url'] ?? ''))
                    <tr class="bg-white">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50">
                                    @if($img)
                                        <img src="{{ $img }}" alt="{{ e($producto->nombre) }}" class="h-full w-full object-cover" />
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-zinc-900">{{ $producto->nombre }}</p>
                                    <p class="mt-0.5 truncate text-xs text-zinc-500">{{ $producto->marca ?: '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-zinc-900">{{ $producto->precio !== null ? number_format((float)$producto->precio, 0, ',', '.') : '—' }} {{ $producto->moneda }}</p>
                            @if($producto->precio_oferta)
                                <p class="mt-0.5 text-xs text-zinc-500">Oferta: {{ number_format((float)$producto->precio_oferta, 0, ',', '.') }} {{ $producto->moneda }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @php($stock = $producto->existencias)
                            @php($lowStockColors = $producto->lowStockColors())
                            @if($producto->esta_activo)
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Activo</span>
                            @else
                                <span class="inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700">Inactivo</span>
                            @endif

                            @if($stock !== null)
                                <p class="mt-1 text-xs text-zinc-600">Existencias: {{ $stock }}</p>
                            @endif

                            @if($lowStockColors !== [])
                                <div class="mt-2 space-y-1">
                                    @foreach($lowStockColors as $alert)
                                        <span class="block rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                            {{ $alert['stock'] <= 0 ? 'El color ' . $alert['color'] . ' está agotado.' : 'Te quedan pocas gafas del color ' . $alert['color'] . '.' }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.gafas-mujeres.edit', $producto) }}" class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">Editar</a>
                                <form action="{{ route('admin.gafas-mujeres.destroy', $producto) }}" method="POST" onsubmit="return confirm('¿Eliminar esta gafa?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-zinc-50">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-sm text-zinc-500">Aún no has agregado gafas para mujer.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('gafas-mujeres.index') }}" class="text-sm font-semibold text-zinc-700 hover:underline">Ver página pública: /gafas-mujeres</a>
    </div>

    @include('admin.partials.auto_refresh')
@endsection
