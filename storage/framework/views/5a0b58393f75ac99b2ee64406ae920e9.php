<?php $__env->startSection('title', 'Información'); ?>
<?php $__env->startSection('heading', 'Información del Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <h3 class="text-lg font-semibold text-zinc-900">Visibilidad del boton de compra</h3>
                <p class="text-sm text-zinc-600">Controla si el boton de pagar se muestra o no para los clientes.</p>
            </div>

            <form method="POST" action="<?php echo e(route('admin.informacion.love-cta')); ?>" class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="hide_love_cta_for_clients" value="0">

                <label class="inline-flex items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm font-medium text-zinc-800">
                    <input type="checkbox" name="hide_love_cta_for_clients" value="1" class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-400" <?php if($hideLoveCtaForClients): echo 'checked'; endif; ?>>
                    <span>Ocultar para clientes</span>
                </label>

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-zinc-800 sm:w-auto">
                    Guardar
                </button>
            </form>
        </div>
    </div>

    <!-- KPIs - Números principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        <!-- Ingresos Totales -->
        <div class="rounded-2xl border border-zinc-200 bg-gradient-to-br from-green-50 to-green-100 p-4 shadow-sm transition hover:shadow-md sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-green-600">Ingresos Totales</p>
                    <p class="mt-2 break-words text-2xl font-bold text-green-900 sm:text-3xl">$<?php echo e(number_format($ingresosTotales, 0)); ?></p>
                    <p class="mt-2 text-xs text-green-600">Todos los tiempos</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pagos Completados -->
        <div class="rounded-2xl border border-zinc-200 bg-gradient-to-br from-purple-50 to-purple-100 p-4 shadow-sm transition hover:shadow-md sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-600">Pagos Completados</p>
                    <p class="mt-2 break-words text-2xl font-bold text-purple-900 sm:text-3xl"><?php echo e($cantidadPagos); ?></p>
                    <p class="mt-2 text-xs text-purple-600">Transacciones exitosas</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Ingresos Este Mes -->
        <div class="rounded-2xl border border-zinc-200 bg-gradient-to-br from-orange-50 to-orange-100 p-4 shadow-sm transition hover:shadow-md sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-orange-600">Este Mes</p>
                    <p class="mt-2 break-words text-2xl font-bold text-orange-900 sm:text-3xl">$<?php echo e(number_format($ingresosEstesMes, 0)); ?></p>
                    <p class="mt-2 text-xs text-orange-600"><?php echo e(now()->format('F Y')); ?></p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

    </div>

    <!-- Gráficas Principales -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gráfica de Ingresos por Mes -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-6">
            <h3 class="mb-4 text-base font-semibold text-zinc-900 sm:text-lg">Ingresos por mes</h3>
            <div class="relative h-56 sm:h-64">
                <canvas id="ingresosMesesChart"></canvas>
            </div>
        </div>

        <!-- Gráfica de Visitantes por Mes -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-6">
            <h3 class="mb-4 text-base font-semibold text-zinc-900 sm:text-lg">Pagos Realizados por Mes</h3>
            <div class="relative h-56 sm:h-64">
                <canvas id="visitantesMesesChart"></canvas>
            </div>
        </div>

        <!-- Estado de Pagos -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-6">
            <h3 class="mb-4 text-base font-semibold text-zinc-900 sm:text-lg">Estado de Pagos</h3>
            <div class="relative h-56 sm:h-64">
                <canvas id="estadoPagosChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabla de Información adicional -->
    <div class="grid grid-cols-1 gap-6">
        <!-- Estadísticas rápidas -->
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm sm:p-6">
            <h3 class="mb-4 text-base font-semibold text-zinc-900 sm:text-lg">Resumen Rápido</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3 border-b border-zinc-100 py-2">
                    <span class="text-sm text-zinc-600">Promedio por pago</span>
                    <span class="break-words text-right font-semibold text-zinc-900">$<?php echo e($cantidadPagos > 0 ? number_format($ingresosTotales / $cantidadPagos, 2) : '0'); ?></span>
                </div>
                <div class="flex items-center justify-between gap-3 border-b border-zinc-100 py-2">
                    <span class="text-sm text-zinc-600">Ingresos este mes</span>
                    <span class="break-words text-right font-semibold text-zinc-900">$<?php echo e(number_format($ingresosEstesMes, 0)); ?></span>
                </div>
                <div class="flex items-center justify-between gap-3 py-2">
                    <span class="text-sm text-zinc-600">Pagos completados</span>
                    <span class="break-words text-right font-semibold text-zinc-900"><?php echo e($cantidadPagos); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
    // Datos desde el servidor
    const mesesNombres = <?php echo $mesesNombres; ?>;
    const ingresosPorMesData = <?php echo $ingresosPorMesData; ?>;
    const visitantesPorMesData = <?php echo $visitantesPorMesData; ?>;

    // Colores profesionales
    const coloresPrincipales = {
        azul: '#3b82f6',
        verde: '#10b981',
        rojo: '#ef4444',
        morado: '#8b5cf6',
        naranja: '#f97316',
        rosa: '#ec4899'
    };

    const hexToRgb = (hex) => {
        if (typeof hex !== 'string') return null;
        const raw = hex.replace('#', '').trim();
        if (raw.length !== 6) return null;
        const r = parseInt(raw.slice(0, 2), 16);
        const g = parseInt(raw.slice(2, 4), 16);
        const b = parseInt(raw.slice(4, 6), 16);
        if ([r, g, b].some((v) => Number.isNaN(v))) return null;
        return { r, g, b };
    };

    const rgba = (hex, alpha) => {
        const rgb = hexToRgb(hex);
        if (!rgb) return hex;
        return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${alpha})`;
    };

    const makeFillGradient = (ctx, hex) => {
        const gradient = ctx.createLinearGradient(0, 0, 0, 320);
        gradient.addColorStop(0, rgba(hex, 0.18));
        gradient.addColorStop(1, rgba(hex, 0.00));
        return gradient;
    };

    const formatMoney = (value) => {
        try {
            return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(value);
        } catch (e) {
            return '$' + Number(value || 0).toLocaleString('es-ES');
        }
    };

    // Gráfica 1: Ingresos por mes
    const ctxIngresos = document.getElementById('ingresosMesesChart').getContext('2d');
    new Chart(ctxIngresos, {
        type: 'line',
        data: {
            labels: mesesNombres,
            datasets: [{
                label: 'Ingresos ($)',
                data: ingresosPorMesData,
                borderColor: coloresPrincipales.azul,
                backgroundColor: makeFillGradient(ctxIngresos, coloresPrincipales.azul),
                borderWidth: 3,
                tension: 0.35,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: coloresPrincipales.azul,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: coloresPrincipales.azul,
                cubicInterpolationMode: 'monotone',
                spanGaps: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        font: { size: 12, weight: 'bold' },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return formatMoney(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => formatMoney(value),
                        font: { size: 11 }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    }
                },
                x: {
                    ticks: { font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });

    // Gráfica 2: Visitantes (clientes) por mes
    const ctxVisitantes = document.getElementById('visitantesMesesChart').getContext('2d');
    new Chart(ctxVisitantes, {
        type: 'line',
        data: {
            labels: mesesNombres,
            datasets: [{
                label: 'Pagos',
                data: visitantesPorMesData,
                borderColor: coloresPrincipales.verde,
                backgroundColor: makeFillGradient(ctxVisitantes, coloresPrincipales.verde),
                borderWidth: 3,
                tension: 0.35,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: coloresPrincipales.verde,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: coloresPrincipales.verde,
                cubicInterpolationMode: 'monotone',
                spanGaps: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        font: { size: 12, weight: 'bold' },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' pago' + (context.parsed.y !== 1 ? 's' : '');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 11 }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    }
                },
                x: {
                    ticks: { font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });

    // Gráfica 3: Estado de Pagos (Pastel)
    const ctxEstado = document.getElementById('estadoPagosChart').getContext('2d');
    new Chart(ctxEstado, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($estadoPagos->pluck('estado')->toArray()); ?>,
            datasets: [{
                data: <?php echo json_encode($estadoPagos->pluck('cantidad')->toArray()); ?>,
                backgroundColor: [
                    rgba(coloresPrincipales.verde, 0.85),
                    rgba(coloresPrincipales.rojo, 0.85),
                    rgba(coloresPrincipales.naranja, 0.85),
                    rgba(coloresPrincipales.azul, 0.85)
                ],
                borderColor: '#fff',
                borderWidth: 2,
                borderRadius: 10,
                spacing: 4,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { size: 11, weight: 'bold' },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const values = context.dataset.data || [];
                            const total = values.reduce((acc, v) => acc + (Number(v) || 0), 0);
                            const value = Number(context.parsed) || 0;
                            const pct = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${context.label}: ${value} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard_empty_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\norla\Desktop\Proyectos con Ronaldo\copia_optica\resources\views/admin/informacion.blade.php ENDPATH**/ ?>