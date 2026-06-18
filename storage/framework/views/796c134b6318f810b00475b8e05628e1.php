<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seguimiento de pedido — Óptica</title>

    <?php ($viteHot = public_path('hot')); ?>
    <?php ($viteManifest = public_path('build/manifest.json')); ?>
    <?php ($hasViteAssets = file_exists($viteHot) || file_exists($viteManifest)); ?>

    <?php if($hasViteAssets): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php endif; ?>

    <?php if(!$hasViteAssets || app()->isLocal()): ?>
        <script src="https://cdn.tailwindcss.com"></script>
    <?php endif; ?>
</head>
<body class="bg-zinc-50 text-zinc-900 antialiased font-sans">
<?php ($showStoreBanner = false); ?>
<?php echo $__env->make('partials.store-navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<main class="mx-auto max-w-5xl px-4 py-6 sm:py-10">
    <section id="seguimiento-pedido" class="overflow-hidden rounded-3xl border border-[#bde5e8] bg-gradient-to-br from-[#eefafa] via-white to-[#f7fdff] p-4 shadow-sm sm:p-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-[#0d6b72]">Seguimiento especial de pedido</p>
                <h1 class="mt-1 text-xl font-bold text-zinc-900 sm:text-2xl">Consulta tu pedido con la cédula</h1>
                <p class="mt-2 text-sm text-zinc-600">Escribe solo tu número de cédula y revisa si tu pedido ya fue enviado o sigue pendiente.</p>
            </div>
            <a href="<?php echo e(route('landing')); ?>" class="rounded-2xl px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">Volver a inicio</a>
        </div>

        <form method="GET" action="<?php echo e(route('pedido.tracking')); ?>" class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
            <div>
                <label for="cedula" class="text-xs font-semibold text-zinc-700">Cédula</label>
                <input id="cedula" name="cedula" value="<?php echo e($trackingInputCedula ?? ''); ?>" placeholder="Número de cédula" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-[#1f9096]" />
            </div>
            <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#1f9096] px-5 py-3 text-sm font-semibold text-white hover:bg-[#187b80]">
                Consultar
            </button>
        </form>

        <?php if($trackingError): ?>
            <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                <?php echo e($trackingError); ?>

            </div>
        <?php endif; ?>

        <?php if(($trackingResults ?? collect())->isNotEmpty()): ?>
            <div class="mt-6">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-zinc-900">Pedidos encontrados</h2>
                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-zinc-700 ring-1 ring-zinc-200">
                        <?php echo e($trackingResults->count()); ?> resultados
                    </span>
                </div>

                <div class="grid gap-3">
                    <?php $__currentLoopData = $trackingResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pedido): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-base font-bold text-zinc-900"><?php echo e($pedido['cliente']); ?></p>
                                    <p class="mt-1 text-sm text-zinc-600">Referencia: <?php echo e($pedido['referencia']); ?></p>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700"><?php echo e($pedido['estado_pago']); ?></span>
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?php echo e($pedido['esta_enviado'] ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'); ?>"><?php echo e($pedido['estado_envio']); ?></span>
                                </div>
                            </div>

                            <div class="mt-4 grid gap-2 text-sm text-zinc-700 sm:grid-cols-3">
                                <div class="rounded-2xl bg-zinc-50 px-3 py-2">
                                    <span class="font-semibold text-zinc-900">Total:</span>
                                    <?php echo e(number_format((float) $pedido['monto'], 0, ',', '.')); ?> <?php echo e($pedido['moneda']); ?>

                                </div>
                                <div class="rounded-2xl bg-zinc-50 px-3 py-2">
                                    <span class="font-semibold text-zinc-900">Pedido:</span>
                                    <?php echo e($pedido['fecha_pedido'] ?: '—'); ?>

                                </div>
                                <div class="rounded-2xl bg-zinc-50 px-3 py-2">
                                    <span class="font-semibold text-zinc-900">Enviado:</span>
                                    <?php echo e($pedido['fecha_envio'] ?: 'Aún sin marcar envío'); ?>

                                </div>
                                <div class="rounded-2xl bg-zinc-50 px-3 py-2">
                                    <span class="font-semibold text-zinc-900">Comprobante:</span>
                                    <?php if(!empty($pedido['shipping_file'])): ?>
                                        <a href="<?php echo e($pedido['shipping_file']); ?>" class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 text-xs font-semibold text-zinc-700 border border-zinc-200" target="_blank" rel="noopener">Descargar PDF / Foto</a>
                                    <?php else: ?>
                                        <span class="text-zinc-600">No disponible</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if(!empty($pedido['shipping_file']) && $pedido['esta_enviado']): ?>
                                <div class="mt-4">
                                    <div class="rounded-2xl border border-zinc-200 bg-white p-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="text-sm text-zinc-800">Comprobante subido: <strong><?php echo e($pedido['shipping_file_name'] ?? 'archivo'); ?></strong></div>
                                            <div>
                                                <a href="<?php echo e($pedido['shipping_file']); ?>" download class="inline-flex items-center gap-2 rounded-full bg-[#1f9096] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#187b80]">Descargar PDF aquí</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
<?php /**PATH C:\Users\norla\Desktop\Proyectos con Ronaldo\copia_optica\resources\views/pedidos/tracking.blade.php ENDPATH**/ ?>