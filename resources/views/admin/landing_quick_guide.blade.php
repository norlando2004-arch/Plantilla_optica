@extends('admin.dashboard_layout')

@section('title', 'Guía rápida (Landing)')

@section('content')
    @php($landingQuickGuide = $landingQuickGuide ?? [])
    @php($steps = is_array($landingQuickGuide['steps'] ?? null) ? $landingQuickGuide['steps'] : [])
    @php($img = asset('images/placeholder.svg'))
    @php($imgSrc = trim((string)($landingQuickGuide['image_url'] ?? '')))
    @php($imgSrc = $imgSrc !== '' ? $imgSrc : $img)

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Guía rápida (Landing)</h1>
            <p class="mt-1 text-sm text-zinc-600">Edita textos, botones, pasos e imagen. Derecha: vista previa en vivo igual al landing.</p>
        </div>
        <a href="{{ url('/') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver landing</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5">
            <form id="landingQuickGuideForm" method="POST" action="{{ route('configuracion.landing-quick-guide.update') }}">
                @csrf

                <div class="grid gap-4">
                    <div>
                        <label class="text-sm font-semibold">Etiqueta</label>
                        <input name="label" value="{{ old('label', $landingQuickGuide['label'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Título</label>
                        <input name="title" value="{{ old('title', $landingQuickGuide['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Descripción</label>
                        <input name="desc" value="{{ old('desc', $landingQuickGuide['desc'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold">Botón 1 (texto)</label>
                            <input name="cta_primary_text" value="{{ old('cta_primary_text', $landingQuickGuide['cta_primary_text'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-semibold">Botón 1 (URL)</label>
                            <input name="cta_primary_href" value="{{ old('cta_primary_href', $landingQuickGuide['cta_primary_href'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="#como-funciona o /como-funciona o https://...">
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold">Botón 2 (texto)</label>
                            <input name="cta_secondary_text" value="{{ old('cta_secondary_text', $landingQuickGuide['cta_secondary_text'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-semibold">Botón 2 (URL)</label>
                            <input name="cta_secondary_href" value="{{ old('cta_secondary_href', $landingQuickGuide['cta_secondary_href'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="#contacto o /contacto o https://...">
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Pasos (3)</p>
                        <div class="mt-4 grid gap-4 sm:grid-cols-3">
                            @for($i = 0; $i < 3; $i++)
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4 text-center">
                                    <label class="text-xs font-semibold text-zinc-700">Paso (k)</label>
                                    <input name="step_{{ $i }}_k" value="{{ old('step_'.$i.'_k', $steps[$i]['k'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm text-center">
                                    <label class="mt-3 block text-xs font-semibold text-zinc-700">Texto (v)</label>
                                    <input name="step_{{ $i }}_v" value="{{ old('step_'.$i.'_v', $steps[$i]['v'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm text-center">
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Imagen (URL o /ruta)</label>
                        <input name="image_url" value="{{ old('image_url', $landingQuickGuide['image_url'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="https://... o /storage/...">
                        <p class="mt-2 text-xs text-zinc-500">Si lo dejas vacío, se usa un placeholder.</p>
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Alt imagen</label>
                        <input name="image_alt" value="{{ old('image_alt', $landingQuickGuide['image_alt'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
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
                <section data-animate="up" class="border-b border-zinc-200 bg-zinc-50">
                    <div class="mx-auto max-w-7xl px-4 py-10">
                        <div class="relative overflow-hidden rounded-[2rem] border border-zinc-200 bg-white">
                            <div class="grid items-center gap-8 p-6 sm:p-10 lg:grid-cols-2">
                                <div>
                                    <p id="previewLabel" class="text-xs font-semibold tracking-wide text-zinc-500">{{ $landingQuickGuide['label'] ?? '' }}</p>
                                    <h2 id="previewTitle" class="mt-2 text-3xl font-semibold tracking-tight">{{ $landingQuickGuide['title'] ?? '' }}</h2>
                                    <p id="previewDesc" class="mt-3 max-w-xl text-sm text-zinc-600">{{ $landingQuickGuide['desc'] ?? '' }}</p>
                                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                                        <a id="previewCta1" href="{{ $landingQuickGuide['cta_primary_href'] ?? '#' }}" class="inline-flex items-center justify-center rounded-xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">{{ $landingQuickGuide['cta_primary_text'] ?? '' }}</a>
                                        <a id="previewCta2" href="{{ $landingQuickGuide['cta_secondary_href'] ?? '#' }}" class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">{{ $landingQuickGuide['cta_secondary_text'] ?? '' }}</a>
                                    </div>
                                    <dl class="mt-7 grid grid-cols-3 gap-3">
                                        @for($i = 0; $i < 3; $i++)
                                            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 text-center">
                                                <dt id="previewStep{{ $i }}K" class="text-xs font-semibold text-zinc-500">Paso {{ $steps[$i]['k'] ?? '' }}</dt>
                                                <dd id="previewStep{{ $i }}V" class="mt-1 text-sm font-semibold">{{ $steps[$i]['v'] ?? '' }}</dd>
                                            </div>
                                        @endfor
                                    </dl>
                                </div>

                                <div class="relative">
                                    <div class="absolute -inset-4 -z-10 rounded-[2.25rem] bg-gradient-to-r from-violet-100 to-fuchsia-100"></div>
                                    <img id="previewImg" class="h-72 w-full rounded-[1.75rem] border border-zinc-200 bg-white object-cover" src="{{ $imgSrc }}" alt="{{ $landingQuickGuide['image_alt'] ?? 'Banner guía' }}" loading="lazy" data-fallback="{{ $img }}">
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
            const form = document.getElementById('landingQuickGuideForm');
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

            const setImg = (id, urlField, altField) => {
                const img = document.getElementById(id);
                if (!img) return;

                const fallback = img.getAttribute('data-fallback') || '';
                const next = (get(urlField) || '').trim() || fallback;
                if (next && img.getAttribute('src') !== next) img.setAttribute('src', next);
                img.setAttribute('alt', get(altField));
            };

            const render = () => {
                setText('previewLabel', get('label'));
                setText('previewTitle', get('title'));
                setText('previewDesc', get('desc'));

                setText('previewCta1', get('cta_primary_text'));
                setHref('previewCta1', get('cta_primary_href'));

                setText('previewCta2', get('cta_secondary_text'));
                setHref('previewCta2', get('cta_secondary_href'));

                for (let i = 0; i < 3; i++) {
                    setText(`previewStep${i}K`, `Paso ${get(`step_${i}_k`)}`);
                    setText(`previewStep${i}V`, get(`step_${i}_v`));
                }

                setImg('previewImg', 'image_url', 'image_alt');

                setText('previewStatus', 'Vista previa actualizada (aún no guardada).');
            };

            form.addEventListener('input', render);
            render();
        })();
    </script>
@endsection
