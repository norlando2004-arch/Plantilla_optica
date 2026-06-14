@extends('admin.dashboard_layout')

@section('title', 'Beneficios (Sección)')

@section('content')
    @php($landingEssentialBenefits = $landingEssentialBenefits ?? [])
    @php($items = is_array($landingEssentialBenefits['items'] ?? null) ? $landingEssentialBenefits['items'] : [])

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Beneficios (Sección)</h1>
            <p class="mt-1 text-sm text-zinc-600">Edita título/subtítulo y 3 cards. Derecha: vista previa en vivo igual al landing.</p>
        </div>
        <a href="{{ url('/') }}#beneficios" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver sección</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5">
            <form id="landingEssentialBenefitsForm" method="POST" action="{{ route('configuracion.landing-essential-benefits.update') }}">
                @csrf

                <div class="grid gap-4">
                    <div>
                        <label class="text-sm font-semibold">Título</label>
                        <input name="title" value="{{ old('title', $landingEssentialBenefits['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Subtítulo</label>
                        <input name="subtitle" value="{{ old('subtitle', $landingEssentialBenefits['subtitle'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Cards (3)</p>
                        <div class="mt-4 grid gap-4">
                            @for($i = 0; $i < 3; $i++)
                                @php($it = is_array($items[$i] ?? null) ? $items[$i] : [])
                                <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                    <p class="text-xs font-semibold text-zinc-500">Card {{ $i + 1 }}</p>

                                    <div class="mt-3 grid gap-4">
                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Título</label>
                                            <input name="item_{{ $i }}_title" value="{{ old('item_'.$i.'_title', $it['title'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                                        </div>

                                        <div>
                                            <label class="text-xs font-semibold text-zinc-700">Descripción</label>
                                            <input name="item_{{ $i }}_desc" value="{{ old('item_'.$i.'_desc', $it['desc'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
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
                <section data-animate="up" class="border-t border-zinc-200 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-12">
                        <h2 id="previewTitle" class="text-2xl font-semibold tracking-tight">{{ $landingEssentialBenefits['title'] ?? '' }}</h2>
                        <p id="previewSubtitle" class="mt-2 text-sm text-zinc-600">{{ $landingEssentialBenefits['subtitle'] ?? '' }}</p>

                        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-animate-stagger="80">
                            @for($i = 0; $i < 3; $i++)
                                @php($it = is_array($items[$i] ?? null) ? $items[$i] : [])
                                <div data-animate="up" class="rounded-3xl border border-zinc-200 p-6">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-zinc-900 text-white">
                                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 2l7 4v6c0 5-3 9-7 10-4-1-7-5-7-10V6l7-4Z"/>
                                            <path d="M9 12l2 2 4-5"/>
                                        </svg>
                                    </div>
                                    <h3 id="previewItem{{ $i }}Title" class="mt-4 text-sm font-semibold">{{ $it['title'] ?? '' }}</h3>
                                    <p id="previewItem{{ $i }}Desc" class="mt-1 text-sm text-zinc-600">{{ $it['desc'] ?? '' }}</p>
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
            const form = document.getElementById('landingEssentialBenefitsForm');
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

                for (let i = 0; i < 3; i++) {
                    setText(`previewItem${i}Title`, get(`item_${i}_title`));
                    setText(`previewItem${i}Desc`, get(`item_${i}_desc`));
                }

                setText('previewStatus', 'Vista previa actualizada (aún no guardada).');
            };

            form.addEventListener('input', render);
            render();
        })();
    </script>
@endsection
