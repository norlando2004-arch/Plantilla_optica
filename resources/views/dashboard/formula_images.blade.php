<!-- blade -->
@extends('layouts.dashboard_empty_sidebar')

@section('title', 'Imagenes de formulas')
@section('heading', 'Imagenes de formulas')

@section('content')
    <div class="space-y-6">
        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-3xl border border-zinc-200 bg-white p-5">
            <h3 class="text-base font-semibold text-zinc-900">Gestionar imágenes de fórmulas</h3>
            <p class="mt-1 text-sm text-zinc-500">Sube imágenes que se mostrarán en la página de compra de gafas (monofocal, bifocal, ocupacionales, progresivos).</p>

            <form class="mt-4" action="{{ route('dashboard.formula-images.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <label class="text-sm font-semibold text-zinc-800">Monofocal</label>
                        <p class="text-xs text-zinc-500">Sube únicamente el icono (miniatura) que será el que se muestre en la selección.</p>
                        <input type="file" name="mono_icon_image" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        <label class="mt-3 block text-xs font-medium text-zinc-700" for="mono_description_text">Texto mostrado al usuario</label>
                        <textarea id="mono_description_text" name="mono_description_text" rows="4" maxlength="400" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">{{ old('mono_description_text', $formulas['mono_description']) }}</textarea>
                        <p class="mt-1 text-[11px] text-zinc-500">Máximo 400 caracteres.</p>
                        <div class="mt-3">
                            <img src="{{ $formulas['mono_icon'] }}" alt="Icono monofocal" loading="lazy" class="h-16 w-20 object-contain sm:h-20 sm:w-24">
                        </div>
                        <label class="mt-2 inline-flex items-center gap-2 text-xs"><input type="checkbox" name="remove_mono_icon_image" value="1"> Quitar icono</label>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <label class="text-sm font-semibold text-zinc-800">Bifocal</label>
                        <p class="text-xs text-zinc-500">Sube únicamente el icono (miniatura) que será el que se muestre en la selección.</p>
                        <input type="file" name="bifocal_icon_image" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        <label class="mt-3 block text-xs font-medium text-zinc-700" for="bifocal_description_text">Texto mostrado al usuario</label>
                        <textarea id="bifocal_description_text" name="bifocal_description_text" rows="4" maxlength="400" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">{{ old('bifocal_description_text', $formulas['bifocal_description']) }}</textarea>
                        <p class="mt-1 text-[11px] text-zinc-500">Máximo 400 caracteres.</p>
                        <div class="mt-3">
                            <img src="{{ $formulas['bifocal_icon'] }}" alt="Icono bifocal" loading="lazy" class="h-16 w-20 object-contain sm:h-20 sm:w-24">
                        </div>
                        <label class="mt-2 inline-flex items-center gap-2 text-xs"><input type="checkbox" name="remove_bifocal_icon_image" value="1"> Quitar icono</label>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <label class="text-sm font-semibold text-zinc-800">Ocupacionales</label>
                        <p class="text-xs text-zinc-500">Sube únicamente el icono (miniatura) que será el que se muestre en la selección.</p>
                        <input type="file" name="ocupacional_icon_image" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        <label class="mt-3 block text-xs font-medium text-zinc-700" for="ocupacional_description_text">Texto mostrado al usuario</label>
                        <textarea id="ocupacional_description_text" name="ocupacional_description_text" rows="4" maxlength="400" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">{{ old('ocupacional_description_text', $formulas['ocupacional_description']) }}</textarea>
                        <p class="mt-1 text-[11px] text-zinc-500">Máximo 400 caracteres.</p>
                        <div class="mt-3">
                            <img src="{{ $formulas['ocupacional_icon'] }}" alt="Icono ocupacional" loading="lazy" class="h-16 w-20 object-contain sm:h-20 sm:w-24">
                        </div>
                        <label class="mt-2 inline-flex items-center gap-2 text-xs"><input type="checkbox" name="remove_ocupacional_icon_image" value="1"> Quitar icono</label>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <label class="text-sm font-semibold text-zinc-800">Imagen por defecto (lente)</label>
                        <p class="text-xs text-zinc-500">Esta imagen se usará como fallback cuando no exista otra.</p>
                        <input type="file" name="default_lente_image" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        <div class="mt-3">
                            <img src="{{ $formulas['default_lente'] }}" alt="Lente" loading="lazy" class="h-16 w-20 object-contain sm:h-20 sm:w-24">
                            <div class="text-xs text-zinc-600">{{ $formulas['default_lente_uploaded_name'] ?? 'manual/por defecto' }}</div>
                        </div>
                        <label class="mt-2 inline-flex items-center gap-2 text-xs"><input type="checkbox" name="remove_default_lente_image" value="1"> Quitar imagen por defecto</label>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                        <label class="text-sm font-semibold text-zinc-800">Progresivos</label>
                        <p class="text-xs text-zinc-500">Sube únicamente el icono (miniatura) que será el que se muestre en la selección.</p>
                        <input type="file" name="progresivo_icon_image" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">
                        <label class="mt-3 block text-xs font-medium text-zinc-700" for="progresivo_description_text">Texto mostrado al usuario</label>
                        <textarea id="progresivo_description_text" name="progresivo_description_text" rows="4" maxlength="400" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm">{{ old('progresivo_description_text', $formulas['progresivo_description']) }}</textarea>
                        <p class="mt-1 text-[11px] text-zinc-500">Máximo 400 caracteres.</p>
                        <div class="mt-3">
                            <img src="{{ $formulas['progresivo_icon'] }}" alt="Icono progresivo" loading="lazy" class="h-16 w-20 object-contain sm:h-20 sm:w-24">
                        </div>
                        <label class="mt-2 inline-flex items-center gap-2 text-xs"><input type="checkbox" name="remove_progresivo_icon_image" value="1"> Quitar icono</label>
                    </div>

                    <div class="lg:col-span-2 rounded-2xl border border-zinc-200 bg-white p-4">
                        <h4 class="text-sm font-semibold text-zinc-900">Sección: Bifocales</h4>
                        <p class="text-xs text-zinc-500 mt-1">Sube un icono para cada variante listada.</p>

                        @php
                            $bifocalVariantCards = [
                                [
                                    'title' => 'Blanco',
                                    'input' => 'bifocal_blanco_icon',
                                    'description_input' => 'bifocal_blanco_description_text',
                                    'description_key' => 'bifocal_blanco_description',
                                    'image' => 'bifocal_blanco_icon',
                                    'remove' => 'remove_bifocal_blanco_icon_image',
                                    'color' => 'text-zinc-700',
                                    'alt' => 'Blanco',
                                ],
                                [
                                    'title' => 'AR Azul',
                                    'input' => 'bifocal_ar_azul_icon',
                                    'description_input' => 'bifocal_ar_azul_description_text',
                                    'description_key' => 'bifocal_ar_azul_description',
                                    'image' => 'bifocal_ar_azul_icon',
                                    'remove' => 'remove_bifocal_ar_azul_icon_image',
                                    'color' => 'text-sky-600',
                                    'alt' => 'AR Azul',
                                ],
                                [
                                    'title' => 'AR Verde',
                                    'input' => 'bifocal_ar_verde_icon',
                                    'description_input' => 'bifocal_ar_verde_description_text',
                                    'description_key' => 'bifocal_ar_verde_description',
                                    'image' => 'bifocal_ar_verde_icon',
                                    'remove' => 'remove_bifocal_ar_verde_icon_image',
                                    'color' => 'text-emerald-600',
                                    'alt' => 'AR Verde',
                                ],
                                [
                                    'title' => 'AR azul + Fotocromático + Blue Block',
                                    'input' => 'bifocal_ar_azul_foto_blue_icon',
                                    'description_input' => 'bifocal_ar_azul_foto_blue_description_text',
                                    'description_key' => 'bifocal_ar_azul_foto_blue_description',
                                    'image' => 'bifocal_ar_azul_foto_blue_icon',
                                    'remove' => 'remove_bifocal_ar_azul_foto_blue_icon_image',
                                    'color' => 'text-sky-600',
                                    'alt' => 'AR azul foto',
                                ],
                                [
                                    'title' => 'AR verde + Fotocromático + Blue Block',
                                    'input' => 'bifocal_ar_verde_foto_blue_icon',
                                    'description_input' => 'bifocal_ar_verde_foto_blue_description_text',
                                    'description_key' => 'bifocal_ar_verde_foto_blue_description',
                                    'image' => 'bifocal_ar_verde_foto_blue_icon',
                                    'remove' => 'remove_bifocal_ar_verde_foto_blue_icon_image',
                                    'color' => 'text-emerald-600',
                                    'alt' => 'AR verde foto',
                                ],
                                [
                                    'title' => '1.59 Transitions Gens',
                                    'input' => 'bifocal_159_transitions_icon',
                                    'description_input' => 'bifocal_159_transitions_description_text',
                                    'description_key' => 'bifocal_159_transitions_description',
                                    'image' => 'bifocal_159_transitions_icon',
                                    'remove' => 'remove_bifocal_159_transitions_icon_image',
                                    'color' => 'text-zinc-700',
                                    'alt' => '1.59',
                                ],
                                [
                                    'title' => 'Bifocal 1.59 Blanco',
                                    'input' => 'bifocal_159_blanco_icon',
                                    'description_input' => 'bifocal_159_blanco_description_text',
                                    'description_key' => 'bifocal_159_blanco_description',
                                    'image' => 'bifocal_159_blanco_icon',
                                    'remove' => 'remove_bifocal_159_blanco_icon_image',
                                    'color' => 'text-zinc-700',
                                    'alt' => '1.59 Blanco',
                                ],
                                [
                                    'title' => 'Bifocal 1.59 AR Verde',
                                    'input' => 'bifocal_159_ar_verde_icon',
                                    'description_input' => 'bifocal_159_ar_verde_description_text',
                                    'description_key' => 'bifocal_159_ar_verde_description',
                                    'image' => 'bifocal_159_ar_verde_icon',
                                    'remove' => 'remove_bifocal_159_ar_verde_icon_image',
                                    'color' => 'text-emerald-600',
                                    'alt' => '1.59 AR Verde',
                                ],
                                [
                                    'title' => 'Bifocal 1.59 Blue Block',
                                    'input' => 'bifocal_159_blue_block_icon',
                                    'description_input' => 'bifocal_159_blue_block_description_text',
                                    'description_key' => 'bifocal_159_blue_block_description',
                                    'image' => 'bifocal_159_blue_block_icon',
                                    'remove' => 'remove_bifocal_159_blue_block_icon_image',
                                    'color' => 'text-sky-500',
                                    'alt' => 'Blue Block',
                                ],
                                [
                                    'title' => 'Bifocal 1.59 Fotocromático + AR + Blue Block',
                                    'input' => 'bifocal_159_foto_ar_blue_icon',
                                    'description_input' => 'bifocal_159_foto_ar_blue_description_text',
                                    'description_key' => 'bifocal_159_foto_ar_blue_description',
                                    'image' => 'bifocal_159_foto_ar_blue_icon',
                                    'remove' => 'remove_bifocal_159_foto_ar_blue_icon_image',
                                    'color' => 'text-zinc-700',
                                    'alt' => 'Fotocromatico',
                                ],
                                [
                                    'title' => 'Bifocal 1.59 AR verde + Fotocromático + Blue Block',
                                    'input' => 'bifocal_159_ar_verde_foto_blue_icon',
                                    'description_input' => 'bifocal_159_ar_verde_foto_blue_description_text',
                                    'description_key' => 'bifocal_159_ar_verde_foto_blue_description',
                                    'image' => 'bifocal_159_ar_verde_foto_blue_icon',
                                    'remove' => 'remove_bifocal_159_ar_verde_foto_blue_icon_image',
                                    'color' => 'text-emerald-600',
                                    'alt' => 'AR verde foto',
                                ],
                            ];
                        @endphp

                        <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($bifocalVariantCards as $card)
                                <div class="rounded-2xl border border-zinc-100 p-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 {{ $card['color'] }}">
                                            <svg class="h-10 w-10" viewBox="0 0 80 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <ellipse cx="40" cy="24" rx="29" ry="16" stroke="currentColor" stroke-width="2.5"></ellipse>
                                                <path d="M11 24C11 35.0457 24.4315 44 40 44C55.5685 44 69 35.0457 69 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold">{{ $card['title'] }}</div>
                                            <input type="file" name="{{ $card['input'] }}" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-3 py-2 text-sm">
                                            <textarea name="{{ $card['description_input'] }}" rows="4" maxlength="400" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-3 py-2 text-sm">{{ old($card['description_input'], $formulas[$card['description_key']]) }}</textarea>
                                            <div class="mt-1 text-[11px] text-zinc-500">Texto del modulo. Maximo 400 caracteres.</div>
                                            <div class="mt-2"><img src="{{ $formulas[$card['image']] }}" class="h-12 w-14 object-contain" alt="{{ $card['alt'] }}"></div>
                                            <label class="mt-1 inline-flex items-center gap-2 text-xs"><input type="checkbox" name="{{ $card['remove'] }}" value="1"> Quitar icono</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="lg:col-span-2 rounded-2xl border border-zinc-200 bg-white p-4">
                        <h4 class="text-sm font-semibold text-zinc-900">Categoría Nara para Progresivos</h4>
                        <p class="text-xs text-zinc-500 mt-1">Sube una imagen/icono para cada nivel Nara. Estas imágenes se usan en la selección de lentes progresivos.</p>

                        <div class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div class="text-center">
                                <div class="mb-2 h-14 w-16 rounded-full bg-center bg-cover shadow-inner mx-auto" style="background-image: url('{{ $formulas['nara_basica'] ?? url('/image/narabasico.png') }}');"></div>
                                <div class="text-sm font-semibold">Nara básica</div>
                                <input type="file" name="nara_basica_image" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-3 py-2 text-sm">
                                <label class="mt-1 inline-flex items-center gap-2 text-xs"><input type="checkbox" name="remove_nara_basica_image" value="1"> Quitar</label>
                            </div>

                            <div class="text-center">
                                <div class="mb-2 h-14 w-16 rounded-full bg-center bg-cover shadow-inner mx-auto" style="background-image: url('{{ $formulas['nara_media'] ?? url('/image/naramedia.png') }}');"></div>
                                <div class="text-sm font-semibold">Nara media</div>
                                <input type="file" name="nara_media_image" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-3 py-2 text-sm">
                                <label class="mt-1 inline-flex items-center gap-2 text-xs"><input type="checkbox" name="remove_nara_media_image" value="1"> Quitar</label>
                            </div>

                            <div class="text-center">
                                <div class="mb-2 h-14 w-16 rounded-full bg-center bg-cover shadow-inner mx-auto" style="background-image: url('{{ $formulas['nara_alta'] ?? url('/image/naraAlta.png') }}');"></div>
                                <div class="text-sm font-semibold">Nara alta</div>
                                <input type="file" name="nara_alta_image" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-3 py-2 text-sm">
                                <label class="mt-1 inline-flex items-center gap-2 text-xs"><input type="checkbox" name="remove_nara_alta_image" value="1"> Quitar</label>
                            </div>

                            <div class="text-center">
                                <div class="mb-2 h-14 w-16 rounded-full bg-center bg-cover shadow-inner mx-auto" style="background-image: url('{{ $formulas['nara_premium'] ?? url('/image/naraPREMIUM.png') }}');"></div>
                                <div class="text-sm font-semibold">Nara premium</div>
                                <input type="file" name="nara_premium_image" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-3 py-2 text-sm">
                                <label class="mt-1 inline-flex items-center gap-2 text-xs"><input type="checkbox" name="remove_nara_premium_image" value="1"> Quitar</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50/60 p-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 text-sky-600">
                                <svg class="h-10 w-10" viewBox="0 0 80 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <ellipse cx="40" cy="24" rx="29" ry="16" stroke="currentColor" stroke-width="2.5"></ellipse>
                                    <path d="M11 24C11 35.0457 24.4315 44 40 44C55.5685 44 69 35.0457 69 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-semibold">Nara Premium 1.60</div>
                                <div class="text-xs text-zinc-500">Fotocromático + AR azul + Blue Block</div>
                                <div class="mt-2"><img src="{{ $formulas['bifocal_ar_azul_foto_blue_icon'] }}" class="h-12 w-14 object-contain" alt="Nara premium AR azul foto"></div>
                                <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 AR azul + Fotocromático + Blue Block.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 rounded-2xl border border-zinc-200 bg-white p-4">
                    <h4 class="text-sm font-semibold text-zinc-900">Sección: Nara Básica 1.56</h4>
                    <p class="mt-1 text-xs text-zinc-500">Estas opciones reutilizan exactamente las mismas imágenes cargadas en Bifocales 1.56. No se duplican archivos: cualquier cambio arriba se refleja aquí.</p>

                    <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-2xl border border-zinc-100 p-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 text-zinc-700">
                                    <svg class="h-10 w-10" viewBox="0 0 80 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <ellipse cx="40" cy="24" rx="29" ry="16" stroke="currentColor" stroke-width="2.5"></ellipse>
                                        <path d="M11 24C11 35.0457 24.4315 44 40 44C55.5685 44 69 35.0457 69 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold">Blanco</div>
                                    <div class="mt-2"><img src="{{ $formulas['bifocal_blanco_icon'] }}" class="h-12 w-14 object-contain" alt="Nara básica blanco"></div>
                                    <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 Blanco</div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-zinc-100 p-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 text-sky-600">
                                    <svg class="h-10 w-10" viewBox="0 0 80 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <ellipse cx="40" cy="24" rx="29" ry="16" stroke="currentColor" stroke-width="2.5"></ellipse>
                                        <path d="M11 24C11 35.0457 24.4315 44 40 44C55.5685 44 69 35.0457 69 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold">AR Azul</div>
                                    <div class="mt-2"><img src="{{ $formulas['bifocal_ar_azul_icon'] }}" class="h-12 w-14 object-contain" alt="Nara básica AR azul"></div>
                                    <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 AR Azul</div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-zinc-100 p-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 text-emerald-600">
                                    <svg class="h-10 w-10" viewBox="0 0 80 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <ellipse cx="40" cy="24" rx="29" ry="16" stroke="currentColor" stroke-width="2.5"></ellipse>
                                        <path d="M11 24C11 35.0457 24.4315 44 40 44C55.5685 44 69 35.0457 69 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold">AR Verde</div>
                                    <div class="mt-2"><img src="{{ $formulas['bifocal_ar_verde_icon'] }}" class="h-12 w-14 object-contain" alt="Nara básica AR verde"></div>
                                    <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 AR Verde</div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-zinc-100 p-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 text-sky-600">
                                    <svg class="h-10 w-10" viewBox="0 0 80 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <ellipse cx="40" cy="24" rx="29" ry="16" stroke="currentColor" stroke-width="2.5"></ellipse>
                                        <path d="M11 24C11 35.0457 24.4315 44 40 44C55.5685 44 69 35.0457 69 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold">AR azul + Fotocromático + Blue Block</div>
                                    <div class="mt-2"><img src="{{ $formulas['bifocal_ar_azul_foto_blue_icon'] }}" class="h-12 w-14 object-contain" alt="Nara básica AR azul foto"></div>
                                    <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 AR azul + Fotocromático + Blue Block</div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-zinc-100 p-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 text-emerald-600">
                                    <svg class="h-10 w-10" viewBox="0 0 80 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <ellipse cx="40" cy="24" rx="29" ry="16" stroke="currentColor" stroke-width="2.5"></ellipse>
                                        <path d="M11 24C11 35.0457 24.4315 44 40 44C55.5685 44 69 35.0457 69 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold">AR verde + Fotocromático + Blue Block</div>
                                    <div class="mt-2"><img src="{{ $formulas['bifocal_ar_verde_foto_blue_icon'] }}" class="h-12 w-14 object-contain" alt="Nara básica AR verde foto"></div>
                                    <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 AR verde + Fotocromático + Blue Block</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 rounded-2xl border border-zinc-200 bg-white p-4">
                    <h4 class="text-sm font-semibold text-zinc-900">Sección: Alto índice 1.67 y 1.74</h4>
                    <p class="mt-1 text-xs text-zinc-500">Estas dos secciones reutilizan las mismas imágenes base ya cargadas en Bifocales 1.56. No crean archivos nuevos: solo dirigen cada opción al icono compartido correspondiente.</p>

                    <div class="mt-3 grid gap-6 lg:grid-cols-2">
                        <div>
                            <p class="text-sm font-semibold text-zinc-900">1.67</p>
                            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-zinc-100 p-3">
                                    <div class="text-sm font-semibold">AR azul</div>
                                    <div class="mt-2"><img src="{{ $formulas['bifocal_ar_azul_icon'] }}" class="h-12 w-14 object-contain" alt="Alto indice 1.67 AR azul"></div>
                                    <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 AR azul</div>
                                </div>
                                <div class="rounded-2xl border border-zinc-100 p-3">
                                    <div class="text-sm font-semibold">AR verde</div>
                                    <div class="mt-2"><img src="{{ $formulas['bifocal_ar_verde_icon'] }}" class="h-12 w-14 object-contain" alt="Alto indice 1.67 AR verde"></div>
                                    <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 AR verde</div>
                                </div>
                                <div class="rounded-2xl border border-zinc-100 p-3">
                                    <div class="text-sm font-semibold">AR azul + Fotocromático + Blue Block</div>
                                    <div class="mt-2"><img src="{{ $formulas['bifocal_ar_azul_foto_blue_icon'] }}" class="h-12 w-14 object-contain" alt="Alto indice 1.67 AR azul foto"></div>
                                    <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 AR azul + Fotocromático + Blue Block</div>
                                </div>
                                <div class="rounded-2xl border border-zinc-100 p-3">
                                    <div class="text-sm font-semibold">AR verde + Fotocromático + Blue Block</div>
                                    <div class="mt-2"><img src="{{ $formulas['bifocal_ar_verde_foto_blue_icon'] }}" class="h-12 w-14 object-contain" alt="Alto indice 1.67 AR verde foto"></div>
                                    <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 AR verde + Fotocromático + Blue Block</div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-zinc-900">1.74</p>
                            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-zinc-100 p-3">
                                    <div class="text-sm font-semibold">AR azul</div>
                                    <div class="mt-2"><img src="{{ $formulas['bifocal_ar_azul_icon'] }}" class="h-12 w-14 object-contain" alt="Alto indice 1.74 AR azul"></div>
                                    <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 AR azul</div>
                                </div>
                                <div class="rounded-2xl border border-zinc-100 p-3">
                                    <div class="text-sm font-semibold">AR verde</div>
                                    <div class="mt-2"><img src="{{ $formulas['bifocal_ar_verde_icon'] }}" class="h-12 w-14 object-contain" alt="Alto indice 1.74 AR verde"></div>
                                    <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 AR verde</div>
                                </div>
                                <div class="rounded-2xl border border-zinc-100 p-3">
                                    <div class="text-sm font-semibold">AR azul + Fotocromático + Blue Block</div>
                                    <div class="mt-2"><img src="{{ $formulas['bifocal_ar_azul_foto_blue_icon'] }}" class="h-12 w-14 object-contain" alt="Alto indice 1.74 AR azul foto"></div>
                                    <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 AR azul + Fotocromático + Blue Block</div>
                                </div>
                                <div class="rounded-2xl border border-zinc-100 p-3">
                                    <div class="text-sm font-semibold">AR verde + Fotocromático + Blue Block</div>
                                    <div class="mt-2"><img src="{{ $formulas['bifocal_ar_verde_foto_blue_icon'] }}" class="h-12 w-14 object-contain" alt="Alto indice 1.74 AR verde foto"></div>
                                    <div class="mt-1 text-xs text-zinc-500">Comparte la imagen con Bifocales 1.56 AR verde + Fotocromático + Blue Block</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" class="rounded-2xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-900">Guardar imágenes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

