<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pasarela (Dummy) — Óptica</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet">

    @php($viteHot = public_path('hot'))
    @php($viteManifest = public_path('build/manifest.json'))
    @php($hasViteAssets = file_exists($viteHot) || file_exists($viteManifest))

    @if($hasViteAssets)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @if(!$hasViteAssets || app()->isLocal())
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>

    <style>
        :root {
            --pay-bg: #f1f3f5;
            --pay-surface: #ffffff;
            --pay-text: #191e25;
            --pay-muted: #6b7280;
            --pay-blue-soft: #edf4ff;
            --pay-blue-strong: #112540;
            --pay-border: #dbe1ea;
            --pay-green: #2f7a45;
            --pay-red: #b04848;
        }

        .pay-shell {
            font-family: 'Montserrat', sans-serif;
            background:
                radial-gradient(1200px 420px at 20% -10%, #ffffff 0%, transparent 62%),
                radial-gradient(900px 360px at 100% 95%, #e8edf4 0%, transparent 70%),
                var(--pay-bg);
        }

        .pay-main-card {
            background: var(--pay-surface);
            border: 1px solid #e7ebf1;
            border-radius: 16px;
            box-shadow: 0 22px 44px -30px rgba(17, 24, 39, 0.42), 0 10px 18px -16px rgba(17, 24, 39, 0.35);
        }

        .pay-fade-up {
            animation: payFadeUp 380ms ease-out both;
        }

        .invoice-panel {
            border: 1px solid #d7dde6;
            border-radius: 14px;
            background: #ffffff;
        }

        .invoice-soft-blue {
            background: linear-gradient(180deg, #f7faff 0%, #eef4fc 100%);
        }

        @media print {
            @page {
                size: letter portrait;
                margin: 0.35in;
            }

            .no-print {
                display: none !important;
            }

            .pay-shell {
                background: #fff !important;
            }

            .pay-main-card {
                box-shadow: none;
                border-color: #d1d5db;
            }
        }

        @keyframes payFadeUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="pay-shell text-zinc-900 antialiased">
@include('partials.store-navbar')

<main class="mx-auto max-w-5xl px-4 py-6 sm:py-10 lg:py-12">
    <div class="mx-auto max-w-4xl pay-fade-up">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-[clamp(1.7rem,3.4vw,2.6rem)] font-extrabold leading-none tracking-[-0.01em]" style="color: var(--pay-text);">Resumen de Pago</h1>
            <p class="mt-2 text-sm font-medium text-zinc-500 sm:text-lg">Óptica Optica</p>
        </div>
        <a href="{{ route('pagos.show', $pago) }}" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 transition hover:bg-white/60 hover:text-zinc-900">
            <span>Volver</span>
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M3.75 10H16.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M11.75 5.5L16.25 10L11.75 14.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
    </div>
    <div id="js-invoice-download-area" class="pay-main-card p-4 sm:p-7">
        <div class="rounded-2xl border px-4 py-4 sm:px-5" style="border-color: #e2e8f0; background: #f7f9fc;">
            <p class="text-sm font-medium leading-relaxed text-zinc-700">Esto es una simulación para que tu flujo de “Pagar” funcione sin comprar todavía una pasarela real.</p>
        </div>

        @if($pago->estado === 'aprobado')
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Venta aprobada</p>
                <p class="mt-1 text-sm font-semibold text-emerald-900">Referencia: {{ (string) $pago->referencia }}</p>
                <p class="mt-1 text-sm text-emerald-900">Pagaste: <span class="font-semibold">{{ number_format((float)$pago->monto, 0, ',', '.') }} {{ $pago->moneda }}</span></p>
            </div>
        @elseif($pago->estado === 'rechazado')
            <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Pago rechazado</p>
                <p class="mt-1 text-sm font-semibold text-rose-900">Referencia: {{ (string) $pago->referencia }}</p>
                <p class="mt-1 text-sm text-rose-900">Monto: <span class="font-semibold">{{ number_format((float)$pago->monto, 0, ',', '.') }} {{ $pago->moneda }}</span></p>
            </div>
        @endif

        <div class="mt-6 rounded-2xl border p-4 sm:p-5" style="border-color: #a8b6cc; background: var(--pay-blue-soft);">
            <p class="text-xs font-semibold uppercase tracking-[0.06em] text-zinc-500">TOTAL</p>
            <p class="mt-1 text-[clamp(1.85rem,3.8vw,2.45rem)] font-extrabold leading-none" style="color: var(--pay-blue-strong);">{{ number_format((float)$pago->monto, 0, ',', '.') }} {{ $pago->moneda }}</p>
        </div>

        @php($items = $pago->carrito && $pago->carrito->items ? $pago->carrito->items : collect())
        @php($metaPago = is_array($pago->meta) ? $pago->meta : [])
        @php($metaCarrito = is_array($pago->carrito?->meta) ? $pago->carrito->meta : [])
        @php($guest = (array) ($metaPago['guest'] ?? $metaPago['cliente'] ?? $metaCarrito['cliente'] ?? []))
        @php($guestNombres = trim((string) ($guest['nombres'] ?? '')))
        @php($guestApellidos = trim((string) ($guest['apellidos'] ?? '')))
        @php($guestNombreCompleto = trim($guestNombres . ' ' . $guestApellidos))
        @php($guestNombreCliente = $guestNombreCompleto !== '' ? $guestNombreCompleto : trim((string) ($guest['nombre'] ?? '')))
        @php($guestNombreCliente = $guestNombreCliente !== '' ? $guestNombreCliente : 'Cliente')
        @php($invoiceRows = $items->map(function ($item) {
            $cantidad = (int) ($item->cantidad ?? 0);
            $precio = (float) ($item->precio_unitario ?? 0);
            return [
                'nombre' => (string) ($item->nombre_producto ?? 'Producto'),
                'cantidad' => $cantidad,
                'unitario' => $precio,
                'linea' => $precio * $cantidad,
                'moneda' => (string) ($item->moneda ?? 'COP'),
            ];
        }))
        @php($invoiceSubtotal = (float) $invoiceRows->sum('linea'))
        @php($invoiceCurrency = (string) ($pago->moneda ?? ($invoiceRows->first()['moneda'] ?? 'COP')))
        @php($invoiceSubtotal = $invoiceSubtotal > 0 ? $invoiceSubtotal : (float) $pago->monto)
        @php($invoiceTotal = (float) $pago->monto)
        @if($items->isNotEmpty())
            <div class="mt-6">
                <p class="text-[1.05rem] font-semibold text-zinc-900">Artículos</p>
                <div class="mt-3 grid gap-2">
                    @foreach($items as $item)
                        @php($linea = ((float) $item->precio_unitario) * ((int) $item->cantidad))
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-zinc-200 bg-white/70 px-3 py-3 sm:px-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="grid h-12 w-12 place-items-center rounded-xl border border-zinc-200 bg-zinc-100/80 text-zinc-600">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M3.8 13.4L5.2 9.6C5.55 8.62 6.49 7.96 7.53 7.96H9.25C10.31 7.96 11.25 8.65 11.58 9.66L12.04 11.05" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M20.2 13.4L18.8 9.6C18.45 8.62 17.51 7.96 16.47 7.96H14.75C13.69 7.96 12.75 8.65 12.42 9.66L11.96 11.05" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M2.8 12.3H8.2C9.41 12.3 10.4 13.29 10.4 14.5V14.5C10.4 17.04 8.34 19.1 5.8 19.1H5.2C2.66 19.1 0.6 17.04 0.6 14.5V14.5C0.6 13.29 1.59 12.3 2.8 12.3Z" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M15.8 12.3H21.2C22.41 12.3 23.4 13.29 23.4 14.5V14.5C23.4 17.04 21.34 19.1 18.8 19.1H18.2C15.66 19.1 13.6 17.04 13.6 14.5V14.5C13.6 13.29 14.59 12.3 15.8 12.3Z" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M10.5 14.1H13.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-base font-semibold text-zinc-900">{{ $item->nombre_producto }}</p>
                                    <p class="mt-0.5 text-sm text-zinc-500">x{{ (int) $item->cantidad }}</p>
                                </div>
                            </div>
                            <p class="shrink-0 text-lg font-semibold text-zinc-900">{{ number_format((float)$linea, 0, ',', '.') }} {{ $item->moneda }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if(in_array((string) $pago->estado, ['aprobado', 'rechazado'], true))
            @php($invoiceCode = 'COMP-PAGO-' . str_pad((string) $pago->id, 5, '0', STR_PAD_LEFT))
            <section class="mt-8 rounded-2xl border border-zinc-200 bg-white pt-4 sm:pt-5">
                <div class="px-4 sm:px-5">
                    <div class="text-center">
                        <p class="text-[clamp(1.9rem,3.6vw,2.35rem)] font-extrabold leading-none tracking-[-0.01em] text-zinc-900">Comprobante No. [{{ $invoiceCode }}]</p>
                        <p class="mt-1 text-[clamp(1.2rem,2.2vw,1.9rem)] font-medium leading-none text-zinc-700">Comprobante de pago</p>
                        <p class="mt-3 text-base text-zinc-700">Referencia: {{ (string) $pago->referencia }}</p>
                        <p class="mt-1 text-base text-zinc-600">Fecha: {{ optional($pago->updated_at ?? $pago->created_at)->format('Y-m-d H:i') }}</p>
                    </div>

                    <div class="invoice-panel mt-5 overflow-hidden">
                        <div class="grid sm:grid-cols-2">
                            <div class="border-b border-zinc-200 p-3 sm:border-b-0 sm:border-r">
                                <p class="border-b border-zinc-200 pb-1 text-xs font-semibold uppercase tracking-[0.08em] text-zinc-500">CLIENTE</p>
                                <p class="mt-2 text-[1.05rem] font-medium text-zinc-900">{{ $guestNombreCliente }}</p>
                                @if(!empty($guest['correo']))
                                    <p class="mt-1 text-[1.05rem] text-zinc-900">{{ $guest['correo'] }}</p>
                                @endif
                                <p class="mt-1 text-[1.05rem] text-zinc-900">{{ $guest['telefono'] ?? 'Sin teléfono registrado' }}</p>
                            </div>
                            <div class="p-3">
                                <p class="border-b border-zinc-200 pb-1 text-xs font-semibold uppercase tracking-[0.08em] text-zinc-500">DIRECCIÓN DE ENVÍO</p>
                                <p class="mt-2 text-[1.05rem] text-zinc-900">{{ $guest['direccion'] ?? 'Sin dirección registrada' }}</p>
                                <p class="mt-1 text-[1.05rem] text-zinc-900">{{ $guest['ciudad'] ?? 'Sin ciudad registrada' }}</p>
                                <p class="mt-1 text-[1.05rem] text-zinc-900">Método: Pasarela Dummy</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <p class="text-[clamp(1.5rem,2.8vw,2rem)] font-semibold text-zinc-900">Artículos</p>

                        <div class="invoice-panel mt-3 overflow-hidden">
                            <div class="grid grid-cols-12 bg-zinc-50 px-3 py-2 text-xs font-semibold uppercase tracking-[0.06em] text-zinc-500 sm:px-4">
                                <p class="col-span-6">DESCRIPCIÓN</p>
                                <p class="col-span-2 text-center">CANT.</p>
                                <p class="col-span-2 text-right">UNIT.</p>
                                <p class="col-span-2 text-right">TOTAL</p>
                            </div>

                            <div class="divide-y divide-zinc-100 bg-white">
                                @forelse($invoiceRows as $row)
                                    <div class="grid grid-cols-12 items-center px-3 py-3 text-sm sm:px-4">
                                        <div class="col-span-6 flex min-w-0 items-center gap-2.5">
                                            <div class="grid h-11 w-11 place-items-center rounded-xl border border-zinc-200 bg-zinc-100/80 text-zinc-600">
                                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path d="M3.8 13.4L5.2 9.6C5.55 8.62 6.49 7.96 7.53 7.96H9.25C10.31 7.96 11.25 8.65 11.58 9.66L12.04 11.05" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                    <path d="M20.2 13.4L18.8 9.6C18.45 8.62 17.51 7.96 16.47 7.96H14.75C13.69 7.96 12.75 8.65 12.42 9.66L11.96 11.05" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                    <path d="M2.8 12.3H8.2C9.41 12.3 10.4 13.29 10.4 14.5V14.5C10.4 17.04 8.34 19.1 5.8 19.1H5.2C2.66 19.1 0.6 17.04 0.6 14.5V14.5C0.6 13.29 1.59 12.3 2.8 12.3Z" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M15.8 12.3H21.2C22.41 12.3 23.4 13.29 23.4 14.5V14.5C23.4 17.04 21.34 19.1 18.8 19.1H18.2C15.66 19.1 13.6 17.04 13.6 14.5V14.5C13.6 13.29 14.59 12.3 15.8 12.3Z" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M10.5 14.1H13.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-[1.08rem] font-medium text-zinc-900">{{ $row['nombre'] }}</p>
                                                <p class="text-sm text-zinc-500">x{{ $row['cantidad'] }}</p>
                                            </div>
                                        </div>
                                        <p class="col-span-2 text-center text-[1.05rem] text-zinc-900">{{ $row['cantidad'] }}</p>
                                        <p class="col-span-2 text-right text-[1.05rem] text-zinc-900">{{ number_format((float) $row['unitario'], 0, ',', '.') }}</p>
                                        <p class="col-span-2 text-right text-[1.08rem] font-semibold text-zinc-900">{{ number_format((float) $row['linea'], 0, ',', '.') }}</p>
                                    </div>
                                @empty
                                    <p class="px-4 py-3 text-sm text-zinc-600">No hay ítems detallados en este pago.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="invoice-soft-blue mt-4 border-y border-zinc-200">
                    <div class="px-4 py-2 sm:px-5">
                        <div class="flex items-center justify-between text-[1.12rem] text-zinc-900">
                            <span>Subtotal</span>
                            <span>{{ number_format($invoiceSubtotal, 0, ',', '.') }} {{ $invoiceCurrency }}</span>
                        </div>
                    </div>
                    <div class="border-t border-zinc-200 px-4 py-2 sm:px-5">
                        <div class="flex items-center justify-between text-[1.12rem] text-zinc-900">
                            <span>Descuento</span>
                            <span>0 {{ $invoiceCurrency }}</span>
                        </div>
                    </div>
                    <div class="border-t border-zinc-300 px-4 py-2.5 sm:px-5">
                        <div class="flex items-center justify-between">
                            <span class="text-[clamp(1.9rem,3vw,2.35rem)] font-extrabold leading-none text-zinc-900">Total Pagado</span>
                            <span class="text-[clamp(1.9rem,3vw,2.35rem)] font-extrabold leading-none text-zinc-900">{{ number_format($invoiceTotal, 0, ',', '.') }} {{ $invoiceCurrency }}</span>
                        </div>
                    </div>
                </div>

                <div class="no-print flex flex-wrap items-center gap-2 border-t border-zinc-200 px-4 py-4 sm:px-5">
                    <button id="js-download-invoice-pdf" type="button" class="inline-flex items-center gap-2 rounded-xl bg-[#1f6a3f] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:brightness-105">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M14 3H7C5.9 3 5 3.9 5 5V19C5 20.1 5.9 21 7 21H17C18.1 21 19 20.1 19 19V8L14 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M14 3V8H19" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M9 13H15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M9 16H13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span>Descargar PDF</span>
                    </button>

                    <button id="js-print-invoice" type="button" class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-semibold text-zinc-800 transition hover:bg-zinc-50">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M7 8V4H17V8" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M7 17H5C3.9 17 3 16.1 3 15V11C3 9.9 3.9 9 5 9H19C20.1 9 21 9.9 21 11V15C21 16.1 20.1 17 19 17H17" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M7 13H17V20H7V13Z" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                        <span>Imprimir comprobante</span>
                    </button>
                </div>
            </section>
        @endif

        @if(!in_array((string) $pago->estado, ['aprobado', 'rechazado'], true))
            <div class="mt-8 flex flex-wrap items-end justify-between gap-3 border-t border-zinc-200 pt-5 sm:gap-4">
                <div class="grid w-full gap-2 sm:flex sm:w-auto sm:flex-wrap">
                <form action="{{ route('pagos.dummy.confirm', $pago) }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="resultado" value="aprobado">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl px-6 py-3 text-sm font-bold text-white transition hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 sm:w-auto" style="background: var(--pay-green); box-shadow: 0 9px 18px -11px rgba(47, 122, 69, 0.9);">
                        Aprobar pago
                    </button>
                </form>

                <form action="{{ route('pagos.dummy.confirm', $pago) }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <input type="hidden" name="resultado" value="rechazado">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border bg-white px-6 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-rose-300/50 sm:w-auto" style="border-color: var(--pay-red);">
                        Rechazar pago
                    </button>
                </form>
                </div>

                <div class="inline-flex items-center gap-2 text-sm font-medium text-zinc-600">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 3L19 6V11.7C19 16.07 16.01 20.14 12 21C7.99 20.14 5 16.07 5 11.7V6L12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        <path d="M9.3 11.7L11.05 13.45L14.7 9.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Seguro</span>
                </div>
            </div>
        @endif
    </div>
    </div>
</main>

<script>
    (function () {
        const downloadBtn = document.getElementById('js-download-invoice-pdf');
        const printBtn = document.getElementById('js-print-invoice');
        const invoiceArea = document.getElementById('js-invoice-download-area');

        if (!invoiceArea || !window.html2canvas || !window.jspdf) {
            return;
        }

        const downloadOriginalLabel = downloadBtn ? downloadBtn.innerHTML : '';
        const printOriginalLabel = printBtn ? printBtn.innerHTML : '';

        const captureInvoiceCanvas = async () => {
            return window.html2canvas(invoiceArea, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#f8fafc',
            });
        };

        const letterLayoutFromCanvas = (canvas) => {
            const pageWidth = 8.5;
            const pageHeight = 11;
            const margin = 0.35;
            const maxWidth = pageWidth - (margin * 2);
            const maxHeight = pageHeight - (margin * 2);

            const pxPerInch = 96;
            const imgWidthIn = canvas.width / pxPerInch;
            const imgHeightIn = canvas.height / pxPerInch;
            const scale = Math.min(maxWidth / imgWidthIn, maxHeight / imgHeightIn);

            const renderWidth = imgWidthIn * scale;
            const renderHeight = imgHeightIn * scale;
            const posX = (pageWidth - renderWidth) / 2;
            const posY = (pageHeight - renderHeight) / 2;

            return { pageWidth, pageHeight, renderWidth, renderHeight, posX, posY };
        };

        if (downloadBtn) {
            downloadBtn.addEventListener('click', async () => {
                try {
                    downloadBtn.disabled = true;
                    downloadBtn.classList.add('opacity-80', 'cursor-wait');
                    downloadBtn.innerHTML = '<span>Generando PDF...</span>';

                    const canvas = await captureInvoiceCanvas();
                    const layout = letterLayoutFromCanvas(canvas);

                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF({
                        orientation: 'portrait',
                        unit: 'in',
                        format: 'letter',
                        compress: true,
                    });

                    pdf.addImage(canvas.toDataURL('image/jpeg', 0.95), 'JPEG', layout.posX, layout.posY, layout.renderWidth, layout.renderHeight, undefined, 'FAST');

                    const ref = @json((string) $pago->referencia);
                    const safeRef = String(ref || 'comprobante').replace(/[^a-zA-Z0-9_-]/g, '_');
                    pdf.save(`comprobante-${safeRef}.pdf`);
                } catch (error) {
                    console.error('No se pudo generar el PDF:', error);
                    alert('No se pudo generar el PDF. Intenta nuevamente.');
                } finally {
                    downloadBtn.disabled = false;
                    downloadBtn.classList.remove('opacity-80', 'cursor-wait');
                    downloadBtn.innerHTML = downloadOriginalLabel;
                }
            });
        }

        if (printBtn) {
            printBtn.addEventListener('click', async () => {
                try {
                    printBtn.disabled = true;
                    printBtn.classList.add('opacity-80', 'cursor-wait');
                    printBtn.innerHTML = '<span>Preparando impresión...</span>';

                    const canvas = await captureInvoiceCanvas();
                    const layout = letterLayoutFromCanvas(canvas);
                    const imageData = canvas.toDataURL('image/jpeg', 0.95);

                    const printWindow = window.open('', '_blank');
                    if (!printWindow) {
                        alert('No se pudo abrir la ventana de impresión. Habilita las ventanas emergentes para este sitio.');
                        return;
                    }

                    const content = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
    <title>Imprimir comprobante</title>
  <style>
    @page { size: letter portrait; margin: 0; }
    html, body { margin: 0; padding: 0; background: #fff; }
    .page {
      width: ${layout.pageWidth}in;
      height: ${layout.pageHeight}in;
      position: relative;
      overflow: hidden;
      background: #fff;
    }
    .image {
      position: absolute;
      left: ${layout.posX}in;
      top: ${layout.posY}in;
      width: ${layout.renderWidth}in;
      height: ${layout.renderHeight}in;
      object-fit: contain;
    }
  </style>
</head>
<body>
  <div class="page">
        <img class="image" src="${imageData}" alt="Comprobante de pago" />
  </div>
  <script>
    window.addEventListener('load', function () {
      window.print();
      window.addEventListener('afterprint', function () { window.close(); });
    });
  <\/script>
</body>
</html>`;

                    printWindow.document.open();
                    printWindow.document.write(content);
                    printWindow.document.close();
                } catch (error) {
                    console.error('No se pudo preparar la impresión:', error);
                    alert('No se pudo preparar la impresión. Intenta nuevamente.');
                } finally {
                    printBtn.disabled = false;
                    printBtn.classList.remove('opacity-80', 'cursor-wait');
                    printBtn.innerHTML = printOriginalLabel;
                }
            });
        }
    })();
</script>
</body>
</html>
