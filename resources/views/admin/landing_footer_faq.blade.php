@extends('admin.dashboard_layout')

@section('title', 'Preguntas Frecuentes & Contacto')

@section('content')
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Preguntas Frecuentes & Contacto</h1>
            <p class="mt-1 text-sm text-zinc-600">Edita las FAQs que aparecen en el footer, contacto y redes sociales.</p>
        </div>
        <a href="{{ url('/') }}#footer-info" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver landing</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-7">
            <form id="footerFaqForm" method="POST" action="{{ route('configuracion.landing-footer-faq.update') }}" enctype="multipart/form-data">
                @csrf

                {{-- Sección Contacto --}}
                <div class="mb-8 border-b border-zinc-200 pb-8">
                    <h2 class="text-lg font-bold text-zinc-900">📞 Contacto</h2>
                    <div class="mt-4 grid gap-4">
                        <div>
                            <label class="text-xs font-semibold text-zinc-700">Email</label>
                            <input
                                name="contact_email"
                                value="{{ old('contact_email', $landingFooter['contact_email']) }}"
                                type="email"
                                class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm"
                            >
                            @error('contact_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-zinc-700">Teléfono</label>
                            <input
                                name="contact_phone"
                                value="{{ old('contact_phone', $landingFooter['contact_phone']) }}"
                                type="text"
                                class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm"
                            >
                            @error('contact_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                {{-- Sección Acerca De --}}
                <div class="mb-8 border-b border-zinc-200 pb-8">
                    <h2 class="text-lg font-bold text-zinc-900">ℹ️ Acerca de Optica</h2>
                    <div class="mt-4 grid gap-4">
                        @foreach([0, 1, 2] as $i)
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3">
                                <label class="text-[11px] font-semibold text-zinc-700">Opción {{ $i + 1 }}</label>
                                <input
                                    name="about_{{ $i }}_text"
                                    value="{{ old("about_$i.text", $landingFooter['about_links'][$i]['text'] ?? '') }}"
                                    class="mt-2 block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm"
                                    placeholder="Texto del link"
                                >
                                <input
                                    name="about_{{ $i }}_href"
                                    value="{{ old("about_$i.href", $landingFooter['about_links'][$i]['href'] ?? '') }}"
                                    class="mt-2 block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm"
                                    placeholder="URL (ej: https://optica.com/terminos o https://dominio.com/terminos.pdf)"
                                >
                                <div class="mt-2 rounded-lg border border-dashed border-zinc-300 bg-white p-3">
                                    <label class="text-[11px] font-semibold text-zinc-700">Subir PDF para Opción {{ $i + 1 }} (máx 100MB)</label>
                                    <input
                                        type="file"
                                        name="about_{{ $i }}_pdf"
                                        accept="application/pdf"
                                        class="mt-2 block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-zinc-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white"
                                    >
                                    @php($pdfUrl = $landingFooter['about_pdf_urls'][$i] ?? '')
                                    @php($pdfName = $landingFooter['about_pdf_names'][$i] ?? '')
                                    @if($pdfUrl !== '')
                                        <div class="mt-2 flex items-center justify-between gap-3">
                                            <a href="{{ $pdfUrl }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-blue-700 hover:underline">PDF actual: {{ $pdfName !== '' ? $pdfName : 'ver archivo' }}</a>
                                            <label class="inline-flex items-center gap-2 text-xs text-zinc-700">
                                                <input type="checkbox" name="clear_about_{{ $i }}_pdf" value="1" class="rounded border-zinc-300">
                                                Quitar PDF actual
                                            </label>
                                        </div>
                                    @endif
                                    @error("about_{$i}_pdf")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                @error("about_$i.href")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Sección Redes Sociales --}}
                <div class="mb-8 border-b border-zinc-200 pb-8">
                    <h2 class="text-lg font-bold text-zinc-900">🔗 Síguenos en Redes</h2>
                    <div class="mt-4 grid gap-4">
                        @foreach([
                            ['platform' => 'Facebook', 'example' => 'https://facebook.com/tu_pagina'],
                            ['platform' => 'Instagram', 'example' => 'https://instagram.com/tu_cuenta'],
                            ['platform' => 'TikTok', 'example' => 'https://tiktok.com/@tu_cuenta'],
                        ] as $social)
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3">
                                <label class="text-[11px] font-semibold text-zinc-700">{{ $social['platform'] }}</label>
                                <input
                                    name="social_{{ $loop->index }}_href"
                                    value="{{ old("social_{$loop->index}_href", $landingFooter['social_links'][$loop->index]['href'] ?? '#') }}"
                                    class="mt-2 block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm"
                                    placeholder="URL (ej: {{ $social['example'] }})"
                                >
                                @error("social_{$loop->index}_href")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Sección FAQs --}}
                <div class="mb-8">
                    <h2 class="text-lg font-bold text-zinc-900">❓ Preguntas Frecuentes</h2>
                    <p class="mt-1 text-sm text-zinc-600">Los usuarios harán click en las preguntas para ver las respuestas en un acordeón.</p>
                    <div id="faqContainer" class="mt-4 grid gap-4">
                        @forelse($landingFooter['faq'] as $i => $faqItem)
                            <div class="faq-item rounded-xl border border-zinc-200 bg-white p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1">
                                        <label class="text-xs font-semibold text-zinc-700">Pregunta {{ $i + 1 }}</label>
                                        <input
                                            type="text"
                                            name="faq[{{ $i }}][question]"
                                            value="{{ old("faq.$i.question", $faqItem['question'] ?? '') }}"
                                            class="mt-2 block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm"
                                            placeholder="¿Cómo...?"
                                        >
                                        @error("faq.$i.question")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                                        <label class="mt-3 text-xs font-semibold text-zinc-700">Respuesta</label>
                                        <textarea
                                            name="faq[{{ $i }}][answer]"
                                            rows="3"
                                            class="mt-2 block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm"
                                            placeholder="Escribe la respuesta aquí..."
                                        >{{ old("faq.$i.answer", $faqItem['answer'] ?? '') }}</textarea>
                                        @error("faq.$i.answer")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <button
                                        type="button"
                                        onclick="document.querySelector('.faq-item:nth-child({{ $i + 1 }})').remove()"
                                        class="mt-2 rounded-lg px-2 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500">Sin preguntas. Agrega una nueva.</p>
                        @endforelse
                    </div>
                    <button
                        type="button"
                        onclick="addFaqItem()"
                        class="mt-3 rounded-lg border border-zinc-200 px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50"
                    >
                        + Agregar pregunta
                    </button>
                </div>

                <div class="flex justify-end gap-3 border-t border-zinc-200 pt-6">
                    <button type="submit" class="rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </section>

        {{-- Preview en el lado derecho --}}
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-5 lg:sticky lg:top-8">
            <h2 class="text-sm font-semibold">Vista previa del footer</h2>
            <p class="mt-1 text-xs text-zinc-500">Así se ve en el landing</p>

            <div class="mt-4 rounded-2xl border border-zinc-200 bg-black p-6 text-white">
                <div class="grid gap-6 lg:grid-cols-3">
                    {{-- Acerca de --}}
                    <div>
                        <p class="text-xs font-bold">Acerca de optica</p>
                        <ul class="mt-2 space-y-1 text-[0.8rem] leading-5">
                            @forelse($landingFooter['about_links'] as $link)
                                @php($pdfUrl = trim((string) ($landingFooter['about_pdf_urls'][$loop->index] ?? '')))
                                @php($rawHref = $pdfUrl !== '' ? $pdfUrl : trim((string) ($link['href'] ?? '#')))
                                @php($isExternal = preg_match('/^https?:\/\//i', $rawHref) === 1)
                                @php($isPdf = preg_match('/\.pdf($|\?)/i', $rawHref) === 1)
                                @php($resolvedHref = ($isExternal && $isPdf) ? ('https://docs.google.com/gview?embedded=1&url=' . urlencode($rawHref)) : $rawHref)
                                <li>
                                    <a
                                        href="{{ $resolvedHref }}"
                                        class="hover:underline"
                                        @if($isExternal) target="_blank" rel="noopener noreferrer" @endif
                                    >{{ $link['text'] }}</a>
                                </li>
                            @empty
                                <li class="text-zinc-500">Sin links</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- Redes Sociales --}}
                    <div>
                        <p class="mt-1 text-xs font-bold">Síguenos en redes</p>
                        <div class="mt-2 flex items-center gap-2">
                            @foreach($landingFooter['social_links'] as $social)
                                <a href="{{ $social['href'] }}" class="flex h-8 w-8 items-center justify-center rounded-full {{ $social['color'] }} text-[0.7rem] font-bold">
                                    {{ $social['icon'] }}
                                </a>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            @php($contactEmail = trim((string) ($landingFooter['contact_email'] ?? '')))
                            @php($contactPhone = trim((string) ($landingFooter['contact_phone'] ?? '')))
                            @php($contactPhoneHref = preg_replace('/[^0-9+]/', '', $contactPhone))
                            <p class="text-xs font-bold">Contáctanos</p>
                            <p class="mt-1 text-[0.8rem] leading-5">
                                @if($contactEmail !== '')
                                    <a href="mailto:{{ $contactEmail }}" class="hover:underline">{{ $contactEmail }}</a>
                                @endif
                                <br>
                                @if($contactPhone !== '' && $contactPhoneHref !== '')
                                    <a href="tel:{{ $contactPhoneHref }}" class="hover:underline">{{ $contactPhone }}</a>
                                @else
                                    {{ $contactPhone }}
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- FAQ Preview --}}
                    <div>
                        <p class="mt-1 text-xs font-bold">Ayuda</p>
                        <ul class="mt-2 space-y-1 text-[0.8rem] leading-5">
                            @forelse($landingFooter['faq'] as $faq)
                                <li class="cursor-pointer hover:underline">{{ $faq['question'] }}</li>
                            @empty
                                <li class="text-zinc-500">Sin FAQs</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            {{-- FAQ Modal Preview --}}
            <div class="mt-6">
                <p class="text-xs font-bold">Modal al hacer click en FAQ:</p>
                <div class="mt-3 max-h-48 space-y-2 overflow-y-auto rounded-2xl border border-zinc-200 bg-zinc-50 p-3">
                    @forelse($landingFooter['faq'] as $faq)
                        <details class="rounded-lg border border-zinc-200 bg-white p-2">
                            <summary class="cursor-pointer text-xs font-semibold text-zinc-800">
                                {{ $faq['question'] }}
                            </summary>
                            <p class="mt-2 text-[0.75rem] text-zinc-700">{{ $faq['answer'] }}</p>
                        </details>
                    @empty
                        <p class="text-xs text-zinc-500">Sin FAQs para mostrar</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <script>
        function addFaqItem() {
            const container = document.getElementById('faqContainer');
            const index = container.querySelectorAll('.faq-item').length;
            
            const html = `
                <div class="faq-item rounded-xl border border-zinc-200 bg-white p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <label class="text-xs font-semibold text-zinc-700">Pregunta (nueva)</label>
                            <input
                                type="text"
                                name="faq[${index}][question]"
                                class="mt-2 block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm"
                                placeholder="¿Cómo...?"
                            >
                            <label class="mt-3 text-xs font-semibold text-zinc-700">Respuesta</label>
                            <textarea
                                name="faq[${index}][answer]"
                                rows="3"
                                class="mt-2 block w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm"
                                placeholder="Escribe la respuesta aquí..."
                            ></textarea>
                        </div>
                        <button
                            type="button"
                            onclick="this.closest('.faq-item').remove()"
                            class="mt-2 rounded-lg px-2 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
        }
    </script>
@endsection
