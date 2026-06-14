@php($banner = $banner ?? null)
@php($placeholderImg = asset('images/placeholder.svg'))
@php($imgUrl = $banner ? $banner->bannerImageUrl('secondary-banners.image', 'secondary_banner', $placeholderImg) : $placeholderImg)
@php($isEdit = !is_null($banner))

<section class="rounded-3xl border border-zinc-200 bg-white p-6">
    <label class="text-sm font-semibold">{{ $isEdit ? 'Imagen del banner secundario' : 'Imágenes de banners secundarios' }}</label>
    <input
        id="secondaryBannerImageFile"
        name="{{ $isEdit ? 'imagen' : 'imagenes[]' }}"
        type="file"
        accept="image/*"
        {{ $isEdit ? '' : 'multiple' }}
        {{ $isEdit ? 'required' : 'required' }}
        class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800"
    >
    <p class="mt-2 text-xs text-zinc-500">{{ $isEdit ? 'Sube una imagen para reemplazar este banner (máximo 50 MB).' : 'Puedes arrastrar o seleccionar varias imágenes (máximo 50 MB por archivo). Se crea un banner por cada archivo.' }}</p>

    @if(!$isEdit)
        <p id="secondaryBannerFilesCount" class="mt-1 text-xs text-zinc-500"></p>
    @endif

    <div class="mt-5 overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100">
        <img id="secondaryBannerImagePreview" src="{{ $imgUrl }}" alt="Vista previa banner secundario" class="h-auto w-full object-cover" loading="lazy">
    </div>

    @if($isEdit)
        <div class="mt-6 border-t border-zinc-200 pt-5">
            <label class="text-sm font-semibold">Agregar más banners (múltiple)</label>
            <input
                id="secondaryBannerExtraFiles"
                name="imagenes_extra[]"
                type="file"
                accept="image/*"
                multiple
                class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800"
            >
            <p class="mt-2 text-xs text-zinc-500">Desde aquí puedes subir varias imágenes (máximo 50 MB por archivo) y se crearán nuevos banners automáticamente.</p>
            <p id="secondaryBannerExtraCount" class="mt-1 text-xs text-zinc-500"></p>
        </div>
    @endif
</section>

<script>
    (() => {
        const fileInput = document.getElementById('secondaryBannerImageFile');
        const preview = document.getElementById('secondaryBannerImagePreview');
        const filesCount = document.getElementById('secondaryBannerFilesCount');
        const extraInput = document.getElementById('secondaryBannerExtraFiles');
        const extraCount = document.getElementById('secondaryBannerExtraCount');
        if (!fileInput || !preview) return;

        const fallback = @json($placeholderImg);
        let objectUrl = null;

        const clearObjectUrl = () => {
            if (!objectUrl) return;
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        };

        fileInput.addEventListener('change', () => {
            const hasFiles = fileInput.files && fileInput.files.length > 0;
            const file = hasFiles ? fileInput.files[0] : null;
            clearObjectUrl();

            if (filesCount) {
                const total = hasFiles ? fileInput.files.length : 0;
                filesCount.textContent = total > 0 ? `${total} imagen(es) seleccionada(s)` : '';
            }

            if (!file) {
                preview.src = fallback;
                return;
            }

            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
        });

        if (extraInput && extraCount) {
            extraInput.addEventListener('change', () => {
                const total = extraInput.files ? extraInput.files.length : 0;
                extraCount.textContent = total > 0 ? `${total} imagen(es) extra seleccionada(s)` : '';
            });
        }

        preview.addEventListener('error', () => {
            preview.src = fallback;
        });

        window.addEventListener('beforeunload', clearObjectUrl);
    })();
</script>
