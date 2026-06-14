@extends('admin.dashboard_layout')

@section('title', 'Destacados (Landing)')

@section('content')
    @php($landingHighlights = $landingHighlights ?? [])
    @php($items = is_array($landingHighlights['items'] ?? null) ? $landingHighlights['items'] : [])
    @php($img = asset('images/placeholder.svg'))

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Destacados (Landing)</h1>
            <p class="mt-1 text-sm text-zinc-600">Edita solo los textos de la sección. Las 4 tarjetas se generan automáticamente (Top 4 más vistos).</p>
        </div>
        <a href="{{ url('/') }}#destacados" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver sección</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5">
            <form id="landingHighlightsForm" method="POST" action="{{ route('configuracion.landing-highlights.update') }}">
                @csrf

                <div class="grid gap-4">
                    <div>
                        <label class="text-sm font-semibold">Título</label>
                        <input name="title" value="{{ old('title', $landingHighlights['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Subtítulo</label>
                        <input name="subtitle" value="{{ old('subtitle', $landingHighlights['subtitle'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold">Tip (etiqueta)</label>
                            <input name="tip_label" value="{{ old('tip_label', $landingHighlights['tip_label'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-semibold">Tip (texto)</label>
                            <input name="tip_text" value="{{ old('tip_text', $landingHighlights['tip_text'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">Guardar</button>
                    </div>
                </div>
            </form>

            <p class="mt-4 text-xs text-zinc-500">Tip: la vista previa se actualiza al escribir (sin guardar).</p>
        </section>

        <section id="preview" class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-7 lg:sticky lg:top-8">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold">Vista previa</h2>
                <span class="text-xs text-zinc-500">Así se verá en el landing</span>
            </div>

            <p class="mt-2 text-xs text-zinc-500">Nota: las 4 tarjetas se calculan automáticamente (Top 4 por usuarios únicos). Aquí se muestran como ejemplo.</p>

            <div class="mt-4 overflow-hidden rounded-3xl border border-zinc-200 bg-white">
                <section data-animate="up" class="mx-auto max-w-7xl px-4 py-12">
                    <div class="grid gap-10 lg:grid-cols-3">
                        <div class="lg:col-span-1">
                            <h2 id="previewTitle" class="text-2xl font-semibold tracking-tight">{{ $landingHighlights['title'] ?? '' }}</h2>
                            <p id="previewSubtitle" class="mt-2 text-sm text-zinc-600">{{ $landingHighlights['subtitle'] ?? '' }}</p>

                            <div class="mt-6 rounded-3xl border border-zinc-200 bg-white p-6">
                                <p id="previewTipLabel" class="text-xs font-semibold text-zinc-500">{{ $landingHighlights['tip_label'] ?? '' }}</p>
                                <p id="previewTipText" class="mt-2 text-sm text-zinc-700">{{ $landingHighlights['tip_text'] ?? '' }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:col-span-2" data-animate-stagger="70">
                            @for($i = 0; $i < 4; $i++)
                                <article data-animate="up" class="rounded-3xl border border-zinc-200 bg-white p-4">
                                    <div class="flex items-center justify-between">
                                        <span id="previewTag{{ $i }}" class="inline-flex rounded-full bg-gradient-to-r from-violet-100 to-fuchsia-100 px-3 py-1 text-xs font-semibold text-zinc-800 ring-1 ring-inset ring-zinc-200">Top {{ $i + 1 }}</span>
                                        <a id="previewHref{{ $i }}" href="#" class="text-sm font-semibold text-zinc-700 hover:text-zinc-900">Ver →</a>
                                    </div>
                                    <img id="previewImg{{ $i }}" class="mt-4 h-40 w-full rounded-2xl border border-zinc-200 bg-white object-cover" src="{{ $img }}" alt="Gafa popular" loading="lazy" data-fallback="{{ $img }}">
                                    <h3 id="previewItemTitle{{ $i }}" class="mt-4 text-sm font-semibold">Gafa popular</h3>
                                    <p id="previewItemCopy{{ $i }}" class="mt-1 text-sm text-zinc-600">Se calcula automáticamente por visitas únicas.</p>
                                </article>
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
            const form = document.getElementById('landingHighlightsForm');
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

            const setImg = (id, url) => {
                const el = document.getElementById(id);
                if (!el) return;
                const fallback = el.getAttribute('data-fallback') || '';
                const next = (url || '').trim() || fallback;
                if (next && el.getAttribute('src') !== next) el.setAttribute('src', next);
            };

            const render = () => {
                setText('previewTitle', get('title'));
                setText('previewSubtitle', get('subtitle'));
                setText('previewTipLabel', get('tip_label'));
                setText('previewTipText', get('tip_text'));

                setText('previewStatus', 'Vista previa actualizada (aún no guardada).');
            };

            form.addEventListener('input', render);
            render();
        })();
    </script>
@endsection
