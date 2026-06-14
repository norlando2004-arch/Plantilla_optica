@extends('admin.dashboard_layout')

@section('title', 'Footer (Landing)')

@section('content')
    @php($landingFooter = $landingFooter ?? [])
    @php($img = asset('images/placeholder.svg'))
    @php($manualImgSrc = trim((string)($landingFooter['manual_image_url'] ?? ($landingFooter['image_url'] ?? ''))))
    @php($services = is_array($landingFooter['services_links'] ?? null) ? $landingFooter['services_links'] : [])
    @php($help = is_array($landingFooter['help_links'] ?? null) ? $landingFooter['help_links'] : [])
    @php($legal = is_array($landingFooter['legal_links'] ?? null) ? $landingFooter['legal_links'] : [])

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Footer (Landing)</h1>
            <p class="mt-1 text-sm text-zinc-600">Edita textos, imagen y enlaces del footer. Derecha: vista previa en vivo igual al landing.</p>
        </div>
        <a href="{{ url('/') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver landing</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5">
            <form id="landingFooterForm" method="POST" action="{{ route('configuracion.landing-footer.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid gap-4">
                    <div>
                        <label class="text-sm font-semibold">Nombre</label>
                        <input name="company_name" value="{{ old('company_name', $landingFooter['company_name'] ?? 'Óptica') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Texto</label>
                        <input name="tagline" value="{{ old('tagline', $landingFooter['tagline'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Imagen footer</p>
                        <div class="mt-4 grid gap-4">
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Imagen subida (archivo)</label>
                                <input type="file" name="uploaded_image" accept="image/*" class="mt-2 block w-full text-sm text-zinc-700 file:mr-3 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">

                                <label class="mt-3 inline-flex items-center gap-2 text-xs text-zinc-600">
                                    <input type="checkbox" name="clear_uploaded_image" value="1" @checked(old('clear_uploaded_image')) class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                                    Quitar imagen subida actual
                                </label>

                                @if(!empty($landingFooter['has_uploaded_image']))
                                    <p class="mt-2 text-xs font-semibold text-emerald-700">Hay una imagen subida guardada en base de datos.</p>
                                @endif
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Imagen manual (URL opcional)</label>
                                <input name="image_url" value="{{ old('image_url', $manualImgSrc) }}" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm" placeholder="/storage/... o https://...">
                                @error('image_url')
                                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Alt</label>
                                <input name="image_alt" value="{{ old('image_alt', $landingFooter['image_alt'] ?? 'Imagen footer') }}" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Aviso</p>
                        <div class="mt-4 grid gap-4">
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Título</label>
                                <input name="notice_title" value="{{ old('notice_title', $landingFooter['notice_title'] ?? 'Aviso') }}" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Texto</label>
                                <textarea name="notice_text" rows="3" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm">{{ old('notice_text', $landingFooter['notice_text'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Servicios (4)</p>
                        <div class="mt-4 grid gap-4">
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Título columna</label>
                                <input name="services_title" value="{{ old('services_title', $landingFooter['services_title'] ?? 'Servicios') }}" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm">
                            </div>

                            @for($i = 0; $i < 4; $i++)
                                @php($it = is_array($services[$i] ?? null) ? $services[$i] : [])
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                    <p class="text-xs font-semibold text-zinc-500">Link {{ $i + 1 }}</p>
                                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Texto</label>
                                            <input name="service_{{ $i }}_text" value="{{ old('service_'.$i.'_text', $it['text'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Href</label>
                                            <input name="service_{{ $i }}_href" value="{{ old('service_'.$i.'_href', $it['href'] ?? '#') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm" placeholder="#ancla o /ruta o https://...">
                                            @error('service_'.$i.'_href')
                                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Ayuda (5)</p>
                        <div class="mt-4 grid gap-4">
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Título columna</label>
                                <input name="help_title" value="{{ old('help_title', $landingFooter['help_title'] ?? 'Ayuda') }}" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm">
                            </div>

                            @for($i = 0; $i < 5; $i++)
                                @php($it = is_array($help[$i] ?? null) ? $help[$i] : [])
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                    <p class="text-xs font-semibold text-zinc-500">Link {{ $i + 1 }}</p>
                                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Texto</label>
                                            <input name="help_{{ $i }}_text" value="{{ old('help_'.$i.'_text', $it['text'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Href</label>
                                            <input name="help_{{ $i }}_href" value="{{ old('help_'.$i.'_href', $it['href'] ?? '#') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm" placeholder="#ancla o /ruta o https://...">
                                            @error('help_'.$i.'_href')
                                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Legal (4)</p>
                        <div class="mt-4 grid gap-4">
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Título columna</label>
                                <input name="legal_title" value="{{ old('legal_title', $landingFooter['legal_title'] ?? 'Legal') }}" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm">
                            </div>

                            @for($i = 0; $i < 4; $i++)
                                @php($it = is_array($legal[$i] ?? null) ? $legal[$i] : [])
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                    <p class="text-xs font-semibold text-zinc-500">Link {{ $i + 1 }}</p>
                                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Texto</label>
                                            <input name="legal_{{ $i }}_text" value="{{ old('legal_'.$i.'_text', $it['text'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Href</label>
                                            <input name="legal_{{ $i }}_href" value="{{ old('legal_'.$i.'_href', $it['href'] ?? '#') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm" placeholder="#ancla o /ruta o https://...">
                                            @error('legal_'.$i.'_href')
                                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Barra inferior</p>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Label</label>
                                <input name="contact_label" value="{{ old('contact_label', $landingFooter['contact_label'] ?? 'Atención:') }}" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Teléfono</label>
                                <input name="contact_phone" value="{{ old('contact_phone', $landingFooter['contact_phone'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-zinc-700">Email</label>
                                <input name="contact_email" value="{{ old('contact_email', $landingFooter['contact_email'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>

                    <button class="mt-2 inline-flex w-full items-center justify-center rounded-2xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white hover:bg-zinc-800" type="submit">Guardar cambios</button>
                    <p class="text-xs text-zinc-500">La vista previa se actualiza en vivo al escribir. Guardar persiste en DB.</p>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-7">
            <p class="text-sm font-semibold text-zinc-900">Vista previa</p>
            <p class="mt-1 text-xs text-zinc-600">Markup replicado del footer del landing.</p>

            <div class="mt-4 overflow-hidden rounded-3xl border border-zinc-200 bg-white">
                @php($previewImg = trim((string)($landingFooter['image_url'] ?? '')))
                @php($previewImg = $previewImg !== '' ? $previewImg : $img)

                <footer data-animate="up" class="border-t border-zinc-200 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-10">
                        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-5">
                            <div class="lg:col-span-2">
                                <p id="previewCompany" class="text-sm font-semibold">{{ $landingFooter['company_name'] ?? 'Óptica' }}</p>
                                <p id="previewTagline" class="mt-2 text-sm text-zinc-600">{{ $landingFooter['tagline'] ?? '' }}</p>
                                <div class="mt-5">
                                    <img id="previewImage" class="h-28 w-full rounded-2xl border border-zinc-200 bg-white object-cover" src="{{ $previewImg }}" alt="{{ $landingFooter['image_alt'] ?? 'Imagen footer' }}" loading="lazy" data-fallback="{{ $img }}" data-current-uploaded-src="{{ trim((string)($landingFooter['image_url'] ?? '')) }}" data-current-manual-src="{{ $manualImgSrc }}">
                                </div>

                                <div class="mt-6 rounded-3xl border border-zinc-200 bg-zinc-50 p-5">
                                    <p id="previewNoticeTitle" class="text-xs font-semibold text-zinc-500">{{ $landingFooter['notice_title'] ?? 'Aviso' }}</p>
                                    <p id="previewNoticeText" class="mt-2 text-sm text-zinc-700">{{ $landingFooter['notice_text'] ?? '' }}</p>
                                </div>
                            </div>

                            <div>
                                <p id="previewServicesTitle" class="text-sm font-semibold">{{ $landingFooter['services_title'] ?? 'Servicios' }}</p>
                                <ul class="mt-3 space-y-2 text-sm text-zinc-600">
                                    @for($i = 0; $i < 4; $i++)
                                        @php($it = is_array($services[$i] ?? null) ? $services[$i] : [])
                                        <li><a id="previewService{{ $i }}" href="{{ $it['href'] ?? '#servicios' }}" class="hover:text-zinc-900">{{ $it['text'] ?? '' }}</a></li>
                                    @endfor
                                </ul>
                            </div>

                            <div>
                                <p id="previewHelpTitle" class="text-sm font-semibold">{{ $landingFooter['help_title'] ?? 'Ayuda' }}</p>
                                <ul class="mt-3 space-y-2 text-sm text-zinc-600">
                                    @for($i = 0; $i < 5; $i++)
                                        @php($it = is_array($help[$i] ?? null) ? $help[$i] : [])
                                        <li><a id="previewHelp{{ $i }}" href="{{ $it['href'] ?? '#' }}" class="hover:text-zinc-900">{{ $it['text'] ?? '' }}</a></li>
                                    @endfor
                                </ul>
                            </div>

                            <div>
                                <p id="previewLegalTitle" class="text-sm font-semibold">{{ $landingFooter['legal_title'] ?? 'Legal' }}</p>
                                <ul class="mt-3 space-y-2 text-sm text-zinc-600">
                                    @for($i = 0; $i < 4; $i++)
                                        @php($it = is_array($legal[$i] ?? null) ? $legal[$i] : [])
                                        <li><a id="previewLegal{{ $i }}" href="{{ $it['href'] ?? '#' }}" class="hover:text-zinc-900">{{ $it['text'] ?? '' }}</a></li>
                                    @endfor
                                </ul>
                            </div>
                        </div>

                        <div class="mt-10 flex flex-col gap-4 border-t border-zinc-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-zinc-500">© {{ date('Y') }} <span id="previewCompany2">{{ $landingFooter['company_name'] ?? 'Óptica' }}</span>. Todos los derechos reservados.</p>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-zinc-500">
                                <span id="previewContactLabel" class="font-semibold text-zinc-600">{{ $landingFooter['contact_label'] ?? 'Atención:' }}</span>
                                <span id="previewContactPhone">{{ $landingFooter['contact_phone'] ?? '' }}</span>
                                <span>•</span>
                                <span id="previewContactEmail">{{ $landingFooter['contact_email'] ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                </footer>

                <div class="border-t border-zinc-200 bg-white p-6">
                    <p class="text-xs font-semibold text-zinc-600">Estado</p>
                    <p id="previewStatus" class="mt-1 text-sm text-zinc-700">Listo para guardar.</p>
                </div>
            </div>
        </section>
    </div>

    <script>
        (function () {
            const form = document.getElementById('landingFooterForm');
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
                setText('previewCompany', get('company_name'));
                setText('previewCompany2', get('company_name'));
                setText('previewTagline', get('tagline'));

                setText('previewNoticeTitle', get('notice_title'));
                setText('previewNoticeText', get('notice_text'));

                setText('previewServicesTitle', get('services_title'));
                for (let i = 0; i < 4; i++) {
                    setText(`previewService${i}`, get(`service_${i}_text`));
                    setAttr(`previewService${i}`, 'href', get(`service_${i}_href`));
                }

                setText('previewHelpTitle', get('help_title'));
                for (let i = 0; i < 5; i++) {
                    setText(`previewHelp${i}`, get(`help_${i}_text`));
                    setAttr(`previewHelp${i}`, 'href', get(`help_${i}_href`));
                }

                setText('previewLegalTitle', get('legal_title'));
                for (let i = 0; i < 4; i++) {
                    setText(`previewLegal${i}`, get(`legal_${i}_text`));
                    setAttr(`previewLegal${i}`, 'href', get(`legal_${i}_href`));
                }

                setText('previewContactLabel', get('contact_label'));
                setText('previewContactPhone', get('contact_phone'));
                setText('previewContactEmail', get('contact_email'));

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
                setAttr('previewImage', 'alt', get('image_alt') || 'Imagen footer');

                setText('previewStatus', 'Vista previa actualizada (aún no guardada).');
            };

            form.addEventListener('input', render);
            render();
        })();
    </script>
@endsection
