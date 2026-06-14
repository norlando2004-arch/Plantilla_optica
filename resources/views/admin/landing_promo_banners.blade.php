@extends('admin.dashboard_layout')

@section('title', 'Banners promo (Landing)')

@section('content')
    @php($landingPromoBanners = $landingPromoBanners ?? [])
    @php($promo = is_array($landingPromoBanners['promo'] ?? null) ? $landingPromoBanners['promo'] : [])
    @php($rec = is_array($landingPromoBanners['recommended'] ?? null) ? $landingPromoBanners['recommended'] : [])
    @php($img = asset('images/placeholder.svg'))

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Banners promo (Landing)</h1>
            <p class="mt-1 text-sm text-zinc-600">Edita textos, URLs e imágenes. Derecha: vista previa en vivo igual al landing.</p>
        </div>
        <a href="{{ url('/') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver landing</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5">
            <form id="landingPromoBannersForm" method="POST" action="{{ route('configuracion.landing-promo-banners.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid gap-4">
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Banner promo</p>
                        <div class="mt-4 grid gap-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Etiqueta</label>
                                    <input name="promo_label" value="{{ old('promo_label', $promo['label'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Botón (texto)</label>
                                    <input name="promo_cta_text" value="{{ old('promo_cta_text', $promo['cta_text'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Título</label>
                                <input name="promo_title" value="{{ old('promo_title', $promo['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Descripción</label>
                                <input name="promo_desc" value="{{ old('promo_desc', $promo['desc'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-zinc-700">URL (al hacer click)</label>
                                <input name="promo_href" value="{{ old('promo_href', $promo['href'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="#destacados o /ofertas o https://...">
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Subir imagen o GIF</label>
                                <input id="promo_uploaded_image" name="promo_uploaded_image" type="file" accept="image/*,.gif" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">

                                <label class="mt-3 block text-xs font-semibold text-zinc-700">Imagen manual (URL o /ruta)</label>
                                <input name="promo_image_url" value="{{ old('promo_image_url', $promo['manual_image_url'] ?? ($promo['image_url'] ?? '')) }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="https://... o /storage/...">

                                <label class="mt-3 flex items-center gap-2 rounded-2xl border border-zinc-200 px-3 py-2 text-xs text-zinc-700">
                                    <input id="promo_clear_uploaded_image" type="checkbox" name="promo_clear_uploaded_image" value="1" class="h-4 w-4 rounded border-zinc-300">
                                    <span>Quitar imagen subida actual</span>
                                </label>

                                <p class="mt-2 text-xs text-zinc-500">{{ !empty($promo['has_uploaded_image']) ? 'Este banner tiene imagen subida persistente.' : 'Este banner no tiene imagen subida persistente.' }}</p>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Alt imagen</label>
                                <input name="promo_image_alt" value="{{ old('promo_image_alt', $promo['image_alt'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Banner recomendado (Luz azul)</p>
                        <div class="mt-4 grid gap-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Etiqueta</label>
                                    <input name="rec_label" value="{{ old('rec_label', $rec['label'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Botón (texto)</label>
                                    <input name="rec_cta_text" value="{{ old('rec_cta_text', $rec['cta_text'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Título</label>
                                <input name="rec_title" value="{{ old('rec_title', $rec['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Descripción</label>
                                <input name="rec_desc" value="{{ old('rec_desc', $rec['desc'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-zinc-700">URL (al hacer click)</label>
                                <input name="rec_href" value="{{ old('rec_href', $rec['href'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="#servicios o /servicios o https://...">
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Subir imagen o GIF</label>
                                <input id="rec_uploaded_image" name="rec_uploaded_image" type="file" accept="image/*,.gif" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">

                                <label class="mt-3 block text-xs font-semibold text-zinc-700">Imagen manual (URL o /ruta)</label>
                                <input name="rec_image_url" value="{{ old('rec_image_url', $rec['manual_image_url'] ?? ($rec['image_url'] ?? '')) }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="https://... o /storage/...">

                                <label class="mt-3 flex items-center gap-2 rounded-2xl border border-zinc-200 px-3 py-2 text-xs text-zinc-700">
                                    <input id="rec_clear_uploaded_image" type="checkbox" name="rec_clear_uploaded_image" value="1" class="h-4 w-4 rounded border-zinc-300">
                                    <span>Quitar imagen subida actual</span>
                                </label>

                                <p class="mt-2 text-xs text-zinc-500">{{ !empty($rec['has_uploaded_image']) ? 'Este banner tiene imagen subida persistente.' : 'Este banner no tiene imagen subida persistente.' }}</p>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Alt imagen</label>
                                <input name="rec_image_alt" value="{{ old('rec_image_alt', $rec['image_alt'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                            </div>
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
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold">Vista previa</h2>
                <span class="text-xs text-zinc-500">Así se verá en el landing</span>
            </div>

            <div class="mt-4 overflow-hidden rounded-3xl border border-zinc-200 bg-white">
                <section data-animate="up" class="border-b border-zinc-200 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-10">
                        <div class="grid gap-4 lg:grid-cols-2">
                            <a id="previewPromoCard" href="{{ $promo['href'] ?? '#destacados' }}" class="group relative overflow-hidden rounded-[2rem] border border-zinc-200 bg-zinc-100">
                                <img id="previewPromoImg" src="{{ ($promo['image_url'] ?? '') ?: $img }}" alt="{{ $promo['image_alt'] ?? 'Banner promo' }}" class="h-56 w-full object-cover transition duration-500 group-hover:scale-[1.02]" loading="lazy" data-fallback="{{ $img }}">
                                <div class="absolute inset-0 bg-gradient-to-r from-zinc-900/55 via-zinc-900/25 to-transparent"></div>
                                <div class="absolute inset-0 flex items-end">
                                    <div class="w-full p-6 sm:p-8">
                                        <p id="previewPromoLabel" class="text-xs font-semibold tracking-wide text-white/90">{{ $promo['label'] ?? '' }}</p>
                                        <p id="previewPromoTitle" class="mt-2 text-2xl font-semibold tracking-tight text-white">{{ $promo['title'] ?? '' }}</p>
                                        <p id="previewPromoDesc" class="mt-2 max-w-md text-sm text-white/85">{{ $promo['desc'] ?? '' }}</p>
                                        <div class="mt-5 inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-zinc-900">
                                            <span id="previewPromoCta">{{ $promo['cta_text'] ?? '' }}</span>
                                            <span aria-hidden="true">→</span>
                                        </div>
                                    </div>
                                </div>
                            </a>

                            <a id="previewRecCard" href="{{ $rec['href'] ?? '#servicios' }}" class="group relative overflow-hidden rounded-[2rem] border border-zinc-200 bg-zinc-100">
                                <img id="previewRecImg" src="{{ ($rec['image_url'] ?? '') ?: $img }}" alt="{{ $rec['image_alt'] ?? 'Banner luz azul' }}" class="h-56 w-full object-cover transition duration-500 group-hover:scale-[1.02]" loading="lazy" data-fallback="{{ $img }}">
                                <div class="absolute inset-0 bg-gradient-to-r from-zinc-900/55 via-zinc-900/25 to-transparent"></div>
                                <div class="absolute inset-0 flex items-end">
                                    <div class="w-full p-6 sm:p-8">
                                        <p id="previewRecLabel" class="text-xs font-semibold tracking-wide text-white/90">{{ $rec['label'] ?? '' }}</p>
                                        <p id="previewRecTitle" class="mt-2 text-2xl font-semibold tracking-tight text-white">{{ $rec['title'] ?? '' }}</p>
                                        <p id="previewRecDesc" class="mt-2 max-w-md text-sm text-white/85">{{ $rec['desc'] ?? '' }}</p>
                                        <div class="mt-5 inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-zinc-900">
                                            <span id="previewRecCta">{{ $rec['cta_text'] ?? '' }}</span>
                                            <span aria-hidden="true">→</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
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
            const form = document.getElementById('landingPromoBannersForm');
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

            const setImg = (id, url, altId) => {
                const img = document.getElementById(id);
                if (!img) return;

                const fallback = img.getAttribute('data-fallback') || '';
                const next = (url || '').trim() || fallback;
                if (next && img.getAttribute('src') !== next) img.setAttribute('src', next);

                if (altId) {
                    const alt = get(altId);
                    img.setAttribute('alt', alt);
                }
            };

            let promoObjectUrl = null;
            let recObjectUrl = null;

            const resolvePromoSource = () => {
                const fileInput = document.getElementById('promo_uploaded_image');
                const clearInput = document.getElementById('promo_clear_uploaded_image');
                const manualUrl = get('promo_image_url').trim();
                const initial = initialPromoImage;

                if (fileInput && fileInput.files && fileInput.files[0]) {
                    if (promoObjectUrl) URL.revokeObjectURL(promoObjectUrl);
                    promoObjectUrl = URL.createObjectURL(fileInput.files[0]);
                    return promoObjectUrl;
                }

                if (promoObjectUrl) {
                    URL.revokeObjectURL(promoObjectUrl);
                    promoObjectUrl = null;
                }

                if (manualUrl !== '') return manualUrl;
                if (clearInput && clearInput.checked) return '';
                return initial;
            };

            const resolveRecSource = () => {
                const fileInput = document.getElementById('rec_uploaded_image');
                const clearInput = document.getElementById('rec_clear_uploaded_image');
                const manualUrl = get('rec_image_url').trim();
                const initial = initialRecImage;

                if (fileInput && fileInput.files && fileInput.files[0]) {
                    if (recObjectUrl) URL.revokeObjectURL(recObjectUrl);
                    recObjectUrl = URL.createObjectURL(fileInput.files[0]);
                    return recObjectUrl;
                }

                if (recObjectUrl) {
                    URL.revokeObjectURL(recObjectUrl);
                    recObjectUrl = null;
                }

                if (manualUrl !== '') return manualUrl;
                if (clearInput && clearInput.checked) return '';
                return initial;
            };

            const initialPromoImage = @json(trim((string) ($promo['image_url'] ?? '')));
            const initialRecImage = @json(trim((string) ($rec['image_url'] ?? '')));

            const render = () => {
                setHref('previewPromoCard', get('promo_href'));
                setText('previewPromoLabel', get('promo_label'));
                setText('previewPromoTitle', get('promo_title'));
                setText('previewPromoDesc', get('promo_desc'));
                setText('previewPromoCta', get('promo_cta_text'));
                setImg('previewPromoImg', resolvePromoSource(), 'promo_image_alt');

                setHref('previewRecCard', get('rec_href'));
                setText('previewRecLabel', get('rec_label'));
                setText('previewRecTitle', get('rec_title'));
                setText('previewRecDesc', get('rec_desc'));
                setText('previewRecCta', get('rec_cta_text'));
                setImg('previewRecImg', resolveRecSource(), 'rec_image_alt');

                setText('previewStatus', 'Vista previa actualizada (aún no guardada).');
            };

            form.addEventListener('input', render);
            form.addEventListener('change', render);
            window.addEventListener('beforeunload', () => {
                if (promoObjectUrl) URL.revokeObjectURL(promoObjectUrl);
                if (recObjectUrl) URL.revokeObjectURL(recObjectUrl);
            });
            render();
        })();
    </script>
@endsection
