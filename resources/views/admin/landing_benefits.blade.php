@extends('admin.dashboard_layout')

@section('title', 'Beneficios landing')

@section('content')
    @php($landingBenefits = $landingBenefits ?? [])
    @php($items = is_array($landingBenefits['items'] ?? null) ? $landingBenefits['items'] : [])

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Beneficios landing</h1>
            <p class="mt-1 text-sm text-zinc-600">Izquierda: lo editable. Derecha: vista previa en vivo.</p>
        </div>
        <a href="{{ url('/') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver landing</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5">
            <form id="landingBenefitsForm" method="POST" action="{{ route('configuracion.landing-benefits.update') }}">
                @csrf

                <div class="grid gap-4">
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Items (4)</p>
                        <p class="mt-1 text-xs text-zinc-500">Estos son los 4 cuadros debajo del bloque principal.</p>

                        <div class="mt-4 grid gap-4">
                            @for($i = 0; $i < 4; $i++)
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                    <p class="text-xs font-semibold text-zinc-500">{{ $i + 1 }}.</p>

                                    <label class="mt-2 block text-xs font-semibold text-zinc-700">Título</label>
                                    <input
                                        name="item_{{ $i }}_title"
                                        value="{{ old('item_'.$i.'_title', $items[$i]['title'] ?? '') }}"
                                        class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm"
                                    >

                                    <label class="mt-3 block text-xs font-semibold text-zinc-700">Descripción</label>
                                    <input
                                        name="item_{{ $i }}_desc"
                                        value="{{ old('item_'.$i.'_desc', $items[$i]['desc'] ?? '') }}"
                                        class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm"
                                    >
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
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold">Vista previa</h2>
                <span class="text-xs text-zinc-500">Así se verá en el landing</span>
            </div>

            <div class="mt-4 overflow-hidden rounded-3xl border border-zinc-200 bg-white">
                <section data-animate="up" class="border-t border-zinc-200 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-10">
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-animate-stagger="70">
                            @for($i = 0; $i < 4; $i++)
                                <div data-animate="up" class="rounded-3xl border border-zinc-200 p-6">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-white">
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 2l7 4v6c0 5-3 9-7 10-4-1-7-5-7-10V6l7-4Z"/>
                                            <path d="M9 12l2 2 4-5"/>
                                        </svg>
                                    </div>
                                    <p id="previewBenefit{{ $i }}Title" class="mt-4 text-sm font-semibold">{{ $items[$i]['title'] ?? '' }}</p>
                                    <p id="previewBenefit{{ $i }}Desc" class="mt-1 text-sm text-zinc-600">{{ $items[$i]['desc'] ?? '' }}</p>
                                </div>
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
            const form = document.getElementById('landingBenefitsForm');
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
                for (let i = 0; i < 4; i++) {
                    setText(`previewBenefit${i}Title`, get(`item_${i}_title`));
                    setText(`previewBenefit${i}Desc`, get(`item_${i}_desc`));
                }
                setText('previewStatus', 'Vista previa actualizada (aún no guardada).');
            };

            form.addEventListener('input', render);
            render();
        })();
    </script>
@endsection
