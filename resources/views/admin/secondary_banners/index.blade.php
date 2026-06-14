@extends('admin.dashboard_layout')

@section('title', 'Banner secundario')

@section('content')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Banner secundario</h1>
            <p class="mt-1 text-sm text-zinc-600">Se muestra en la sección secundaria de la landing. Puedes crear todos los que quieras.</p>
        </div>

        <a href="{{ route('configuracion.secondary-banners.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">Nuevo banner</a>
    </div>

    <form method="POST" action="{{ route('configuracion.secondary-banners.settings.update') }}" class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4">
        @csrf
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <label for="seconds_per_slide" class="text-sm font-semibold text-zinc-800">Cambiar imagen cada (segundos)</label>
                <input id="seconds_per_slide" name="seconds_per_slide" type="number" min="2" max="30" value="{{ old('seconds_per_slide', (int)($secondaryCarousel['seconds_per_slide'] ?? 5)) }}" class="mt-2 w-32 rounded-xl border border-zinc-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-800">Guardar tiempo</button>
        </div>
    </form>

    <div class="mt-6 overflow-hidden rounded-3xl border border-zinc-200 bg-white">
        <table class="min-w-full divide-y divide-zinc-200 text-sm">
            <thead class="bg-zinc-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Orden</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Estado</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Vista previa (landing)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500">Contenido</th>
                <th class="px-4 py-3"></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200">
            @forelse($banners as $banner)
                @php($imgUrl = $banner->bannerImageUrl('secondary-banners.image', 'secondary_banner', asset('images/placeholder.svg')))
                @php($posX = (int) ($banner->meta['image_pos_x'] ?? 50))
                @php($posY = (int) ($banner->meta['image_pos_y'] ?? 50))
                @php($zoom = (float) ($banner->meta['image_zoom'] ?? 1))
                @php($zoom = max(0.5, min(1, $zoom)))
                @php($t = max(0, min(1, ($zoom - 0.5) / 0.5)))
                @php($posXEff = 50 + (($posX - 50) * $t))
                @php($posYEff = 50 + (($posY - 50) * $t))
                <tr>
                    <td class="px-4 py-3 font-mono text-xs text-zinc-800">{{ $banner->orden }}</td>
                    <td class="px-4 py-3">
                        @if($banner->esta_activa)
                            <span class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">Activa</span>
                        @else
                            <span class="inline-flex rounded-full bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-700">Inactiva</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="w-64 max-w-[16rem] overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100">
                            <div class="relative h-24">
                                <img
                                    src="{{ $imgUrl }}"
                                    alt="Banner secundario"
                                    class="h-full w-full object-cover"
                                    style="object-position: {{ (int) round($posXEff) }}% {{ (int) round($posYEff) }}%;"
                                    onerror="this.onerror=null; this.src='{{ asset('images/placeholder.svg') }}';"
                                    loading="lazy"
                                >
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-zinc-500">{{ $banner->uploaded_image_original_name ?: 'Imagen de banner secundario' }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-xs font-semibold text-zinc-500">Archivo</p>
                        <p class="mt-1 font-semibold text-zinc-900">{{ $banner->uploaded_image_original_name ?: 'Sin nombre' }}</p>
                        <p class="mt-1 line-clamp-2 text-sm text-zinc-600">ID #{{ $banner->id }}</p>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('configuracion.secondary-banners.edit', $banner) }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Editar</a>
                            <form method="POST" action="{{ route('configuracion.secondary-banners.destroy', $banner) }}" onsubmit="return confirm('¿Eliminar este banner secundario?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-xl bg-rose-600 px-3 py-2 text-sm font-semibold text-white hover:bg-rose-500">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-sm text-zinc-600">
                        Aún no hay banners secundarios. La landing mostrará la imagen por defecto.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
