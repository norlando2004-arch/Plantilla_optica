@extends('admin.dashboard_layout')

@section('title', 'Fotos de categorías')

@section('content')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&display=swap" rel="stylesheet">

    <style>
        .category-preview-label {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0.5rem;
            text-align: center;
            font-family: 'Baloo 2', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: clamp(2rem, 3vw, 3rem);
            font-weight: 800;
            letter-spacing: 0.02em;
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.18);
            pointer-events: none;
        }
    </style>

    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Fotos de categorías</h1>
            <p class="mt-1 text-sm text-zinc-600">Cambia las imágenes de Niños, Mujeres y Hombres del bloque de categorías.</p>
        </div>
        <a href="{{ route('configuracion.index') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Volver</a>
    </div>

    <form class="mt-6" method="POST" action="{{ route('configuracion.landing-category-photos.update') }}" enctype="multipart/form-data">
        @csrf
        @php($previewCardHeight = 400)

        <section class="rounded-3xl border border-zinc-200 bg-white p-6">
            <div class="space-y-8">
                <div>
                    <label class="text-sm font-semibold">Imagen NIÑOS</label>
                    <input id="ninosImageFile" name="ninos_image_file" type="file" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">
                    <p class="mt-2 text-xs text-zinc-500">Actual: {{ $landingCategoryPhotos['ninos_uploaded_name'] ?? 'imagen por defecto' }}</p>
                    @if(!empty($landingCategoryPhotos['has_uploaded_ninos']))
                        <button type="submit" name="remove_ninos_image" value="1" class="mt-2 rounded-xl bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">Eliminar imagen subida (volver a images/Niños.png)</button>
                    @endif
                    <div class="relative mt-3 w-full max-w-[360px] overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100" style="min-height: {{ $previewCardHeight }}px;">
                        <img id="ninosImagePreview" src="{{ $landingCategoryPhotos['ninos_image'] ?? '/images/Niños.png' }}" alt="Vista previa niños" class="h-full w-full object-cover" loading="lazy" style="min-height: {{ $previewCardHeight }}px;">
                        <div class="category-preview-label">NIÑOS</div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold">Imagen MUJERES</label>
                    <input id="mujeresImageFile" name="mujeres_image_file" type="file" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">
                    <p class="mt-2 text-xs text-zinc-500">Actual: {{ $landingCategoryPhotos['mujeres_uploaded_name'] ?? 'imagen por defecto' }}</p>
                    @if(!empty($landingCategoryPhotos['has_uploaded_mujeres']))
                        <button type="submit" name="remove_mujeres_image" value="1" class="mt-2 rounded-xl bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">Eliminar imagen subida (volver a images/Mujer.png)</button>
                    @endif
                    <div class="relative mt-3 w-full max-w-[360px] overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100" style="min-height: {{ $previewCardHeight }}px;">
                        <img id="mujeresImagePreview" src="{{ $landingCategoryPhotos['mujeres_image'] ?? '/images/Mujer.png' }}" alt="Vista previa mujeres" class="h-full w-full object-cover" loading="lazy" style="min-height: {{ $previewCardHeight }}px;">
                        <div class="category-preview-label">MUJERES</div>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold">Imagen HOMBRES</label>
                    <input id="hombresImageFile" name="hombres_image_file" type="file" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">
                    <p class="mt-2 text-xs text-zinc-500">Actual: {{ $landingCategoryPhotos['hombres_uploaded_name'] ?? 'imagen por defecto' }}</p>
                    @if(!empty($landingCategoryPhotos['has_uploaded_hombres']))
                        <button type="submit" name="remove_hombres_image" value="1" class="mt-2 rounded-xl bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">Eliminar imagen subida (volver a images/Hombre.png)</button>
                    @endif
                    <div class="relative mt-3 w-full max-w-[360px] overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100" style="min-height: {{ $previewCardHeight }}px;">
                        <img id="hombresImagePreview" src="{{ $landingCategoryPhotos['hombres_image'] ?? '/images/Hombre.png' }}" alt="Vista previa hombres" class="h-full w-full object-cover" loading="lazy" style="min-height: {{ $previewCardHeight }}px;">
                        <div class="category-preview-label">HOMBRES</div>
                    </div>
                </div>
            </div>

            <p class="mt-4 text-xs text-zinc-500">Sube las imágenes arrastrando o seleccionando archivo, igual que en banners.</p>
        </section>

        <div class="mt-6 flex justify-end gap-2">
            <button type="submit" class="rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">Guardar cambios</button>
        </div>
    </form>

    <script>
        (() => {
            const bindPreview = (inputId, imgId) => {
                const input = document.getElementById(inputId);
                const img = document.getElementById(imgId);
                if (!input || !img) return;

                let objectUrl = null;
                const clear = () => {
                    if (!objectUrl) return;
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                };

                input.addEventListener('change', () => {
                    const file = input.files && input.files[0] ? input.files[0] : null;
                    clear();
                    if (!file) return;
                    objectUrl = URL.createObjectURL(file);
                    img.src = objectUrl;
                });

                window.addEventListener('beforeunload', clear);
            };

            bindPreview('ninosImageFile', 'ninosImagePreview');
            bindPreview('mujeresImageFile', 'mujeresImagePreview');
            bindPreview('hombresImageFile', 'hombresImagePreview');
        })();
    </script>
@endsection
