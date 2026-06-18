<?php $__env->startSection('title', $onlyEmployees ? 'Empleados' : 'Usuarios'); ?>
<?php $__env->startSection('heading', $onlyEmployees ? 'Empleados' : 'Usuarios'); ?>

<?php $__env->startSection('content'); ?>
    <div class="rounded-2xl border border-zinc-200 bg-white p-5">
        <?php if(!$onlyEmployees): ?>
            <div class="mb-5 flex flex-wrap items-center gap-2">
                <?php if($showAllUsers): ?>
                    <a href="<?php echo e(route('admin')); ?>" class="rounded-xl border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                        Ocultar Usuarios
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('admin', ['usuarios' => 1])); ?>" class="rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800">
                        Usuarios
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="GET" action="<?php echo e($onlyEmployees ? route('admin.empleados') : route('admin')); ?>" class="mb-5 flex flex-col gap-3 md:flex-row md:items-center">
            <?php if(!$onlyEmployees && $showAllUsers): ?>
                <input type="hidden" name="usuarios" value="1">
            <?php endif; ?>
            <input
                type="text"
                name="q"
                value="<?php echo e($search); ?>"
                placeholder="Buscar por ID, nombre o correo"
                class="w-full rounded-xl border border-zinc-300 px-4 py-2.5 text-sm text-zinc-900 outline-none focus:border-zinc-900"
            >
            <button type="submit" class="rounded-xl bg-zinc-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800">
                Buscar
            </button>
            <?php if($search !== ''): ?>
                <a href="<?php echo e($onlyEmployees ? route('admin.empleados') : route('admin', ['usuarios' => 1])); ?>" class="rounded-xl border border-zinc-300 px-4 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                    Limpiar
                </a>
            <?php endif; ?>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm">
                <thead class="bg-zinc-50">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-700">ID</th>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-700">Nombre</th>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-700">Correo</th>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-700">Rol actual</th>
                    <th class="px-3 py-2 text-left font-semibold text-zinc-700">Cambiar rol</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 bg-white">
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $isProgramador = (int) $user->rol_id === 4;
                        $maskProgramadorAsUsuario = !$onlyEmployees && $showAllUsers && $isProgramador;
                        $displayRolId = $maskProgramadorAsUsuario ? 1 : (int) $user->rol_id;
                        $displayRolNombre = $maskProgramadorAsUsuario
                            ? ($roles->firstWhere('id', 1)?->nombre ?? 'Usuario')
                            : ($isProgramador ? 'Programador' : ($user->rol?->nombre ?? ('Rol #' . $user->rol_id)));
                    ?>
                    <tr>
                        <td class="px-3 py-3 text-zinc-700"><?php echo e($user->id); ?></td>
                        <td class="px-3 py-3 font-medium text-zinc-900"><?php echo e($user->nombre); ?></td>
                        <td class="px-3 py-3 text-zinc-700"><?php echo e($user->correo); ?></td>
                        <td class="px-3 py-3 text-zinc-700">
                            <?php echo e($displayRolNombre); ?>

                        </td>
                        <td class="px-3 py-3">
                            <?php if($isProgramador && !$maskProgramadorAsUsuario): ?>
                                <span class="inline-flex items-center rounded-lg border border-zinc-200 bg-zinc-100 px-3 py-2 text-xs font-semibold text-zinc-600">
                                    Bloqueado para cambios
                                </span>
                            <?php else: ?>
                                <form method="POST" action="<?php echo e(route('admin.usuarios.update-role', $user)); ?>" class="flex flex-col gap-2 md:flex-row md:items-center">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <select name="rol_id" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm">
                                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($rol->id); ?>" <?php if($displayRolId === (int) $rol->id): echo 'selected'; endif; ?>>
                                                <?php echo e($rol->nombre); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <button type="submit" class="rounded-lg bg-zinc-900 px-3 py-2 text-sm font-semibold text-white hover:bg-zinc-700">
                                        Guardar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-zinc-500">No se encontraron usuarios.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            <?php echo e($users->onEachSide(1)->links()); ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard_empty_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\norla\Desktop\Proyectos con Ronaldo\copia_optica\resources\views/admin/users/index.blade.php ENDPATH**/ ?>