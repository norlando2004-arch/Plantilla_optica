@extends('admin.dashboard_layout')

@section('title', 'Listo para el panel admin')

@section('content')
    @php($landingAdminReady = $landingAdminReady ?? [])
    @php($modules = is_array($landingAdminReady['modules'] ?? null) ? $landingAdminReady['modules'] : [])
    @php($blocks = is_array($landingAdminReady['blocks'] ?? null) ? $landingAdminReady['blocks'] : [])
    @php($img = asset('images/placeholder.svg'))
    @php($imgSrc = trim((string)($landingAdminReady['image_url'] ?? '')))
    @php($imgSrc = $imgSrc !== '' ? $imgSrc : $img)

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Listo para el panel admin</h1>
            <p class="mt-1 text-sm text-zinc-600">Edita el bloque y puedes habilitar/deshabilitar su visibilidad en el landing.</p>
        </div>
        <a href="{{ url('/') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver landing</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5">
            <form id="landingAdminReadyForm" method="POST" action="{{ route('configuracion.landing-admin-ready.update') }}">
                @csrf

                <div class="grid gap-4">
                    <label class="flex items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold">Habilitado</p>
                            <p class="text-xs text-zinc-600">Si lo deshabilitas, esta sección no se mostrará en el landing.</p>
                        </div>
                        <input id="enabled" name="enabled" type="checkbox" value="1" class="h-5 w-5" {{ old('enabled', (bool)($landingAdminReady['enabled'] ?? true)) ? 'checked' : '' }}>
                    </label>

                    <div>
                        <label class="text-sm font-semibold">Título</label>
                        <input name="title" value="{{ old('title', $landingAdminReady['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Texto</label>
                        <textarea name="text" rows="3" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">{{ old('text', $landingAdminReady['text'] ?? '') }}</textarea>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Módulos (2)</p>
                        <div class="mt-4 grid gap-4">
                            @for($i = 0; $i < 2; $i++)
                                @php($m = is_array($modules[$i] ?? null) ? $modules[$i] : [])
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                    <p class="text-xs font-semibold text-zinc-500">Módulo {{ $i + 1 }}</p>
                                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Etiqueta</label>
                                            <input name="module_{{ $i }}_label" value="{{ old('module_'.$i.'_label', $m['label'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Título</label>
                                            <input name="module_{{ $i }}_title" value="{{ old('module_'.$i.'_title', $m['title'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="text-xs font-semibold text-zinc-700">Descripción</label>
                                        <input name="module_{{ $i }}_desc" value="{{ old('module_'.$i.'_desc', $m['desc'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold">Imagen (URL o /ruta)</label>
                            <input name="image_url" value="{{ old('image_url', $landingAdminReady['image_url'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm" placeholder="https://... o /storage/...">
                            <p class="mt-2 text-xs text-zinc-500">Vacío = placeholder.</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold">Alt imagen</label>
                            <input name="image_alt" value="{{ old('image_alt', $landingAdminReady['image_alt'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Bloques (3)</p>
                        <div class="mt-4 grid gap-4">
                            @for($i = 0; $i < 3; $i++)
                                @php($b = is_array($blocks[$i] ?? null) ? $blocks[$i] : [])
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                    <p class="text-xs font-semibold text-zinc-500">Bloque {{ $i + 1 }}</p>
                                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Etiqueta</label>
                                            <input name="block_{{ $i }}_label" value="{{ old('block_'.$i.'_label', $b['label'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                        </div>
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Título</label>
                                            <input name="block_{{ $i }}_title" value="{{ old('block_'.$i.'_title', $b['title'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
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
                <section id="previewSection" data-animate="up" class="border-t border-zinc-200 bg-zinc-50">
                    <div class="mx-auto max-w-7xl px-4 py-12">
                        <div class="grid items-center gap-10 lg:grid-cols-2">
                            <div>
                                <h2 id="previewTitle" class="text-2xl font-semibold tracking-tight">{{ $landingAdminReady['title'] ?? '' }}</h2>
                                <p id="previewText" class="mt-2 text-sm text-zinc-600">{{ $landingAdminReady['text'] ?? '' }}</p>
                                <div class="mt-6 grid gap-3 sm:grid-cols-2" data-animate-stagger="80">
                                    @for($i = 0; $i < 2; $i++)
                                        @php($m = is_array($modules[$i] ?? null) ? $modules[$i] : [])
                                        <div data-animate="up" class="rounded-3xl border border-zinc-200 bg-white p-5">
                                            <p id="previewModule{{ $i }}Label" class="text-xs font-semibold text-zinc-500">{{ $m['label'] ?? '' }}</p>
                                            <p id="previewModule{{ $i }}Title" class="mt-1 text-sm font-semibold">{{ $m['title'] ?? '' }}</p>
                                            <p id="previewModule{{ $i }}Desc" class="mt-2 text-sm text-zinc-600">{{ $m['desc'] ?? '' }}</p>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <div class="rounded-[2rem] border border-zinc-200 bg-white p-4">
                                <img id="previewImg" class="h-64 w-full rounded-[1.5rem] border border-zinc-200 object-cover" src="{{ $imgSrc }}" alt="{{ $landingAdminReady['image_alt'] ?? 'Imagen de ejemplo' }}" loading="lazy" data-fallback="{{ $img }}">
                                <div class="mt-4 grid gap-3 sm:grid-cols-3" data-animate-stagger="70">
                                    @for($i = 0; $i < 3; $i++)
                                        @php($b = is_array($blocks[$i] ?? null) ? $blocks[$i] : [])
                                        <div data-animate="up" class="rounded-2xl bg-zinc-50 p-4">
                                            <p id="previewBlock{{ $i }}Label" class="text-xs font-semibold text-zinc-500">{{ $b['label'] ?? '' }}</p>
                                            <p id="previewBlock{{ $i }}Title" class="mt-1 text-sm font-semibold">{{ $b['title'] ?? '' }}</p>
                                        </div>
                                    @endfor
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
            const form = document.getElementById('landingAdminReadyForm');
            if (!form) return;

            const get = (name) => {
                const el = form.querySelector(`[name="${name}"]`);
                if (!el) return '';
                if (el.type === 'checkbox') return el.checked ? '1' : '0';
                return String(el.value || '');
            };

            const setText = (id, value) => {
                const el = document.getElementById(id);
                if (!el) return;
                el.textContent = value;
            };

            const setImg = () => {
                const img = document.getElementById('previewImg');
                if (!img) return;
                const fallback = img.getAttribute('data-fallback') || '';
                const next = (get('image_url') || '').trim() || fallback;
                if (next && img.getAttribute('src') !== next) img.setAttribute('src', next);
                img.setAttribute('alt', get('image_alt'));
            };

            const render = () => {
                const enabled = get('enabled') === '1';
                const section = document.getElementById('previewSection');
                if (section) section.style.display = enabled ? '' : 'none';

                setText('previewTitle', get('title'));
                setText('previewText', get('text'));

                for (let i = 0; i < 2; i++) {
                    setText(`previewModule${i}Label`, get(`module_${i}_label`));
                    setText(`previewModule${i}Title`, get(`module_${i}_title`));
                    setText(`previewModule${i}Desc`, get(`module_${i}_desc`));
                }

                for (let i = 0; i < 3; i++) {
                    setText(`previewBlock${i}Label`, get(`block_${i}_label`));
                    setText(`previewBlock${i}Title`, get(`block_${i}_title`));
                }

                setImg();
                setText('previewStatus', enabled ? 'Vista previa actualizada (aún no guardada).' : 'Sección deshabilitada (no se verá en el landing).');
            };

            form.addEventListener('input', render);
            render();
        })();
    </script>
@endsection
