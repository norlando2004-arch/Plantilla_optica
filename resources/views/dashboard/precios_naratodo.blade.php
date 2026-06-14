@extends('layouts.dashboard_empty_sidebar')

@section('title', 'PreciosOptica')
@section('heading', 'PreciosOptica')

@section('content')
    <div class="rounded-3xl border border-zinc-200 bg-white p-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Configuración</p>
            <h2 class="mt-1 text-lg font-semibold text-zinc-900">PRECIOS</h2>
            <p class="mt-1 text-sm text-zinc-600">Edita los precios por tipo de lente. Solo Progresivos usa niveles NARA.</p>
        </div>

        <form method="POST" action="{{ route('dashboard.precios-naratodo.update') }}" class="mt-6 space-y-6">
            @csrf
            @method('PUT')

            <section class="rounded-2xl border border-zinc-200 p-4">
                <h3 class="text-base font-semibold text-zinc-900">Progresivos</h3>
                <p class="mt-1 text-sm text-zinc-600">Lentes progresivos (digitales NARA).</p>
                <div class="mt-4 overflow-x-auto rounded-2xl border border-zinc-200">
                    <table class="w-full min-w-[52rem] border-collapse bg-white">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Marca Optica</th>
                                @foreach($naraOptions as $naraLabel)
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ $naraLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($progresivosLensTypes as $lensKey)
                                @continue(!isset($lensTypeOptions[$lensKey]))
                                <tr class="border-t border-zinc-200">
                                    <td class="px-4 py-3 text-sm font-semibold text-zinc-900">{{ $lensTypeOptions[$lensKey] }}</td>
                                    @foreach($naraOptions as $naraKey => $naraLabel)
                                        @php
                                            $raw = old("prices.$lensKey.$naraKey", $matrix[$lensKey][$naraKey] ?? 0);
                                            $num = (int) preg_replace('/\D+/', '', (string) $raw);
                                            $display = number_format($num, 0, '', '.');
                                        @endphp
                                        <td class="px-3 py-2">
                                            <div class="flex items-center gap-2 rounded-xl border border-zinc-200 px-3 py-2">
                                                <span class="text-xs font-semibold text-zinc-500">$</span>
                                                <input name="prices[{{ $lensKey }}][{{ $naraKey }}]" value="{{ $display }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border border-zinc-200 p-4">
                <h3 class="text-base font-semibold text-zinc-900">Monofocal</h3>
                <p class="mt-1 text-sm text-zinc-600">Estos precios funcionan según la fórmula del cliente.</p>

                @php
                    $monoBasica156 = [
                        '156_blanco',
                        '156_blue_block',
                        '156_ar_verde',
                        '156_fotocromatico_superhidrofobico',
                        '156_ar_verde_fotocromatico_blue_block',
                    ];
                @endphp

                <div class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50/60 p-3">
                    <p class="text-sm font-semibold text-zinc-900">Nara Basica 1.56</p>
                    <div class="mt-3 overflow-x-auto rounded-2xl border border-zinc-200 bg-white">
                        <table class="w-full min-w-[52rem] border-collapse">
                            <thead class="bg-zinc-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Tipo</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Precio 1 (hasta +/-3)</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Precio 2 (hasta +/-4)</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Precio 3 (mayor a +/-4)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monoBasica156 as $lensKey)
                                    @continue(!isset($monofocalTiered[$lensKey]) || !isset($lensTypeOptions[$lensKey]))
                                    @php
                                        $tiers = $monofocalTiered[$lensKey];
                                    @endphp
                                    <tr class="border-t border-zinc-200">
                                        <td class="px-4 py-3 text-sm font-semibold text-zinc-900">{{ $lensTypeOptions[$lensKey] }}</td>
                                        @foreach(['tier1', 'tier2', 'tier3'] as $tierKey)
                                            @php
                                                $raw = old("monofocal_tiered.$lensKey.$tierKey", $tiers[(int) str_replace('tier', '', $tierKey)] ?? 0);
                                                $num = (int) preg_replace('/\D+/', '', (string) $raw);
                                                $display = number_format($num, 0, '', '.');
                                            @endphp
                                            <td class="px-3 py-2">
                                                <div class="flex items-center gap-2 rounded-xl border border-zinc-200 px-3 py-2">
                                                    <span class="text-xs font-semibold text-zinc-500">$</span>
                                                    <input name="monofocal_tiered[{{ $lensKey }}][{{ $tierKey }}]" value="{{ $display }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50/60 p-3">
                    <p class="text-sm font-semibold text-zinc-900">Nara Premium 1.60</p>
                    <div class="mt-3 overflow-x-auto rounded-2xl border border-zinc-200 bg-white">
                        <table class="w-full min-w-[42rem] border-collapse">
                            <thead class="bg-zinc-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Tipo</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Precio 1 (hasta +/-4)</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Precio 2 (mayor a +/-4)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $lensKey = '160_premium';
                                    $tiers = $monofocalTiered[$lensKey] ?? [1 => 0, 2 => 0, 3 => 0];
                                @endphp
                                <tr class="border-t border-zinc-200">
                                    <td class="px-4 py-3 text-sm font-semibold text-zinc-900">Fotocromatico + AR azul + Blue Block</td>
                                    @foreach(['tier1', 'tier2'] as $tierKey)
                                        @php
                                            $raw = old("monofocal_tiered.$lensKey.$tierKey", $tiers[(int) str_replace('tier', '', $tierKey)] ?? 0);
                                            $num = (int) preg_replace('/\D+/', '', (string) $raw);
                                            $display = number_format($num, 0, '', '.');
                                        @endphp
                                        <td class="px-3 py-2">
                                            <div class="flex items-center gap-2 rounded-xl border border-zinc-200 px-3 py-2">
                                                <span class="text-xs font-semibold text-zinc-500">$</span>
                                                <input name="monofocal_tiered[{{ $lensKey }}][{{ $tierKey }}]" value="{{ $display }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                        @php
                            $tier3Raw = old("monofocal_tiered.$lensKey.tier3", old("monofocal_tiered.$lensKey.tier2", $tiers[2] ?? 0));
                        @endphp
                        <input type="hidden" name="monofocal_tiered[{{ $lensKey }}][tier3]" value="{{ $tier3Raw }}">
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50/60 p-3">
                    <p class="text-sm font-semibold text-zinc-900">Alto indice</p>
                    <div class="mt-3 overflow-x-auto rounded-2xl border border-zinc-200 bg-white">
                        <table class="w-full min-w-[52rem] border-collapse">
                            <thead class="bg-zinc-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Tipo</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">1.67</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">1.74</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $altoIndiceRows = [
                                        ['label' => 'AR Verde', 'k167' => '167_ar_verde', 'k174' => '174_ar_verde'],
                                        ['label' => 'Blue Block', 'k167' => '167_blue_block', 'k174' => '174_blue_block'],
                                        ['label' => 'AR azul + Fotocromatico + Blue Block', 'k167' => '167_ar_azul_fotocromatico_blue_block', 'k174' => '174_ar_azul_fotocromatico_blue_block'],
                                        ['label' => 'AR verde + Fotocromatico + Blue Block', 'k167' => '167_ar_verde_fotocromatico_blue_block', 'k174' => '174_ar_verde_fotocromatico_blue_block'],
                                    ];
                                @endphp
                                @foreach($altoIndiceRows as $row)
                                    @php
                                        $raw167 = old("monofocal_fixed.{$row['k167']}", $monofocalFixed[$row['k167']] ?? 0);
                                        $raw174 = old("monofocal_fixed.{$row['k174']}", $monofocalFixed[$row['k174']] ?? 0);
                                        $d167 = number_format((int) preg_replace('/\D+/', '', (string) $raw167), 0, '', '.');
                                        $d174 = number_format((int) preg_replace('/\D+/', '', (string) $raw174), 0, '', '.');
                                    @endphp
                                    <tr class="border-t border-zinc-200">
                                        <td class="px-4 py-3 text-sm font-semibold text-zinc-900">{{ $row['label'] }}</td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center gap-2 rounded-xl border border-zinc-200 px-3 py-2">
                                                <span class="text-xs font-semibold text-zinc-500">$</span>
                                                <input name="monofocal_fixed[{{ $row['k167'] }}]" value="{{ $d167 }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex items-center gap-2 rounded-xl border border-zinc-200 px-3 py-2">
                                                <span class="text-xs font-semibold text-zinc-500">$</span>
                                                <input name="monofocal_fixed[{{ $row['k174'] }}]" value="{{ $d174 }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto rounded-2xl border border-zinc-200">
                    <table class="w-full min-w-[52rem] border-collapse bg-white">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Lente</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Hasta +/-3.00</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Hasta +/-4.00</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Mayor a +/-4.00</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monofocalTiered as $lensKey => $tiers)
                                @continue(!isset($lensTypeOptions[$lensKey]))
                                <tr class="border-t border-zinc-200">
                                    <td class="px-4 py-3 text-sm font-semibold text-zinc-900">{{ $lensTypeOptions[$lensKey] }}</td>
                                    @foreach(['tier1', 'tier2', 'tier3'] as $tierKey)
                                        @php
                                            $raw = old("monofocal_tiered.$lensKey.$tierKey", $tiers[(int) str_replace('tier', '', $tierKey)] ?? 0);
                                            $num = (int) preg_replace('/\D+/', '', (string) $raw);
                                            $display = number_format($num, 0, '', '.');
                                        @endphp
                                        <td class="px-3 py-2">
                                            <div class="flex items-center gap-2 rounded-xl border border-zinc-200 px-3 py-2">
                                                <span class="text-xs font-semibold text-zinc-500">$</span>
                                                <input name="monofocal_tiered[{{ $lensKey }}][{{ $tierKey }}]" value="{{ $display }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 overflow-x-auto rounded-2xl border border-zinc-200">
                    <table class="w-full min-w-[42rem] border-collapse bg-white">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Transitions 1.59</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Sin color (rango bajo)</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Sin color (rango alto)</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Con color</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-t border-zinc-200">
                                <td class="px-4 py-3 text-sm font-semibold text-zinc-900">1.59 Transitions Gens</td>
                                @foreach(['tier1', 'tier2', 'with_color'] as $key)
                                    @php
                                        $raw = old("monofocal_transitions.$key", $monofocalTransitions[$key] ?? 0);
                                        $num = (int) preg_replace('/\D+/', '', (string) $raw);
                                        $display = number_format($num, 0, '', '.');
                                    @endphp
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-2 rounded-xl border border-zinc-200 px-3 py-2">
                                            <span class="text-xs font-semibold text-zinc-500">$</span>
                                            <input name="monofocal_transitions[{{ $key }}]" value="{{ $display }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border border-zinc-200 p-4">
                <h3 class="text-base font-semibold text-zinc-900">Bifocal</h3>
                <div class="mt-4 overflow-x-auto rounded-2xl border border-zinc-200">
                    <table class="w-full min-w-[42rem] border-collapse bg-white">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Lente</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Precio fijo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bifocalFixed as $lensKey => $value)
                                @continue(!isset($lensTypeOptions[$lensKey]))
                                @php
                                    $raw = old("bifocal_fixed.$lensKey", $value);
                                    $num = (int) preg_replace('/\D+/', '', (string) $raw);
                                    $display = number_format($num, 0, '', '.');
                                @endphp
                                <tr class="border-t border-zinc-200">
                                    <td class="px-4 py-3 text-sm font-semibold text-zinc-900">{{ $lensTypeOptions[$lensKey] }}</td>
                                    <td class="px-3 py-2">
                                        <div class="mx-auto flex w-full max-w-[14rem] items-center gap-2 rounded-xl border border-zinc-200 px-3 py-2">
                                            <span class="text-xs font-semibold text-zinc-500">$</span>
                                            <input name="bifocal_fixed[{{ $lensKey }}]" value="{{ $display }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border border-zinc-200 p-4">
                <h3 class="text-base font-semibold text-zinc-900">Ocupacional</h3>
                <div class="mt-4 overflow-x-auto rounded-2xl border border-zinc-200">
                    <table class="w-full min-w-[42rem] border-collapse bg-white">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Lente</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Precio fijo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ocupacionalFixed as $lensKey => $value)
                                @continue(!isset($lensTypeOptions[$lensKey]))
                                @php
                                    $raw = old("ocupacional_fixed.$lensKey", $value);
                                    $num = (int) preg_replace('/\D+/', '', (string) $raw);
                                    $display = number_format($num, 0, '', '.');
                                @endphp
                                <tr class="border-t border-zinc-200">
                                    <td class="px-4 py-3 text-sm font-semibold text-zinc-900">{{ $lensTypeOptions[$lensKey] }}</td>
                                    <td class="px-3 py-2">
                                        <div class="mx-auto flex w-full max-w-[14rem] items-center gap-2 rounded-xl border border-zinc-200 px-3 py-2">
                                            <span class="text-xs font-semibold text-zinc-500">$</span>
                                            <input name="ocupacional_fixed[{{ $lensKey }}]" value="{{ $display }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border border-zinc-200 bg-emerald-50/40 p-4">
                <h3 class="text-base font-semibold text-zinc-900">Sin fórmula</h3>
                <p class="mt-1 text-sm text-zinc-600">Seleccionar</p>
                @php
                    $sinFormulaRows = [
                        ['label' => 'Blanco', 'key' => '156_blanco'],
                        ['label' => 'AR Azul', 'key' => '156_blue_block'],
                        ['label' => 'AR verde', 'key' => '156_ar_verde'],
                        ['label' => 'AR azul + Fotocromatico + Blue Block', 'key' => '156_fotocromatico_superhidrofobico'],
                        ['label' => 'AR verde + Fotocromatico + Blue Block', 'key' => '156_ar_verde_fotocromatico_blue_block'],
                    ];
                @endphp
                <div class="mt-3 space-y-2">
                    @foreach($sinFormulaRows as $row)
                        @php
                            $raw = old("no_formula_prices.{$row['key']}", $noFormulaFixed[$row['key']] ?? 0);
                            $num = (int) preg_replace('/\D+/', '', (string) $raw);
                            $display = number_format($num, 0, '', '.');
                        @endphp
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm">
                            <span class="font-medium text-zinc-800">{{ $row['label'] }}</span>
                            <div class="flex w-full max-w-[12rem] items-center gap-2 rounded-xl border border-zinc-200 px-3 py-2">
                                <span class="text-xs font-semibold text-zinc-500">$</span>
                                <input name="no_formula_prices[{{ $row['key'] }}]" value="{{ $display }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-2xl border border-zinc-200 bg-cyan-50/40 p-4">
                <h3 class="text-base font-semibold text-zinc-900">Poly</h3>
                <p class="mt-1 text-sm text-zinc-600">Aplica solo a monturas con compatibilidad Poly = Sí.</p>

                @php
                    $polyRows = [
                        ['label' => 'Blanco', 'key' => '156_blanco'],
                        ['label' => 'Ar verde', 'key' => '156_ar_verde'],
                        ['label' => 'Blue block', 'key' => '156_blue_block'],
                        ['label' => 'Fotoverde', 'key' => '156_ar_verde_fotocromatico_blue_block'],
                        ['label' => 'Fotoblue', 'key' => '156_fotocromatico_superhidrofobico'],
                    ];
                @endphp

                <div class="mt-4 overflow-x-auto rounded-2xl border border-zinc-200">
                    <table class="w-full min-w-[62rem] border-collapse bg-white">
                        <thead class="bg-zinc-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Poly</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Hasta +-3 en esfera y cilindro -2</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Hasta esfera +-4 y cilindro -4</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Después de esfera +-4 cilindro -4</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Después de +-9 en esfera y cilindro</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($polyRows as $row)
                                @php
                                    $key = $row['key'];
                                    $tiers = $polyTiered[$key] ?? [1 => 0, 2 => 0, 3 => 0, 4 => 0];
                                @endphp
                                <tr class="border-t border-zinc-200">
                                    <td class="px-4 py-3 text-sm font-semibold text-zinc-900">{{ $row['label'] }}</td>
                                    @foreach(['tier1', 'tier2', 'tier3', 'tier4'] as $tierKey)
                                        @php
                                            $raw = old("poly_tiered.$key.$tierKey", $tiers[(int) str_replace('tier', '', $tierKey)] ?? 0);
                                            $num = (int) preg_replace('/\D+/', '', (string) $raw);
                                            $display = number_format($num, 0, '', '.');
                                        @endphp
                                        <td class="px-3 py-2">
                                            <div class="flex items-center gap-2 rounded-xl border border-zinc-200 px-3 py-2">
                                                <span class="text-xs font-semibold text-zinc-500">$</span>
                                                <input name="poly_tiered[{{ $key }}][{{ $tierKey }}]" value="{{ $display }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 rounded-2xl border border-zinc-200 bg-white p-3">
                    <p class="text-sm font-semibold text-zinc-900">Poly sin fórmula</p>
                    <div class="mt-3 space-y-2">
                        @foreach($polyRows as $row)
                            @php
                                $raw = old("poly_no_formula_prices.{$row['key']}", $polyNoFormula[$row['key']] ?? 0);
                                $num = (int) preg_replace('/\D+/', '', (string) $raw);
                                $display = number_format($num, 0, '', '.');
                            @endphp
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm">
                                <span class="font-medium text-zinc-800">{{ $row['label'] }}</span>
                                <div class="flex w-full max-w-[12rem] items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2">
                                    <span class="text-xs font-semibold text-zinc-500">$</span>
                                    <input name="poly_no_formula_prices[{{ $row['key'] }}]" value="{{ $display }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-zinc-200 bg-white p-3">
                    <p class="text-sm font-semibold text-zinc-900">Poly Progresivos (Nara POLY 1.59)</p>
                    <div class="mt-3 overflow-x-auto rounded-2xl border border-zinc-200">
                        <table class="w-full min-w-[52rem] border-collapse bg-white">
                            <thead class="bg-zinc-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Poly</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Basica</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Media</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500">Alta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $polyProgresivosRows = [
                                        ['label' => 'Nara POLY 1.59 Blanco', 'key' => '156_blanco'],
                                        ['label' => 'Nara POLY 1.59 AR Verde', 'key' => '156_ar_verde'],
                                        ['label' => 'Nara POLY 1.59 Blue Block', 'key' => '156_blue_block'],
                                        ['label' => 'Nara POLY 1.59 Fotocromatico', 'key' => '156_fotocromatico_superhidrofobico'],
                                        ['label' => 'Nara POLY 1.59 Transitions', 'key' => '159_transitions_gens'],
                                    ];
                                @endphp
                                @foreach($polyProgresivosRows as $row)
                                    @php
                                        $levels = $polyProgresivos[$row['key']] ?? ['basica' => 0, 'media' => 0, 'alta' => 0];
                                    @endphp
                                    <tr class="border-t border-zinc-200">
                                        <td class="px-4 py-3 text-sm font-semibold text-zinc-900">{{ $row['label'] }}</td>
                                        @foreach(['basica', 'media', 'alta'] as $levelKey)
                                            @php
                                                $raw = old("poly_progresivos_prices.{$row['key']}.$levelKey", $levels[$levelKey] ?? 0);
                                                $num = (int) preg_replace('/\D+/', '', (string) $raw);
                                                $display = number_format($num, 0, '', '.');
                                            @endphp
                                            <td class="px-3 py-2">
                                                <div class="flex items-center gap-2 rounded-xl border border-zinc-200 px-3 py-2">
                                                    <span class="text-xs font-semibold text-zinc-500">$</span>
                                                    <input name="poly_progresivos_prices[{{ $row['key'] }}][{{ $levelKey }}]" value="{{ $display }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-zinc-200 bg-white p-3">
                    <p class="text-sm font-semibold text-zinc-900">Poly Ocupacional</p>
                    <p class="mt-1 text-xs text-zinc-600">Se usa cuando la montura tiene Poly = Sí.</p>
                    <div class="mt-3 space-y-2">
                        @php
                            $polyOcupacionalRows = [
                                ['label' => 'Nara POLY 1.59 AR Verde', 'key' => '156_ar_verde'],
                                ['label' => 'Nara POLY 1.59 Blue Block', 'key' => '156_blue_block'],
                            ];
                        @endphp
                        @foreach($polyOcupacionalRows as $row)
                            @php
                                $raw = old("poly_ocupacional_fixed.{$row['key']}", $polyOcupacional[$row['key']] ?? 0);
                                $num = (int) preg_replace('/\D+/', '', (string) $raw);
                                $display = number_format($num, 0, '', '.');
                            @endphp
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm">
                                <span class="font-medium text-zinc-800">{{ $row['label'] }}</span>
                                <div class="flex w-full max-w-[12rem] items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2">
                                    <span class="text-xs font-semibold text-zinc-500">$</span>
                                    <input name="poly_ocupacional_fixed[{{ $row['key'] }}]" value="{{ $display }}" inputmode="numeric" pattern="[0-9.]*" class="w-full border-0 bg-transparent text-center text-sm font-semibold outline-none" data-price-input />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-900">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>

    <script>
        (() => {
            const inputs = Array.from(document.querySelectorAll('[data-price-input]'));
            if (!inputs.length) return;

            const toDigits = (value) => String(value || '').replace(/\D+/g, '');
            const fmt = new Intl.NumberFormat('es-CO', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            });

            const format = (value) => {
                const digits = toDigits(value);
                if (!digits) return '';
                const n = Number.parseInt(digits, 10);
                if (!Number.isFinite(n)) return '';
                return fmt.format(n);
            };

            inputs.forEach((input) => {
                // Formato inicial
                input.value = format(input.value);

                input.addEventListener('focus', () => {
                    input.value = toDigits(input.value);
                    // Colocar cursor al final
                    requestAnimationFrame(() => {
                        try {
                            input.setSelectionRange(input.value.length, input.value.length);
                        } catch {
                            // no-op
                        }
                    });
                });

                input.addEventListener('blur', () => {
                    input.value = format(input.value);
                });
            });
        })();
    </script>
@endsection
