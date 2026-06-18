<?php $__env->startSection('title', 'Gafas deportivas'); ?>
<?php $__env->startSection('heading', 'Gafas deportivas'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $promoPreview = (string) ($promoImage['image_url'] ?? asset('images/borrardespues.png'));
        $promoImages = is_array($promoImage['image_urls'] ?? null) ? $promoImage['image_urls'] : [];
        $promoImageNames = is_array($promoImage['uploaded_names'] ?? null) ? $promoImage['uploaded_names'] : [];
        $promoAssets = is_array($promoImage['promo_assets'] ?? null) ? $promoImage['promo_assets'] : [];
        if ($promoAssets === [] && $promoImages !== []) {
            $promoAssets = array_map(static fn (string $url): array => ['id' => null, 'url' => $url], $promoImages);
        }
    ?>

    <div class="mb-6 rounded-3xl border border-zinc-200 bg-white p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-zinc-900">Imagen promo de deportivas en /gafas</h3>
                <p class="mt-1 text-sm text-zinc-500">Se mostrará cuando entren a /gafas con el filtro Deportivas.</p>
            </div>
        </div>

        <form class="mt-4" action="<?php echo e(route('admin.gafas-descanso.promo-image.update')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
                <div>
                    <label for="gafasDescansoPromoImage" class="text-sm font-semibold text-zinc-800">Subir nuevas imágenes</label>
                    <input id="gafasDescansoPromoImage" name="promo_image_files[]" type="file" accept="image/*" multiple class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">
                    <?php if($promoImageNames !== []): ?>
                        <p class="mt-2 text-xs text-zinc-500">Actuales (<?php echo e(count($promoImageNames)); ?>): <?php echo e(implode(', ', $promoImageNames)); ?></p>
                    <?php else: ?>
                        <p class="mt-2 text-xs text-zinc-500">Actual: imagen por defecto (images/borrardespues.png)</p>
                    <?php endif; ?>
                    <p class="mt-1 text-xs text-zinc-500">Las imágenes subidas se intercalan automáticamente en /gafas cuando el filtro Deportivas está activo.</p>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <button type="submit" class="rounded-2xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-900">Guardar imágenes promo</button>
                        <?php if(!empty($promoImage['has_uploaded_image'])): ?>
                            <button type="submit" name="remove_promo_image" value="1" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Quitar imágenes subidas</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50">
                    <img src="<?php echo e($promoPreview); ?>" alt="Vista previa banner deportivas" class="h-56 w-full object-cover" loading="lazy">
                </div>
            </div>

            <?php if($promoAssets !== []): ?>
                <div class="mt-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Vista previa banners deportivas</p>
                    <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        <?php $__currentLoopData = $promoAssets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $promoAsset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($promoImageUrl = (string) ($promoAsset['url'] ?? '')); ?>
                            <div class="relative overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50">
                                <?php if(!empty($promoAsset['id'])): ?>
                                    <button type="submit" name="remove_promo_image_id" value="<?php echo e((int) $promoAsset['id']); ?>" class="absolute right-1.5 top-1.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-black/75 text-sm font-bold text-white hover:bg-black" title="Quitar imagen" aria-label="Quitar imagen">×</button>
                                <?php endif; ?>
                                <img src="<?php echo e($promoImageUrl); ?>" alt="Banner deportivas" class="h-24 w-full object-cover" loading="lazy">
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900">Listado</h2>
            <p class="mt-1 text-sm text-zinc-500">Administra las gafas deportivas.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="<?php echo e(route('admin.gafas-descanso.import.show')); ?>" class="inline-flex items-center justify-center rounded-2xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                ⬆ Importar Excel
            </a>
            <a href="<?php echo e(route('admin.gafas-descanso.create')); ?>" class="inline-flex items-center justify-center rounded-2xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-900">
                + Agregar
            </a>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-3xl border border-zinc-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="bg-zinc-50 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                <tr>
                    <th class="px-5 py-4">Producto</th>
                    <th class="px-5 py-4">Precio</th>
                    <th class="px-5 py-4">Estado</th>
                    <th class="px-5 py-4 text-right">Acciones</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                <?php $__empty_1 = true; $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php ($meta = is_array($producto->meta) ? $producto->meta : []); ?>
                    <?php ($img = (string)($meta['imagen_url'] ?? '')); ?>
                    <tr class="bg-white">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50">
                                    <?php if($img): ?>
                                        <img src="<?php echo e($img); ?>" alt="<?php echo e(e($producto->nombre)); ?>" class="h-full w-full object-cover" />
                                    <?php endif; ?>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-zinc-900"><?php echo e($producto->nombre); ?></p>
                                    <p class="mt-0.5 truncate text-xs text-zinc-500"><?php echo e($producto->marca ?: '—'); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-semibold text-zinc-900"><?php echo e($producto->precio !== null ? number_format((float)$producto->precio, 0, ',', '.') : '—'); ?> <?php echo e($producto->moneda); ?></p>
                            <?php if($producto->precio_oferta): ?>
                                <p class="mt-0.5 text-xs text-zinc-500">Oferta: <?php echo e(number_format((float)$producto->precio_oferta, 0, ',', '.')); ?> <?php echo e($producto->moneda); ?></p>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4">
                            <?php ($stock = $producto->existencias); ?>
                            <?php ($lowStockColors = $producto->lowStockColors()); ?>
                            <?php if($producto->esta_activo): ?>
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Activo</span>
                            <?php else: ?>
                                <span class="inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700">Inactivo</span>
                            <?php endif; ?>

                            <?php if($stock !== null): ?>
                                <p class="mt-1 text-xs text-zinc-600">Existencias: <?php echo e($stock); ?></p>
                            <?php endif; ?>

                            <?php if($lowStockColors !== []): ?>
                                <div class="mt-2 space-y-1">
                                    <?php $__currentLoopData = $lowStockColors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="block rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                            <?php echo e($alert['stock'] <= 0 ? 'El color ' . $alert['color'] . ' está agotado.' : 'Te quedan pocas gafas del color ' . $alert['color'] . '.'); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?php echo e(route('admin.gafas-descanso.edit', $producto)); ?>" class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">Editar</a>
                                <form action="<?php echo e(route('admin.gafas-descanso.destroy', $producto)); ?>" method="POST" onsubmit="return confirm('¿Eliminar esta gafa?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-zinc-50">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-sm text-zinc-500">Aún no has agregado gafas deportivas.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        <a href="<?php echo e(route('gafas-deportivas.index')); ?>" class="text-sm font-semibold text-zinc-700 hover:underline">Ver página pública: /gafas-deportivas</a>
    </div>

    <?php echo $__env->make('admin.partials.auto_refresh', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard_empty_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\norla\Desktop\Proyectos con Ronaldo\copia_optica\resources\views/admin/gafas_descanso/index.blade.php ENDPATH**/ ?>