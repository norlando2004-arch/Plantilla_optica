@extends('admin.dashboard_layout')

@section('title', 'Contenido landing')

@section('content')
    @php($landingIntro = $landingIntro ?? [])

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Contenido landing</h1>
            <p class="mt-1 text-sm text-zinc-600">Izquierda: lo editable. Derecha: vista previa en vivo.</p>
        </div>
        <a href="{{ url('/') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver landing</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5">
            <form id="landingIntroForm" method="POST" action="{{ route('configuracion.landing-intro.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid gap-4">
                        <div>
                            <label class="text-sm font-semibold">Badge</label>
                            <input name="badge" value="{{ old('badge', $landingIntro['badge'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <label class="text-sm font-semibold">Título (inicio)</label>
                                <input name="title_prefix" value="{{ old('title_prefix', $landingIntro['title_prefix'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                            </div>
                            <div>
                                <label class="text-sm font-semibold">Título (resaltado)</label>
                                <input name="title_highlight" value="{{ old('title_highlight', $landingIntro['title_highlight'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                            </div>
                            <div>
                                <label class="text-sm font-semibold">Título (final)</label>
                                <input name="title_suffix" value="{{ old('title_suffix', $landingIntro['title_suffix'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Descripción</label>
                            <textarea name="description" rows="3" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">{{ old('description', $landingIntro['description'] ?? '') }}</textarea>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-semibold">Botón 1</label>
                                <input name="cta_primary_text" value="{{ old('cta_primary_text', $landingIntro['cta_primary_text'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                            </div>
                            <div>
                                <label class="text-sm font-semibold">Botón 2</label>
                                <input name="cta_secondary_text" value="{{ old('cta_secondary_text', $landingIntro['cta_secondary_text'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                            </div>
                        </div>

                        @php($stats = $landingIntro['stats'] ?? [])
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                            <p class="text-sm font-semibold">Mini métricas (3)</p>
                            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                                @for($i = 0; $i < 3; $i++)
                                    <div>
                                        <label class="text-xs font-semibold text-zinc-700">{{ $i + 1 }}. Label</label>
                                        <input name="stats_{{ $i }}_label" value="{{ old('stats_'.$i.'_label', $stats[$i]['label'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm">
                                        <label class="mt-3 block text-xs font-semibold text-zinc-700">{{ $i + 1 }}. Valor</label>
                                        <input name="stats_{{ $i }}_value" value="{{ old('stats_'.$i.'_value', $stats[$i]['value'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm">
                                    </div>
                                @endfor
                            </div>
                        </div>

                        @php($cards = $landingIntro['cards'] ?? [])
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                            <p class="text-sm font-semibold">Cards (2)</p>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                @for($i = 0; $i < 2; $i++)
                                    <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                        <label class="text-xs font-semibold text-zinc-700">Eyebrow</label>
                                        <input name="card_{{ $i }}_eyebrow" value="{{ old('card_'.$i.'_eyebrow', $cards[$i]['eyebrow'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                                        <label class="mt-3 block text-xs font-semibold text-zinc-700">Título</label>
                                        <input name="card_{{ $i }}_title" value="{{ old('card_'.$i.'_title', $cards[$i]['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                                        <label class="mt-3 block text-xs font-semibold text-zinc-700">Descripción</label>
                                        <input name="card_{{ $i }}_desc" value="{{ old('card_'.$i.'_desc', $cards[$i]['desc'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Alt de la imagen</label>
                            <input name="image_alt" value="{{ old('image_alt', $landingIntro['image_alt'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Subir imágenes o GIF (múltiples)</label>
                            <input id="landingIntroImageFiles" name="uploaded_images[]" type="file" multiple accept="image/*,.gif" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">
                            <p class="mt-2 text-xs text-zinc-500">Puedes subir varias imágenes y GIF. Se guardan en la base de datos y la landing las rota automáticamente. Máximo 6 archivos por guardado, 50 MB cada uno.</p>
                            <p class="mt-2 text-xs text-zinc-500">Imágenes persistentes actuales: <span class="font-semibold">{{ (int) ($landingIntro['uploaded_images_count'] ?? 0) }}</span></p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="flex items-center gap-2 rounded-2xl border border-zinc-200 px-4 py-3 text-sm text-zinc-700">
                                <input id="landingIntroReplaceUploads" type="checkbox" name="replace_uploaded_images" value="1" class="h-4 w-4 rounded border-zinc-300">
                                <span>Reemplazar imágenes subidas actuales</span>
                            </label>
                            <label class="flex items-center gap-2 rounded-2xl border border-zinc-200 px-4 py-3 text-sm text-zinc-700">
                                <input id="landingIntroClearUploads" type="checkbox" name="clear_uploaded_images" value="1" class="h-4 w-4 rounded border-zinc-300">
                                <span>Quitar imágenes subidas actuales</span>
                            </label>
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Imágenes externas (URLs opcionales)</label>
                            <textarea id="landingIntroImageUrls" name="image_urls" rows="8" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 font-mono text-xs">{{ old('image_urls', implode("\n", $landingIntro['manual_image_urls'] ?? [])) }}</textarea>
                            <p class="mt-2 text-xs text-zinc-500">Puedes mezclar URLs externas con las subidas. La landing rota primero las imágenes subidas y luego estas URLs.</p>
                        </div>

                        @php($mini = $landingIntro['mini_cards'] ?? [])
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                            <p class="text-sm font-semibold">Mini cards (2)</p>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                @for($i = 0; $i < 2; $i++)
                                    <div>
                                        <label class="text-xs font-semibold text-zinc-700">Eyebrow</label>
                                        <input name="mini_{{ $i }}_eyebrow" value="{{ old('mini_'.$i.'_eyebrow', $mini[$i]['eyebrow'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm">
                                        <label class="mt-3 block text-xs font-semibold text-zinc-700">Título</label>
                                        <input name="mini_{{ $i }}_title" value="{{ old('mini_'.$i.'_title', $mini[$i]['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm">
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">Guardar</button>
                        </div>
                </div>
            </form>

            <p class="mt-4 text-xs text-zinc-500">Tip: escribe y mira la vista previa a la derecha sin recargar.</p>
        </section>

        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-7 lg:sticky lg:top-8">
            @php($stats = $landingIntro['stats'] ?? [])
            @php($cards = $landingIntro['cards'] ?? [])
            @php($mini = $landingIntro['mini_cards'] ?? [])
            @php($urls = $landingIntro['image_urls'] ?? [])
            @php($firstUrl = is_array($urls) ? (collect($urls)->map(fn($u)=>trim((string)$u))->first(fn($u)=>$u!=='')) : null)

            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold">Vista previa</h2>
                <span class="text-xs text-zinc-500">Así se verá en el landing</span>
            </div>

            <div class="mt-4 overflow-hidden rounded-3xl border border-zinc-200 bg-gradient-to-br from-white via-white to-zinc-50 p-6">
                <p id="previewBadge" class="inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200">
                    {{ $landingIntro['badge'] ?? '' }}
                </p>

                <h2 class="mt-4 text-3xl font-semibold tracking-tight">
                    <span id="previewTitlePrefix">{{ $landingIntro['title_prefix'] ?? '' }}</span>
                    <span class="bg-gradient-to-r from-violet-700 via-fuchsia-700 to-rose-700 bg-clip-text text-transparent" id="previewTitleHighlight">{{ $landingIntro['title_highlight'] ?? '' }}</span>
                    <span id="previewTitleSuffix">{{ $landingIntro['title_suffix'] ?? '' }}</span>
                </h2>

                <p id="previewDesc" class="mt-3 max-w-2xl text-sm leading-6 text-zinc-600">{{ $landingIntro['description'] ?? '' }}</p>

                <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                    <span id="previewCta1" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-violet-600 to-fuchsia-600 px-5 py-3 text-sm font-semibold text-white">{{ $landingIntro['cta_primary_text'] ?? '' }}</span>
                    <span id="previewCta2" class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200">{{ $landingIntro['cta_secondary_text'] ?? '' }}</span>
                </div>

                <dl class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @for($i = 0; $i < 3; $i++)
                        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                            <dt id="previewStat{{ $i }}Label" class="text-xs font-semibold text-zinc-500">{{ $stats[$i]['label'] ?? '' }}</dt>
                            <dd id="previewStat{{ $i }}Value" class="mt-1 text-sm font-semibold">{{ $stats[$i]['value'] ?? '' }}</dd>
                        </div>
                    @endfor
                </dl>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    @for($i = 0; $i < 2; $i++)
                        <div class="rounded-3xl border border-zinc-200 bg-white p-5">
                            <p id="previewCard{{ $i }}Eyebrow" class="text-xs font-semibold text-zinc-500">{{ $cards[$i]['eyebrow'] ?? '' }}</p>
                            <p id="previewCard{{ $i }}Title" class="mt-2 text-sm font-semibold">{{ $cards[$i]['title'] ?? '' }}</p>
                            <p id="previewCard{{ $i }}Desc" class="mt-1 text-sm text-zinc-600">{{ $cards[$i]['desc'] ?? '' }}</p>
                        </div>
                    @endfor
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-5">
                    <div class="lg:col-span-3">
                        <img
                            id="previewImage"
                            class="w-full rounded-[2rem] border border-zinc-200 bg-white"
                            src="{{ $firstUrl ?: asset('images/placeholder.svg') }}"
                            data-fallback="{{ asset('images/placeholder.svg') }}"
                            alt="{{ $landingIntro['image_alt'] ?? '' }}"
                            loading="lazy"
                        >

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            @for($i = 0; $i < 2; $i++)
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                    <p id="previewMini{{ $i }}Eyebrow" class="text-xs font-semibold text-zinc-500">{{ $mini[$i]['eyebrow'] ?? '' }}</p>
                                    <p id="previewMini{{ $i }}Title" class="mt-1 text-sm font-semibold">{{ $mini[$i]['title'] ?? '' }}</p>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div class="rounded-3xl border border-zinc-200 bg-white p-5">
                            <p class="text-sm font-semibold">Imagen</p>
                            <p class="mt-2 text-xs text-zinc-500">Se toma la primera URL no vacía.</p>
                            <p class="mt-3 text-xs font-semibold text-zinc-700">Alt</p>
                            <p id="previewAlt" class="mt-1 text-sm text-zinc-700">{{ $landingIntro['image_alt'] ?? '' }}</p>
                        </div>

                        <div class="mt-3 rounded-3xl border border-zinc-200 bg-white p-5">
                            <p class="text-sm font-semibold">Estado</p>
                            <p id="previewStatus" class="mt-2 text-sm text-zinc-600">Listo para guardar.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        (function () {
            const form = document.getElementById('landingIntroForm');
            if (!form) return;

            const imageFilesInput = document.getElementById('landingIntroImageFiles');
            const replaceUploadsInput = document.getElementById('landingIntroReplaceUploads');
            const clearUploadsInput = document.getElementById('landingIntroClearUploads');
            const imageUrlsTextarea = document.getElementById('landingIntroImageUrls');
            let objectUrl = null;

            const get = (name) => {
                const el = form.querySelector(`[name="${name}"]`);
                return el ? String(el.value || '') : '';
            };

            const setText = (id, value) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.textContent = value;
            };

            const revokeObjectUrl = () => {
                if (!objectUrl) return;
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            };

            const setImageFromUrls = () => {
                const img = document.getElementById('previewImage');
                if (!img) return;

                const raw = imageUrlsTextarea ? String(imageUrlsTextarea.value || '') : get('image_urls');
                const urls = raw
                    .split(/\r\n|\r|\n/)
                    .map((s) => s.trim())
                    .filter((s) => s.length > 0);

                const fallback = img.getAttribute('data-fallback') || img.getAttribute('src') || '';
                const initial = @json($firstUrl ?: asset('images/placeholder.svg'));
                const hasNewFiles = imageFilesInput && imageFilesInput.files && imageFilesInput.files.length > 0;
                const shouldIgnoreInitial = (replaceUploadsInput && replaceUploadsInput.checked) || (clearUploadsInput && clearUploadsInput.checked);

                let first = urls[0] || '';

                if (hasNewFiles) {
                    revokeObjectUrl();
                    objectUrl = URL.createObjectURL(imageFilesInput.files[0]);
                    first = objectUrl;
                } else if (first === '' && !shouldIgnoreInitial) {
                    first = initial || fallback;
                }

                if (first === '') {
                    first = fallback;
                }

                if (first && img.getAttribute('src') !== first) img.setAttribute('src', first);
            };

            const render = () => {
                setText('previewBadge', get('badge'));
                setText('previewTitlePrefix', get('title_prefix'));
                setText('previewTitleHighlight', get('title_highlight'));
                setText('previewTitleSuffix', get('title_suffix'));
                setText('previewDesc', get('description'));
                setText('previewCta1', get('cta_primary_text'));
                setText('previewCta2', get('cta_secondary_text'));

                for (let i = 0; i < 3; i++) {
                    setText(`previewStat${i}Label`, get(`stats_${i}_label`));
                    setText(`previewStat${i}Value`, get(`stats_${i}_value`));
                }

                for (let i = 0; i < 2; i++) {
                    setText(`previewCard${i}Eyebrow`, get(`card_${i}_eyebrow`));
                    setText(`previewCard${i}Title`, get(`card_${i}_title`));
                    setText(`previewCard${i}Desc`, get(`card_${i}_desc`));
                    setText(`previewMini${i}Eyebrow`, get(`mini_${i}_eyebrow`));
                    setText(`previewMini${i}Title`, get(`mini_${i}_title`));
                }

                const alt = get('image_alt');
                const img = document.getElementById('previewImage');
                if (img) img.setAttribute('alt', alt);
                setText('previewAlt', alt);
                setImageFromUrls();
                setText('previewStatus', 'Vista previa actualizada.');
            };

            form.addEventListener('input', render);
            form.addEventListener('change', render);
            window.addEventListener('beforeunload', revokeObjectUrl);
            render();
        })();
    </script>
@endsection
