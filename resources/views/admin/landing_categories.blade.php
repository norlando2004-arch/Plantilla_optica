@extends('admin.dashboard_layout')

@section('title', 'Categorías landing')

@section('content')
    @php($landingCategories = $landingCategories ?? [])
    @php($items = is_array($landingCategories['items'] ?? null) ? $landingCategories['items'] : [])
    @php($img = asset('images/placeholder.svg'))

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Categorías landing</h1>
            <p class="mt-1 text-sm text-zinc-600">Edita el texto, la URL y la foto (por URL) de cada tarjeta. Derecha: vista previa en vivo igual al landing.</p>
        </div>
        <a href="{{ url('/') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver landing</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5">
            <form id="landingCategoriesForm" method="POST" action="{{ route('configuracion.landing-categories.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid gap-4">
                    <div>
                        <label class="text-sm font-semibold">Título</label>
                        <input name="title" value="{{ old('title', $landingCategories['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Descripción</label>
                        <input name="subtitle" value="{{ old('subtitle', $landingCategories['subtitle'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold">Link superior (texto)</label>
                            <input name="top_link_text" value="{{ old('top_link_text', $landingCategories['top_link_text'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-semibold">Link superior (URL)</label>
                            <input name="top_link_href" value="{{ old('top_link_href', $landingCategories['top_link_href'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="#destacados o https://...">
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Tarjetas (4)</p>
                        <div class="mt-4 grid gap-4">
                            @for($i = 0; $i < 4; $i++)
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                    <p class="text-xs font-semibold text-zinc-500">{{ $i + 1 }}.</p>

                                    <label class="mt-2 block text-xs font-semibold text-zinc-700">Título</label>
                                    <input name="item_{{ $i }}_title" value="{{ old('item_'.$i.'_title', $items[$i]['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">

                                    <label class="mt-3 block text-xs font-semibold text-zinc-700">Descripción</label>
                                    <input name="item_{{ $i }}_desc" value="{{ old('item_'.$i.'_desc', $items[$i]['desc'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">

                                    <label class="mt-3 block text-xs font-semibold text-zinc-700">URL (al hacer click)</label>
                                    <input name="item_{{ $i }}_href" value="{{ old('item_'.$i.'_href', $items[$i]['href'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="#categorias o /productos o https://...">

                                    <label class="mt-3 block text-xs font-semibold text-zinc-700">Subir foto o GIF</label>
                                    <input id="item_{{ $i }}_uploaded_image" name="item_{{ $i }}_uploaded_image" type="file" accept="image/*,.gif" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">

                                    <label class="mt-3 block text-xs font-semibold text-zinc-700">URL manual de la foto (opcional)</label>
                                    <input name="item_{{ $i }}_image_url" value="{{ old('item_'.$i.'_image_url', $items[$i]['manual_image_url'] ?? ($items[$i]['image_url'] ?? '')) }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="https://... o /storage/...">

                                    <label class="mt-3 flex items-center gap-2 rounded-2xl border border-zinc-200 px-3 py-2 text-xs text-zinc-700">
                                        <input id="item_{{ $i }}_clear_uploaded_image" type="checkbox" name="item_{{ $i }}_clear_uploaded_image" value="1" class="h-4 w-4 rounded border-zinc-300">
                                        <span>Quitar imagen subida actual</span>
                                    </label>

                                    <p class="mt-2 text-xs text-zinc-500">{{ !empty($items[$i]['has_uploaded_image']) ? 'Esta tarjeta tiene una imagen subida persistente.' : 'Esta tarjeta no tiene imagen subida persistente.' }}</p>

                                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-zinc-700">Encuadre horizontal</label>
                                            <input name="item_{{ $i }}_image_pos_x" type="range" min="0" max="100" step="1" value="{{ old('item_'.$i.'_image_pos_x', $items[$i]['image_pos_x'] ?? 50) }}" class="mt-2 w-full" aria-label="Encuadre horizontal">
                                            <div class="mt-1 flex justify-between text-[11px] font-semibold text-zinc-500">
                                                <span>Izquierda</span>
                                                <span>Centro</span>
                                                <span>Derecha</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-zinc-700">Encuadre vertical</label>
                                            <input name="item_{{ $i }}_image_pos_y" type="range" min="0" max="100" step="1" value="{{ old('item_'.$i.'_image_pos_y', $items[$i]['image_pos_y'] ?? 50) }}" class="mt-2 w-full" aria-label="Encuadre vertical">
                                            <div class="mt-1 flex justify-between text-[11px] font-semibold text-zinc-500">
                                                <span>Arriba</span>
                                                <span>Centro</span>
                                                <span>Abajo</span>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-zinc-700">Zoom</label>
                                            <input name="item_{{ $i }}_image_zoom" type="range" min="0.5" max="1.5" step="0.01" value="{{ old('item_'.$i.'_image_zoom', $items[$i]['image_zoom'] ?? 1) }}" class="mt-2 w-full" aria-label="Zoom">
                                            <div class="mt-1 flex justify-between text-[11px] font-semibold text-zinc-500">
                                                <span>Lejos</span>
                                                <span>Normal</span>
                                                <span>Cerca</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">Guardar</button>
                    </div>
                </div>
            </form>

            <p class="mt-4 text-xs text-zinc-500">Tip: la vista previa se actualiza al escribir (sin guardar).</p>
        </section>

        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-7 lg:sticky lg:top-8">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold">Vista previa</h2>
                <span class="text-xs text-zinc-500">Así se verá en el landing</span>
            </div>

            <div class="mt-4 overflow-hidden rounded-3xl border border-zinc-200 bg-white">
                <section data-animate="up" id="categorias" class="border-y border-zinc-200 bg-zinc-50">
                    <div class="mx-auto max-w-7xl px-4 py-12">
                        <div class="flex items-end justify-between gap-6">
                            <div>
                                <h2 id="previewTitle" class="text-2xl font-semibold tracking-tight">{{ $landingCategories['title'] ?? '' }}</h2>
                                <p id="previewSubtitle" class="mt-2 text-sm text-zinc-600">{{ $landingCategories['subtitle'] ?? '' }}</p>
                            </div>
                            <a id="previewTopLink" href="{{ $landingCategories['top_link_href'] ?? '#destacados' }}" class="hidden text-sm font-semibold text-zinc-700 hover:text-zinc-900 md:inline">{{ $landingCategories['top_link_text'] ?? '' }}</a>
                        </div>

                        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-animate-stagger="90">
                            @for($i = 0; $i < 4; $i++)
                                @php($href = $items[$i]['href'] ?? '#')
                                @php($cardImg = trim((string)($items[$i]['image_url'] ?? '')))
                                @php($cardImg = $cardImg !== '' ? $cardImg : $img)
                                @php($posX = (float)($items[$i]['image_pos_x'] ?? 50))
                                @php($posY = (float)($items[$i]['image_pos_y'] ?? 50))
                                @php($zoom = (float)($items[$i]['image_zoom'] ?? 1))
                                @php($posX = max(0, min(100, $posX)))
                                @php($posY = max(0, min(100, $posY)))
                                @php($zoom = max(0.5, min(1.5, $zoom)))
                                <a id="previewItem{{ $i }}Card" data-animate="down" href="{{ $href }}" class="group rounded-3xl border border-zinc-200 bg-white p-4 hover:bg-zinc-50">
                                    <div class="h-36 w-full overflow-hidden rounded-2xl border border-zinc-200 bg-white">
                                        <img id="previewItem{{ $i }}Img" class="h-full w-full bg-white object-cover" src="{{ $cardImg }}" alt="{{ $items[$i]['title'] ?? '' }}" loading="lazy" style="object-position: {{ (int) round($posX) }}% {{ (int) round($posY) }}%; transform-origin: {{ (int) round($posX) }}% {{ (int) round($posY) }}%; transform: scale({{ number_format($zoom, 2, '.', '') }});">
                                    </div>
                                    <div class="mt-4">
                                        <p id="previewItem{{ $i }}Title" class="text-sm font-semibold">{{ $items[$i]['title'] ?? '' }}</p>
                                        <p id="previewItem{{ $i }}Desc" class="mt-1 text-sm text-zinc-600">{{ $items[$i]['desc'] ?? '' }}</p>
                                        <p class="mt-3 text-sm font-semibold text-zinc-700 group-hover:text-zinc-900">Ver opciones →</p>
                                    </div>
                                </a>
                            @endfor
                        </div>
                    </div>
                </section>

                <div class="border-t border-zinc-200 bg-white p-6">
                    <p class="text-xs font-semibold text-zinc-600">Estado</p>
                    <p id="previewStatus" class="mt-1 text-sm text-zinc-700">Listo para guardar.</p>
                </div>
            </div>
        </section>
    </div>

    <script>
        (function () {
            const form = document.getElementById('landingCategoriesForm');
            if (!form) return;

            const get = (name) => {
                const el = form.querySelector(`[name="${name}"]`);
                return el ? String(el.value || '') : '';
            };

            const setText = (id, value) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.textContent = value;
            };

            const setHref = (id, value) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.setAttribute('href', value || '#');
            };

            const setSrc = (id, value, fallback) => {
                const el = document.getElementById(id);
                if (!el) return;
                const v = String(value || '').trim();
                el.setAttribute('src', v !== '' ? v : fallback);
            };

            const objectUrls = new Map();

            const resolveImageSource = (i, fallback) => {
                const fileInput = document.getElementById(`item_${i}_uploaded_image`);
                const clearInput = document.getElementById(`item_${i}_clear_uploaded_image`);
                const urlVal = get(`item_${i}_image_url`).trim();
                const initial = initialImages[i] || '';

                if (fileInput && fileInput.files && fileInput.files[0]) {
                    const prev = objectUrls.get(i);
                    if (prev) {
                        URL.revokeObjectURL(prev);
                    }

                    const next = URL.createObjectURL(fileInput.files[0]);
                    objectUrls.set(i, next);
                    return next;
                }

                const prev = objectUrls.get(i);
                if (prev) {
                    URL.revokeObjectURL(prev);
                    objectUrls.delete(i);
                }

                if (urlVal !== '') {
                    return urlVal;
                }

                if (clearInput && clearInput.checked) {
                    return fallback;
                }

                return initial !== '' ? initial : fallback;
            };

            const clamp = (n, min, max) => Math.max(min, Math.min(max, n));

            const setCrop = (id, posX, posY, zoom) => {
                const el = document.getElementById(id);
                if (!el) return;

                const x = clamp(Number(posX), 0, 100);
                const y = clamp(Number(posY), 0, 100);
                const z = clamp(Number(zoom), 0.5, 1.5);

                el.style.objectPosition = `${Math.round(x)}% ${Math.round(y)}%`;
                el.style.transformOrigin = `${Math.round(x)}% ${Math.round(y)}%`;
                el.style.transform = `scale(${z.toFixed(2)})`;
            };

            const placeholderImg = @json($img);
            const initialImages = @json(array_map(
                fn ($item) => trim((string) ($item['image_url'] ?? '')),
                is_array($items) ? $items : []
            ));

            const render = () => {
                setText('previewTitle', get('title'));
                setText('previewSubtitle', get('subtitle'));
                setText('previewTopLink', get('top_link_text'));
                setHref('previewTopLink', get('top_link_href'));

                for (let i = 0; i < 4; i++) {
                    setText(`previewItem${i}Title`, get(`item_${i}_title`));
                    setText(`previewItem${i}Desc`, get(`item_${i}_desc`));
                    setHref(`previewItem${i}Card`, get(`item_${i}_href`));
                    setSrc(`previewItem${i}Img`, resolveImageSource(i, placeholderImg), placeholderImg);

                    const posX = parseFloat(get(`item_${i}_image_pos_x`) || '50');
                    const posY = parseFloat(get(`item_${i}_image_pos_y`) || '50');
                    const zoom = parseFloat(get(`item_${i}_image_zoom`) || '1');
                    setCrop(`previewItem${i}Img`, posX, posY, zoom);
                }

                setText('previewStatus', 'Vista previa actualizada (aún no guardada).');
            };

            form.addEventListener('input', render);
            form.addEventListener('change', render);
            window.addEventListener('beforeunload', () => {
                objectUrls.forEach((value) => URL.revokeObjectURL(value));
                objectUrls.clear();
            });
            render();
        })();
    </script>
@endsection
