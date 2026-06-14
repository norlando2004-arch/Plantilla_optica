@extends('admin.dashboard_layout')

@section('title', 'Preguntas frecuentes (FAQ)')

@section('content')
    @php($landingFaq = $landingFaq ?? [])
    @php($items = is_array($landingFaq['items'] ?? null) ? $landingFaq['items'] : [])

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Preguntas frecuentes (FAQ)</h1>
            <p class="mt-1 text-sm text-zinc-600">FAQ accesible usando &lt;details&gt; (sin JS). Derecha: vista previa en vivo igual al landing.</p>
        </div>
        <a href="{{ url('/') }}#faq" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver sección</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5">
            <form id="landingFaqForm" method="POST" action="{{ route('configuracion.landing-faq.update') }}">
                @csrf

                <div class="grid gap-4">
                    <div>
                        <label class="text-sm font-semibold">Título</label>
                        <input name="title" value="{{ old('title', $landingFaq['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Subtítulo</label>
                        <input name="subtitle" value="{{ old('subtitle', $landingFaq['subtitle'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        <p class="mt-2 text-xs text-zinc-500">Tip: si necesitas saltos de línea en respuestas, usa Enter en las respuestas.</p>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Preguntas (5)</p>
                        <div class="mt-4 grid gap-4">
                            @for($i = 0; $i < 5; $i++)
                                @php($it = is_array($items[$i] ?? null) ? $items[$i] : [])
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                    <p class="text-xs font-semibold text-zinc-500">Item {{ $i + 1 }}</p>

                                    <div class="mt-3 grid gap-4">
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Pregunta</label>
                                            <input name="item_{{ $i }}_question" value="{{ old('item_'.$i.'_question', $it['question'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                        </div>

                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Respuesta</label>
                                            <textarea name="item_{{ $i }}_answer" rows="4" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">{{ old('item_'.$i.'_answer', $it['answer'] ?? '') }}</textarea>
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
                <section data-animate="up" id="faq" class="border-t border-zinc-200 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-12">
                        <div class="grid gap-10 lg:grid-cols-3">
                            <div class="lg:col-span-1">
                                <h2 id="previewTitle" class="text-2xl font-semibold tracking-tight">{{ $landingFaq['title'] ?? '' }}</h2>
                                <p id="previewSubtitle" class="mt-2 text-sm text-zinc-600">{{ $landingFaq['subtitle'] ?? '' }}</p>
                            </div>

                            <div class="space-y-3 lg:col-span-2" data-animate-stagger="70">
                                @for($i = 0; $i < 5; $i++)
                                    @php($it = is_array($items[$i] ?? null) ? $items[$i] : [])
                                    <details data-animate="up" class="group rounded-3xl border border-zinc-200 p-5">
                                        <summary class="cursor-pointer list-none text-sm font-semibold">
                                            <span class="flex items-center justify-between gap-4">
                                                <span id="previewItem{{ $i }}Q">{{ $it['question'] ?? '' }}</span>
                                                <span class="flex h-8 w-8 items-center justify-center rounded-2xl bg-zinc-900 text-white">
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4 transition-transform group-open:rotate-45" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M12 5v14"/>
                                                        <path d="M5 12h14"/>
                                                    </svg>
                                                </span>
                                            </span>
                                        </summary>
                                        <p id="previewItem{{ $i }}A" class="mt-3 whitespace-pre-line text-sm text-zinc-600">{{ $it['answer'] ?? '' }}</p>
                                    </details>
                                @endfor
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
            const form = document.getElementById('landingFaqForm');
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

            const render = () => {
                setText('previewTitle', get('title'));
                setText('previewSubtitle', get('subtitle'));

                for (let i = 0; i < 5; i++) {
                    setText(`previewItem${i}Q`, get(`item_${i}_question`));
                    setText(`previewItem${i}A`, get(`item_${i}_answer`));
                }

                setText('previewStatus', 'Vista previa actualizada (aún no guardada).');
            };

            form.addEventListener('input', render);
            render();
        })();
    </script>
@endsection
