<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('heading', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $promoPreview = (string) ($gafasPromo['image_url'] ?? asset('images/borrardespues.png'));
    ?>

    <div class="space-y-6">
        <?php if(session('status')): ?>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?>

        <div class="rounded-3xl border border-zinc-200 bg-zinc-50 p-6">
            <h2 class="text-lg font-semibold text-zinc-900">Bienvenido</h2>
            <p class="mt-2 text-sm text-zinc-600">
                Desde aquí también puedes cambiar la imagen promo principal de /gafas.
            </p>
        </div>

        <div class="rounded-3xl border border-zinc-200 bg-white p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">Imagen promo de /gafas</h3>
                    <p class="mt-1 text-sm text-zinc-500">Puedes subir una o varias imágenes. Si hay varias, se rotan automáticamente en la página pública.</p>
                </div>
            </div>

            <form class="mt-4" action="<?php echo e(route('dashboard.gafas-promo.update')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px] lg:items-start">
                    <div>
                        <label for="gafasPromoImage" class="text-sm font-semibold text-zinc-800">Subir nueva imagen</label>
                        <input id="gafasPromoImage" name="promo_image_file" type="file" accept="image/*" class="mt-2 block w-full rounded-2xl border border-zinc-200 px-4 py-3 text-sm file:mr-4 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">
                        <p class="mt-2 text-xs text-zinc-500">Actual: <?php echo e($gafasPromo['uploaded_name'] ?? 'imagen por defecto (images/borrardespues.png)'); ?></p>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <button type="submit" class="rounded-2xl bg-zinc-950 px-4 py-2 text-sm font-semibold text-white hover:bg-zinc-900">Guardar imagen promo</button>
                            <?php if(!empty($gafasPromo['has_uploaded_image'])): ?>
                                <button type="submit" name="remove_promo_image" value="1" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100">Quitar imagen subida</button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-50">
                        <img src="<?php echo e($promoPreview); ?>" alt="Vista previa banner gafas" class="h-56 w-full object-cover" loading="lazy">
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard_empty_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\norla\Desktop\Proyectos con Ronaldo\copia_optica\resources\views/dashboard.blade.php ENDPATH**/ ?>