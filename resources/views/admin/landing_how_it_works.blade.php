@extends('admin.dashboard_layout')

@section('title', 'Cómo funciona (Landing)')

@section('content')
    @php($landingHowItWorks = $landingHowItWorks ?? [])
    @php($steps = is_array($landingHowItWorks['steps'] ?? null) ? $landingHowItWorks['steps'] : [])
    @php($img = asset('images/placeholder.svg'))
    @php($manualImgSrc = trim((string)($landingHowItWorks['manual_image_url'] ?? ($landingHowItWorks['image_url'] ?? ''))))
    @php($imgSrc = trim((string)($landingHowItWorks['image_url'] ?? '')))
    @php($imgSrc = $imgSrc !== '' ? $imgSrc : $img)

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Cómo funciona (Landing)</h1>
            <p class="mt-1 text-sm text-zinc-600">Edita el flujo (4 pasos), botones y métricas. Derecha: vista previa en vivo igual al landing.</p>
        </div>
        <a href="{{ url('/') }}#como-funciona" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver sección</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5">
            <form id="landingHowItWorksForm" method="POST" action="{{ route('configuracion.landing-how-it-works.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid gap-4">
                    <div>
                        <label class="text-sm font-semibold">Título</label>
                        <input name="title" value="{{ old('title', $landingHowItWorks['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Subtítulo</label>
                        <input name="subtitle" value="{{ old('subtitle', $landingHowItWorks['subtitle'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Pasos (4)</p>
                        <div class="mt-4 grid gap-4">
                            @for($i = 0; $i < 4; $i++)
                                @php($st = is_array($steps[$i] ?? null) ? $steps[$i] : [])
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                    <p class="text-xs font-semibold text-zinc-500">Paso {{ $i + 1 }}</p>
                                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Título</label>
                                            <input name="step_{{ $i }}_title" value="{{ old('step_'.$i.'_title', $st['title'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Descripción</label>
                                            <input name="step_{{ $i }}_desc" value="{{ old('step_'.$i.'_desc', $st['desc'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold">Botón 1 (texto)</label>
                            <input name="cta_primary_text" value="{{ old('cta_primary_text', $landingHowItWorks['cta_primary_text'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-semibold">Botón 1 (URL)</label>
                            <input name="cta_primary_href" value="{{ old('cta_primary_href', $landingHowItWorks['cta_primary_href'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="#categorias o /ruta o https://...">
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold">Botón 2 (texto)</label>
                            <input name="cta_secondary_text" value="{{ old('cta_secondary_text', $landingHowItWorks['cta_secondary_text'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-semibold">Botón 2 (URL)</label>
                            <input name="cta_secondary_href" value="{{ old('cta_secondary_href', $landingHowItWorks['cta_secondary_href'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="#contacto o /ruta o https://...">
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold">Imagen subida (archivo)</label>
                            <input type="file" name="uploaded_image" accept="image/*" class="mt-2 block w-full text-sm text-zinc-700 file:mr-3 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">

                            <label class="mt-3 inline-flex items-center gap-2 text-xs text-zinc-600">
                                <input type="checkbox" name="clear_uploaded_image" value="1" @checked(old('clear_uploaded_image')) class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                                Quitar imagen subida actual
                            </label>

                            @if(!empty($landingHowItWorks['has_uploaded_image']))
                                <p class="mt-2 text-xs font-semibold text-emerald-700">Hay una imagen subida guardada en base de datos.</p>
                            @endif
                        </div>
                        <div>
                            <label class="text-sm font-semibold">Imagen manual (URL o /ruta)</label>
                            <input name="image_url" value="{{ old('image_url', $manualImgSrc) }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="https://... o /storage/...">
                            <p class="mt-2 text-xs text-zinc-500">Se usa si no hay imagen subida.</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-semibold">Alt imagen</label>
                            <input name="image_alt" value="{{ old('image_alt', $landingHowItWorks['image_alt'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Métricas (2)</p>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                <p class="text-xs font-semibold text-zinc-500">Métrica 1</p>
                                <div class="mt-3 grid gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-zinc-700">Label</label>
                                        <input name="stat_0_label" value="{{ old('stat_0_label', $landingHowItWorks['stat_0_label'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-zinc-700">Valor</label>
                                        <input name="stat_0_value" value="{{ old('stat_0_value', $landingHowItWorks['stat_0_value'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                <p class="text-xs font-semibold text-zinc-500">Métrica 2</p>
                                <div class="mt-3 grid gap-3">
                                    <div>
                                        <label class="text-xs font-semibold text-zinc-700">Label</label>
                                        <input name="stat_1_label" value="{{ old('stat_1_label', $landingHowItWorks['stat_1_label'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-zinc-700">Valor</label>
                                        <input name="stat_1_value" value="{{ old('stat_1_value', $landingHowItWorks['stat_1_value'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                    </div>
                                </div>
                            </div>
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
                <section data-animate="up" class="border-t border-zinc-200 bg-zinc-50">
                    <div class="mx-auto max-w-7xl px-4 py-12">
                        <div class="grid items-center gap-10 lg:grid-cols-2">
                            <div>
                                <h2 id="previewTitle" class="text-2xl font-semibold tracking-tight">{{ $landingHowItWorks['title'] ?? '' }}</h2>
                                <p id="previewSubtitle" class="mt-2 text-sm text-zinc-600">{{ $landingHowItWorks['subtitle'] ?? '' }}</p>

                                <ol class="mt-8 space-y-4" data-animate-stagger="80">
                                    @for($i = 0; $i < 4; $i++)
                                        @php($st = is_array($steps[$i] ?? null) ? $steps[$i] : [])
                                        <li data-animate="up" class="flex gap-4 rounded-3xl border border-zinc-200 bg-white p-5">
                                            <div class="flex h-9 w-9 flex-none items-center justify-center rounded-2xl bg-zinc-900 text-sm font-semibold text-white">
                                                {{ $i + 1 }}
                                            </div>
                                            <div>
                                                <p id="previewStep{{ $i }}Title" class="text-sm font-semibold">{{ $st['title'] ?? '' }}</p>
                                                <p id="previewStep{{ $i }}Desc" class="mt-1 text-sm text-zinc-600">{{ $st['desc'] ?? '' }}</p>
                                            </div>
                                        </li>
                                    @endfor
                                </ol>

                                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                                    <a id="previewCta1" href="{{ $landingHowItWorks['cta_primary_href'] ?? '#' }}" class="inline-flex items-center justify-center rounded-xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">{{ $landingHowItWorks['cta_primary_text'] ?? '' }}</a>
                                    <a id="previewCta2" href="{{ $landingHowItWorks['cta_secondary_href'] ?? '#' }}" class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">{{ $landingHowItWorks['cta_secondary_text'] ?? '' }}</a>
                                </div>
                            </div>

                            <div class="rounded-[2rem] border border-zinc-200 bg-white p-4">
                                <img id="previewImg" class="h-72 w-full rounded-[1.5rem] border border-zinc-200 object-cover" src="{{ $imgSrc }}" alt="{{ $landingHowItWorks['image_alt'] ?? 'Flujo de compra' }}" loading="lazy" data-fallback="{{ $img }}" data-current-uploaded-src="{{ trim((string)($landingHowItWorks['image_url'] ?? '')) }}" data-current-manual-src="{{ $manualImgSrc }}">
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-2xl bg-zinc-50 p-4">
                                        <p id="previewStat0Label" class="text-xs font-semibold text-zinc-500">{{ $landingHowItWorks['stat_0_label'] ?? '' }}</p>
                                        <p id="previewStat0Value" class="mt-1 text-sm font-semibold">{{ $landingHowItWorks['stat_0_value'] ?? '' }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-zinc-50 p-4">
                                        <p id="previewStat1Label" class="text-xs font-semibold text-zinc-500">{{ $landingHowItWorks['stat_1_label'] ?? '' }}</p>
                                        <p id="previewStat1Value" class="mt-1 text-sm font-semibold">{{ $landingHowItWorks['stat_1_value'] ?? '' }}</p>
                                    </div>
                                </div>
                            </div>
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
            const form = document.getElementById('landingHowItWorksForm');
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

            const setImg = () => {
                const img = document.getElementById('previewImg');
                if (!img) return;
                const fallback = img.getAttribute('data-fallback') || '';
                const uploadedInput = form.querySelector('[name="uploaded_image"]');
                const uploadedSelected = uploadedInput && uploadedInput.files && uploadedInput.files[0] ? uploadedInput.files[0] : null;
                const clearUploaded = form.querySelector('[name="clear_uploaded_image"]')?.checked === true;
                const currentUploadedSrc = (img.getAttribute('data-current-uploaded-src') || '').trim();
                const manual = (get('image_url') || '').trim();

                let next = fallback;
                if (uploadedSelected) {
                    next = URL.createObjectURL(uploadedSelected);
                } else if (!clearUploaded && currentUploadedSrc) {
                    next = currentUploadedSrc;
                } else if (manual) {
                    next = manual;
                }

                if (next && img.getAttribute('src') !== next) img.setAttribute('src', next);
                img.setAttribute('alt', get('image_alt'));
            };

            const render = () => {
                setText('previewTitle', get('title'));
                setText('previewSubtitle', get('subtitle'));

                for (let i = 0; i < 4; i++) {
                    setText(`previewStep${i}Title`, get(`step_${i}_title`));
                    setText(`previewStep${i}Desc`, get(`step_${i}_desc`));
                }

                setText('previewCta1', get('cta_primary_text'));
                setHref('previewCta1', get('cta_primary_href'));

                setText('previewCta2', get('cta_secondary_text'));
                setHref('previewCta2', get('cta_secondary_href'));

                setText('previewStat0Label', get('stat_0_label'));
                setText('previewStat0Value', get('stat_0_value'));
                setText('previewStat1Label', get('stat_1_label'));
                setText('previewStat1Value', get('stat_1_value'));

                setImg();

                setText('previewStatus', 'Vista previa actualizada (aún no guardada).');
            };

            form.addEventListener('input', render);
            render();
        })();
    </script>
@endsection
