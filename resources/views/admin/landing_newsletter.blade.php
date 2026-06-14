@extends('admin.dashboard_layout')

@section('title', 'Newsletter (Landing)')

@section('content')
    @php($landingNewsletter = $landingNewsletter ?? [])

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Newsletter (Landing)</h1>
            <p class="mt-1 text-sm text-zinc-600">Edita textos del formulario visual (sin envío real). Derecha: vista previa en vivo igual al landing.</p>
        </div>
        <a href="{{ url('/') }}#newsletter" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver sección</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5">
            <form id="landingNewsletterForm" method="POST" action="{{ route('configuracion.landing-newsletter.update') }}">
                @csrf

                <div class="grid gap-4">
                    <div>
                        <label class="text-sm font-semibold">Título</label>
                        <input name="title" value="{{ old('title', $landingNewsletter['title'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div>
                        <label class="text-sm font-semibold">Descripción</label>
                        <input name="subtitle" value="{{ old('subtitle', $landingNewsletter['subtitle'] ?? '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <p class="text-sm font-semibold">Formulario</p>
                        <div class="mt-4 grid gap-4">
                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Label del email</label>
                                <input name="email_label" value="{{ old('email_label', $landingNewsletter['email_label'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Placeholder del email</label>
                                <input name="email_placeholder" value="{{ old('email_placeholder', $landingNewsletter['email_placeholder'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Texto del botón</label>
                                <input name="button_text" value="{{ old('button_text', $landingNewsletter['button_text'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-zinc-700">Nota debajo</label>
                                <input name="note" value="{{ old('note', $landingNewsletter['note'] ?? '') }}" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
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
                <section data-animate="up" id="newsletter" class="border-t border-zinc-200 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-12">
                        <div data-animate="up" class="rounded-[2rem] border border-zinc-200 bg-zinc-50 p-6 sm:p-10">
                            <div class="grid items-center gap-8 lg:grid-cols-2">
                                <div>
                                    <h2 id="previewTitle" class="text-2xl font-semibold tracking-tight">{{ $landingNewsletter['title'] ?? '' }}</h2>
                                    <p id="previewSubtitle" class="mt-2 text-sm text-zinc-600">{{ $landingNewsletter['subtitle'] ?? '' }}</p>
                                </div>
                                <form action="#" method="get" class="grid gap-3 sm:grid-cols-[1fr_auto]">
                                    <label class="sr-only" for="previewEmail">Email</label>
                                    <input id="previewEmail" name="email" type="email" class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none ring-zinc-900/10 focus:ring-2" placeholder="{{ $landingNewsletter['email_placeholder'] ?? '' }}" />
                                    <button id="previewButton" class="inline-flex items-center justify-center rounded-xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800" type="submit">{{ $landingNewsletter['button_text'] ?? '' }}</button>
                                    <p id="previewNote" class="sm:col-span-2 text-xs text-zinc-600">{{ $landingNewsletter['note'] ?? '' }}</p>
                                </form>
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
            const form = document.getElementById('landingNewsletterForm');
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
                setAttr('previewEmail', 'placeholder', get('email_placeholder'));
                setText('previewButton', get('button_text'));
                setText('previewNote', get('note'));
                setText('previewStatus', 'Vista previa actualizada (aún no guardada).');
            };

            form.addEventListener('input', render);
            render();
        })();
    </script>
@endsection
