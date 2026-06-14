@extends('admin.dashboard_layout')

@section('title', 'Contacto (Landing)')

@section('content')
    @php($landingContact = $landingContact ?? [])
    @php($channels = is_array($landingContact['channels'] ?? null) ? $landingContact['channels'] : [])
    @php($img = asset('images/placeholder.svg'))
    @php($manualImgSrc = trim((string)($landingContact['manual_image_url'] ?? ($landingContact['image_url'] ?? ''))))

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Contacto (Landing)</h1>
            <p class="mt-1 text-sm text-zinc-600">Edita datos, cards y el formulario visual (sin backend). Derecha: vista previa en vivo igual al landing.</p>
        </div>
        <a href="{{ url('/') }}#contacto" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver sección</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5">
            <form id="landingContactForm" method="POST" action="{{ route('configuracion.landing-contact.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid gap-4">
                    <div>
                        <label class="text-sm font-semibold">Título</label>
                        <input name="title" value="{{ old('title', $landingContact['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Subtítulo</label>
                        <input name="subtitle" value="{{ old('subtitle', $landingContact['subtitle'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Datos de contacto</p>
                        <div class="mt-4 grid gap-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Label WhatsApp</label>
                                    <input name="whatsapp_label" value="{{ old('whatsapp_label', $landingContact['whatsapp_label'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">WhatsApp</label>
                                    <input name="whatsapp_value" value="{{ old('whatsapp_value', $landingContact['whatsapp_value'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Label Email</label>
                                    <input name="email_label" value="{{ old('email_label', $landingContact['email_label'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Email</label>
                                    <input name="email_value" value="{{ old('email_value', $landingContact['email_value'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Label Horario</label>
                                    <input name="hours_label" value="{{ old('hours_label', $landingContact['hours_label'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Horario</label>
                                    <input name="hours_value" value="{{ old('hours_value', $landingContact['hours_value'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Cards (2)</p>
                        <div class="mt-4 grid gap-4">
                            @for($i = 0; $i < 2; $i++)
                                @php($it = is_array($channels[$i] ?? null) ? $channels[$i] : [])
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                    <p class="text-xs font-semibold text-zinc-500">Card {{ $i + 1 }}</p>
                                    <div class="mt-3 grid gap-4">
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <label class="text-xs font-semibold text-zinc-700">Label</label>
                                                <input name="channel_{{ $i }}_label" value="{{ old('channel_'.$i.'_label', $it['label'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                            </div>
                                            <div>
                                                <label class="text-xs font-semibold text-zinc-700">Título</label>
                                                <input name="channel_{{ $i }}_title" value="{{ old('channel_'.$i.'_title', $it['title'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Descripción</label>
                                            <input name="channel_{{ $i }}_desc" value="{{ old('channel_'.$i.'_desc', $it['desc'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Formulario visual</p>
                        <div class="mt-4 grid gap-4">
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Label</label>
                                <input name="form_label" value="{{ old('form_label', $landingContact['form_label'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Placeholder</label>
                                <input name="form_placeholder" value="{{ old('form_placeholder', $landingContact['form_placeholder'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Texto botón</label>
                                    <input name="form_button_text" value="{{ old('form_button_text', $landingContact['form_button_text'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Nota</label>
                                    <input name="form_note" value="{{ old('form_note', $landingContact['form_note'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Imagen</p>
                        <div class="mt-4 grid gap-4">
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Imagen subida (archivo)</label>
                                <input type="file" name="uploaded_image" accept="image/*" class="mt-2 block w-full text-sm text-zinc-700 file:mr-3 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">

                                <label class="mt-3 inline-flex items-center gap-2 text-xs text-zinc-600">
                                    <input type="checkbox" name="clear_uploaded_image" value="1" @checked(old('clear_uploaded_image')) class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                                    Quitar imagen subida actual
                                </label>

                                @if(!empty($landingContact['has_uploaded_image']))
                                    <p class="mt-2 text-xs font-semibold text-emerald-700">Hay una imagen subida guardada en base de datos.</p>
                                @endif
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Imagen manual (URL o /ruta)</label>
                                <input name="image_url" value="{{ old('image_url', $manualImgSrc) }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm" placeholder="/storage/...">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Alt</label>
                                <input name="image_alt" value="{{ old('image_alt', $landingContact['image_alt'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
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
                <section data-animate="up" id="contacto" class="border-t border-zinc-200 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-12">
                        <div class="grid gap-10 lg:grid-cols-2">
                            <div>
                                <h2 id="previewTitle" class="text-2xl font-semibold tracking-tight">{{ $landingContact['title'] ?? '' }}</h2>
                                <p id="previewSubtitle" class="mt-2 text-sm text-zinc-600">{{ $landingContact['subtitle'] ?? '' }}</p>

                                <div class="mt-6 space-y-3 text-sm text-zinc-700">
                                    <p><span id="previewWhatsappLabel" class="font-semibold">{{ $landingContact['whatsapp_label'] ?? '' }}</span> <span id="previewWhatsappValue">{{ $landingContact['whatsapp_value'] ?? '' }}</span></p>
                                    <p><span id="previewEmailLabel" class="font-semibold">{{ $landingContact['email_label'] ?? '' }}</span> <span id="previewEmailValue">{{ $landingContact['email_value'] ?? '' }}</span></p>
                                    <p><span id="previewHoursLabel" class="font-semibold">{{ $landingContact['hours_label'] ?? '' }}</span> <span id="previewHoursValue">{{ $landingContact['hours_value'] ?? '' }}</span></p>
                                </div>

                                <div class="mt-8 grid gap-3 sm:grid-cols-2" data-animate-stagger="80">
                                    @for($i = 0; $i < 2; $i++)
                                        @php($it = is_array($channels[$i] ?? null) ? $channels[$i] : [])
                                        <div data-animate="up" class="rounded-3xl border border-zinc-200 bg-zinc-50 p-5">
                                            <p id="previewChannel{{ $i }}Label" class="text-xs font-semibold text-zinc-500">{{ $it['label'] ?? '' }}</p>
                                            <p id="previewChannel{{ $i }}Title" class="mt-2 text-sm font-semibold">{{ $it['title'] ?? '' }}</p>
                                            <p id="previewChannel{{ $i }}Desc" class="mt-1 text-sm text-zinc-600">{{ $it['desc'] ?? '' }}</p>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <div data-animate="up" class="rounded-3xl border border-zinc-200 bg-zinc-50 p-6">
                                <form action="#" method="get" class="grid gap-3">
                                    <label id="previewFormLabel" class="text-sm font-semibold" for="q">{{ $landingContact['form_label'] ?? '' }}</label>
                                    <input id="q" name="q" class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none ring-zinc-900/10 focus:ring-2" placeholder="{{ $landingContact['form_placeholder'] ?? '' }}" />
                                    <button id="previewFormButton" class="inline-flex items-center justify-center rounded-xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800" type="submit">{{ $landingContact['form_button_text'] ?? '' }}</button>
                                    <p id="previewFormNote" class="text-xs text-zinc-600">{{ $landingContact['form_note'] ?? '' }}</p>
                                </form>

                                <div class="mt-6">
                                    @php($imgSrc = trim((string)($landingContact['image_url'] ?? '')))
                                    @php($imgSrc = $imgSrc !== '' ? $imgSrc : $img)
                                    <img id="previewImage" class="h-48 w-full rounded-2xl border border-zinc-200 bg-white object-cover" src="{{ $imgSrc }}" alt="{{ $landingContact['image_alt'] ?? 'Contacto' }}" loading="lazy" data-fallback="{{ $img }}" data-current-uploaded-src="{{ trim((string)($landingContact['image_url'] ?? '')) }}" data-current-manual-src="{{ $manualImgSrc }}">
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
            const form = document.getElementById('landingContactForm');
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

            const setAttr = (id, attr, value) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.setAttribute(attr, value);
            };

            const render = () => {
                setText('previewTitle', get('title'));
                setText('previewSubtitle', get('subtitle'));

                setText('previewWhatsappLabel', get('whatsapp_label'));
                setText('previewWhatsappValue', get('whatsapp_value'));
                setText('previewEmailLabel', get('email_label'));
                setText('previewEmailValue', get('email_value'));
                setText('previewHoursLabel', get('hours_label'));
                setText('previewHoursValue', get('hours_value'));

                for (let i = 0; i < 2; i++) {
                    setText(`previewChannel${i}Label`, get(`channel_${i}_label`));
                    setText(`previewChannel${i}Title`, get(`channel_${i}_title`));
                    setText(`previewChannel${i}Desc`, get(`channel_${i}_desc`));
                }

                setText('previewFormLabel', get('form_label'));
                setAttr('q', 'placeholder', get('form_placeholder'));
                setText('previewFormButton', get('form_button_text'));
                setText('previewFormNote', get('form_note'));

                const previewImg = document.getElementById('previewImage');
                const uploadedInput = form.querySelector('[name="uploaded_image"]');
                const uploadedSelected = uploadedInput && uploadedInput.files && uploadedInput.files[0] ? uploadedInput.files[0] : null;
                const clearUploaded = form.querySelector('[name="clear_uploaded_image"]')?.checked === true;
                const currentUploadedSrc = (previewImg?.getAttribute('data-current-uploaded-src') || '').trim();
                const manual = get('image_url').trim();
                const fallback = previewImg?.getAttribute('data-fallback') || '{{ $img }}';

                let src = fallback;
                if (uploadedSelected) {
                    src = URL.createObjectURL(uploadedSelected);
                } else if (!clearUploaded && currentUploadedSrc) {
                    src = currentUploadedSrc;
                } else if (manual) {
                    src = manual;
                }

                setAttr('previewImage', 'src', src);
                setAttr('previewImage', 'alt', get('image_alt') || 'Contacto');

                setText('previewStatus', 'Vista previa actualizada (aún no guardada).');
            };

            form.addEventListener('input', render);
            render();
        })();
    </script>
@endsection
