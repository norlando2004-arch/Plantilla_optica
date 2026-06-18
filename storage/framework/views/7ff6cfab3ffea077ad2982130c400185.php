<?php $__env->startSection('title', 'Próximos envíos'); ?>
<?php $__env->startSection('heading', 'Próximos envíos'); ?>

<?php $__env->startSection('content'); ?>
    <div class="rounded-3xl border border-zinc-200 bg-gradient-to-br from-zinc-50 to-white p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-lg font-semibold text-zinc-900">Tus envíos</h2>
                    <span class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-zinc-700 ring-1 ring-zinc-200">
                        <?php echo e($pagos->count()); ?> <?php echo e($pagos->count() === 1 ? 'pedido' : 'pedidos'); ?>

                    </span>
                </div>
                <p class="mt-1 text-sm text-zinc-600">Aquí verás la información de envío de tus compras aprobadas, con los datos del cliente, la montura y los lentes.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="<?php echo e(route('dashboard.proximos-envios', ['view' => 'pendientes'])); ?>" class="inline-flex items-center justify-center rounded-2xl px-3 py-2 text-xs font-semibold <?php echo e(($view ?? 'pendientes') === 'pendientes' ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-800 border border-zinc-200 hover:bg-zinc-100'); ?>">
                    Próximos envíos
                </a>
                <a href="<?php echo e(route('dashboard.proximos-envios', ['view' => 'enviadas'])); ?>" class="inline-flex items-center justify-center rounded-2xl px-3 py-2 text-xs font-semibold <?php echo e(($view ?? 'pendientes') === 'enviadas' ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-800 border border-zinc-200 hover:bg-zinc-100'); ?>">
                    Ver enviadas
                </a>
            </div>
        </div>

        <?php if(($view ?? 'pendientes') === 'enviadas'): ?>
            <form method="GET" action="<?php echo e(route('dashboard.proximos-envios')); ?>" class="mt-4 flex flex-wrap items-end gap-3">
                <input type="hidden" name="view" value="enviadas" />
                <div>
                    <label class="text-xs font-semibold text-zinc-700">Buscar por cédula</label>
                    <input name="cedula" value="<?php echo e($cedula ?? ''); ?>" class="mt-1 w-48 rounded-2xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-400" placeholder="Número de documento" />
                </div>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-4 py-2 text-xs font-semibold text-white hover:bg-zinc-800">
                    Filtrar
                </button>
            </form>
        <?php endif; ?>

        <?php if($pagos->isEmpty()): ?>
            <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-4 text-sm text-zinc-700">
                <?php if(($view ?? 'pendientes') === 'enviadas'): ?>
                    No hay envíos marcados como enviados.
                <?php else: ?>
                    Aún no tienes pagos aprobados para enviar.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="mt-6 space-y-4">
                <?php $__currentLoopData = $pagos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pago): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $meta = is_array($pago->meta) ? $pago->meta : [];
                        $carritoMeta = is_array($pago->carrito?->meta) ? $pago->carrito->meta : [];
                        $perfilId = (int) ($meta['perfil_cliente_id'] ?? $carritoMeta['perfil_cliente_id'] ?? 0);
                        $perfil = $perfilId ? ($perfiles[$perfilId] ?? null) : null;
                        $guest = is_array($meta['guest'] ?? null) ? $meta['guest'] : [];
                        $buyer = is_array($meta['cliente'] ?? null) ? $meta['cliente'] : (is_array($carritoMeta['cliente'] ?? null) ? $carritoMeta['cliente'] : []);
                        $lentes = is_array($meta['lentes'] ?? null) ? $meta['lentes'] : (is_array($carritoMeta['lentes'] ?? null) ? $carritoMeta['lentes'] : []);
                        $frame = is_array($meta['montura'] ?? null) ? $meta['montura'] : (is_array($carritoMeta['montura'] ?? null) ? $carritoMeta['montura'] : []);
                        $prescriptionId = (int) ($meta['prescription_id'] ?? $carritoMeta['prescription_id'] ?? 0);
                        $prescription = $prescriptionId ? ($prescriptions[$prescriptionId] ?? null) : null;
                        $manual = is_array($prescription->analysis['manual_input'] ?? null) ? $prescription->analysis['manual_input'] : [];
                        $usuario = $perfil?->usuario ?? $pago->carrito?->usuario;
                        $firstItem = ($pago->carrito?->items ?? collect())->first();
                        $frameProduct = $firstItem?->producto;
                        $frameProductMeta = is_array($frameProduct?->meta ?? null) ? $frameProduct->meta : [];
                        $frameFeatures = is_array($frame['caracteristicas'] ?? null) ? $frame['caracteristicas'] : (is_array($frameProduct?->caracteristicas ?? null) ? $frameProduct->caracteristicas : []);
                        $polyEnabled = array_key_exists('poly_enabled', $lentes)
                            ? (bool) $lentes['poly_enabled']
                            : \App\Services\Gafas\GafaLensPricing::usesPolyForCharacteristics($frameFeatures);
                        $frameMeasures = is_array($frameFeatures['medidas'] ?? null) ? $frameFeatures['medidas'] : [];
                        $fmtMeasure = static function ($value): ?string {
                            if ($value === null || $value === '') {
                                return null;
                            }

                            return rtrim(rtrim(number_format((float) $value, 1, '.', ''), '0'), '.') . ' cm';
                        };
                        $frameColor = trim((string) ($frame['color'] ?? ($frameProductMeta['color'] ?? ($frameProductMeta['color_nombre'] ?? ($frameProduct?->color ?? '')))));
                        $frameBrand = trim((string) ($frame['marca'] ?? ($frameProduct?->marca ?? '')));
                        $frameMaterial = trim((string) ($frame['material_montura'] ?? ($frameProduct?->material_montura ?? '')));
                        $frameDescription = trim((string) ($frame['descripcion'] ?? ($frameProduct?->descripcion ?? '')));
                        $frameLensWidth = $fmtMeasure($frameMeasures['ancho_lente_cm'] ?? null);
                        $frameBridge = $fmtMeasure($frameMeasures['puente_cm'] ?? null);
                        $frameTemples = $fmtMeasure($frameMeasures['largo_patillas_cm'] ?? null);
                        $frameSpecParts = array_values(array_filter([
                            $frameBrand !== '' ? 'Marca: ' . $frameBrand : null,
                            $frameMaterial !== '' ? 'Material: ' . $frameMaterial : null,
                            $frameLensWidth ? 'Lente: ' . $frameLensWidth : null,
                            $frameBridge ? 'Puente: ' . $frameBridge : null,
                            $frameTemples ? 'Patillas: ' . $frameTemples : null,
                        ]));
                        $productTitle = (string) ($firstItem?->nombre_producto ?? ($frame['nombre'] ?? 'Producto'));
                        $productSpecsLine = count($frameSpecParts) ? implode(' • ', $frameSpecParts) : 'Especificaciones no disponibles';
                        $productMeasureItems = [
                            ['label' => 'Material', 'value' => $frameMaterial !== '' ? $frameMaterial : '—'],
                            ['label' => 'Lente', 'value' => $frameLensWidth ?? '—'],
                            ['label' => 'Puente', 'value' => $frameBridge ?? '—'],
                            ['label' => 'Patillas', 'value' => $frameTemples ?? '—'],
                        ];
                        $approvedAt = $pago->updated_at?->copy()?->timezone('America/Bogota') ?? $pago->created_at?->copy()?->timezone('America/Bogota');
                        $approvedAtLabel = $approvedAt ? $approvedAt->format('Y-m-d h:i A') : null;
                    ?>

                    <div class="overflow-hidden rounded-[28px] border border-zinc-200 bg-white shadow-[0_14px_35px_-24px_rgba(15,23,42,0.32)]">
                        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-zinc-200 bg-[linear-gradient(120deg,#fbfdff_0%,#eef4fb_100%)] px-5 py-4">
                            <div>
                                <p class="text-lg font-semibold text-zinc-900">Referencia: <?php echo e($pago->referencia); ?></p>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-zinc-600">
                                    <span>Aprobado • <?php echo e($approvedAtLabel ?? '—'); ?></span>
                                    <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-[11px] font-semibold text-sky-700"><?php echo e($perfil ? 'Cliente registrado' : 'Compra invitada'); ?></span>
                                    <?php if($pago->envio_estado === 'enviado'): ?>
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">Enviado</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700">Pendiente de envío</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="ml-auto flex items-center gap-3">
                                <div class="min-w-[150px] text-right">
                                    <p class="text-[12px] uppercase tracking-wide text-zinc-600">Total pagado</p>
                                    <p class="text-[42px] leading-none font-bold text-blue-700"><?php echo e(number_format((float) $pago->monto, 0, ',', '.')); ?> <?php echo e($pago->moneda); ?></p>
                                </div>

                                <div class="grid gap-2">
                                    <a href="<?php echo e(route('pagos.show', $pago)); ?>" class="inline-flex items-center justify-center rounded-full bg-blue-700 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                                        Ver pago
                                    </a>
                                    <?php if($prescriptionId): ?>
                                        <a href="<?php echo e(route('dashboard.pagos.formula', ['pago' => $pago->id])); ?>" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-full border border-zinc-200 bg-white px-5 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50">
                                            Ver fórmula
                                        </a>
                                    <?php endif; ?>
                                    <?php ($metaPago = is_array($pago->meta) ? $pago->meta : []); ?>
                                    <?php ($shippingFile = $metaPago['shipping_file'] ?? null); ?>

                                    <?php if($pago->envio_estado === 'enviado'): ?>
                                        <div class="text-sm text-emerald-700">Envío confirmado</div>
                                    <?php else: ?>
                                        <div class="grid gap-2">
                                                <?php ($buttonDisabled = (bool) $shippingFile); ?>
                                                <button type="button" data-toggle-form="upload-form-<?php echo e($pago->id); ?>" class="inline-flex items-center justify-center rounded-full px-5 py-2 text-sm font-semibold <?php echo e($buttonDisabled ? 'bg-zinc-100 text-zinc-400 border border-zinc-200 cursor-not-allowed opacity-60' : 'border border-blue-300 bg-white text-blue-700 hover:bg-blue-50'); ?>" <?php echo e($buttonDisabled ? 'disabled title="Ya existe un comprobante subido"' : ''); ?>>
                                                    Marcar enviado / Subir comprobante
                                                </button>

                                            <div id="upload-form-<?php echo e($pago->id); ?>" style="display:none;" class="rounded-2xl border border-zinc-200 bg-white p-3">
                                                <?php if($shippingFile): ?>
                                                    <p class="text-sm text-zinc-700">Ya existe un archivo subido: <strong><?php echo e($shippingFile['original_name'] ?? basename($shippingFile['path'] ?? '')); ?></strong></p>
                                                    <p class="text-sm text-zinc-600">Tipo: <?php echo e($shippingFile['type'] ?? '—'); ?>. Para cambiarlo, elimina el existente primero.</p>
                                                <?php else: ?>
                                                    <form method="POST" action="<?php echo e(route('dashboard.proximos-envios.upload-shipping', $pago)); ?>" enctype="multipart/form-data">
                                                        <?php echo csrf_field(); ?>
                                                        <label class="text-sm font-semibold text-zinc-700">Aquí puedes subir el PDF o foto de la ruta de envío</label>
                                                        <div class="mt-2 flex items-center gap-2">
                                                            <input type="file" name="shipping_file" accept=".pdf,image/*" class="rounded-md border border-zinc-200" required />
                                                            <button type="submit" class="inline-flex items-center justify-center rounded-full border border-blue-300 bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Subir y marcar enviado</button>
                                                        </div>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="px-5 py-5">
                            <div class="grid gap-4 lg:grid-cols-12">
                                <article class="rounded-3xl border border-zinc-200 bg-zinc-50 p-5 lg:col-span-4">
                                    <p class="text-[13px] font-semibold uppercase tracking-wide text-zinc-700">Orden y producto</p>
                                    <div class="mt-3 flex items-start justify-between gap-3">
                                        <h3 class="text-4xl leading-[1.1] font-semibold text-zinc-900"><?php echo e($productTitle); ?></h3>
                                        <svg viewBox="0 0 24 24" class="h-16 w-16 text-zinc-400" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M2 14.5c.3-1.1.8-2.5 2-3.2 1.2-.7 3.4-.8 8-.8s6.8.1 8 .8c1.2.7 1.7 2.1 2 3.2"></path>
                                            <path d="M6.3 14.5 8.4 18c.4.6 1 .9 1.7.9h3.8c.7 0 1.3-.3 1.7-.9l2.1-3.5"></path>
                                            <path d="M3.8 11.9 5.7 7c.3-.9 1.2-1.5 2.2-1.5h8.2c1 0 1.9.6 2.2 1.5l1.9 4.9"></path>
                                        </svg>
                                    </div>

                                    <p class="mt-3 text-sm text-zinc-700"><span class="font-semibold text-zinc-900">Especificaciones:</span> <?php echo e($productSpecsLine); ?></p>

                                    <?php if($frameColor !== ''): ?>
                                        <p class="mt-2 text-sm text-zinc-700"><span class="font-semibold text-zinc-900">Color de montura:</span> <?php echo e($frameColor); ?></p>
                                    <?php endif; ?>

                                    <div class="mt-4 grid grid-cols-2 gap-3">
                                        <?php $__currentLoopData = $productMeasureItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $measure): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="flex items-start gap-2 rounded-2xl bg-white px-3 py-2.5 ring-1 ring-zinc-200">
                                                <span class="mt-0.5 inline-flex h-7 w-7 items-center justify-center rounded-xl bg-zinc-100 text-zinc-600">
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M4 12h16"></path>
                                                        <path d="M12 4v16"></path>
                                                    </svg>
                                                </span>
                                                <div>
                                                    <p class="text-sm text-zinc-600"><?php echo e($measure['label']); ?></p>
                                                    <p class="text-3xl font-semibold text-zinc-900 leading-none"><?php echo e($measure['value']); ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </article>

                                <article class="rounded-3xl border border-zinc-200 bg-zinc-50 p-5 lg:col-span-8">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-[13px] font-semibold uppercase tracking-wide text-zinc-700">Datos del cliente y lentes</p>
                                        <span class="text-sm font-medium text-rose-600">Eliminar</span>
                                    </div>

                                    <div class="mt-4 grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
                                        <div class="space-y-2 lg:border-r lg:border-zinc-200 lg:pr-4">
                                            <div class="flex items-start justify-between gap-3 py-1">
                                                <span class="font-semibold text-zinc-900">Nombre</span>
                                                <span class="text-right text-zinc-800"><?php echo e($buyer['nombre'] ?? ($usuario->nombre ?? ($guest['nombre'] ?? '—'))); ?></span>
                                            </div>
                                            <div class="flex items-start justify-between gap-3 py-1">
                                                <span class="font-semibold text-zinc-900">Correo</span>
                                                <span class="text-right text-zinc-800 break-all"><?php echo e($buyer['correo'] ?? ($usuario->correo ?? ($guest['correo'] ?? '—'))); ?></span>
                                            </div>
                                            <div class="flex items-start justify-between gap-3 py-1">
                                                <span class="font-semibold text-zinc-900">Documento</span>
                                                <span class="text-right text-zinc-800"><?php echo e(trim((string) (($buyer['tipo_documento'] ?? ($perfil->tipo_documento ?? ($guest['tipo_documento'] ?? ''))) . ' ' . ($buyer['numero_documento'] ?? ($perfil->numero_documento ?? ($guest['numero_documento'] ?? ''))))) ?: '—'); ?></span>
                                            </div>
                                            <div class="flex items-start justify-between gap-3 py-1">
                                                <span class="font-semibold text-zinc-900">Teléfono</span>
                                                <span class="text-right text-zinc-800"><?php echo e($buyer['telefono'] ?? ($perfil->telefono ?? ($guest['telefono'] ?? '—'))); ?></span>
                                            </div>
                                            <div class="flex items-start justify-between gap-3 py-1">
                                                <span class="font-semibold text-zinc-900">Ciudad</span>
                                                <span class="text-right text-zinc-800"><?php echo e($buyer['ciudad'] ?? ($perfil->ciudad ?? ($guest['ciudad'] ?? '—'))); ?></span>
                                            </div>

                                            <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-zinc-900 shadow-sm">
                                                <?php echo e($buyer['direccion'] ?? ($perfil->direccion ?? ($guest['direccion'] ?? '—'))); ?>

                                            </div>

                                            <?php ($fechaNacimiento = $buyer['fecha_nacimiento'] ?? ($perfil->fecha_nacimiento ?? null)); ?>
                                            <?php if($fechaNacimiento): ?>
                                                <div class="flex items-start justify-between gap-3 py-1">
                                                    <span class="font-semibold text-zinc-900">Fecha nacimiento</span>
                                                    <span class="text-right text-zinc-800"><?php echo e($fechaNacimiento); ?></span>
                                                </div>
                                            <?php endif; ?>

                                            <?php ($generoRaw = $buyer['genero'] ?? ($perfil->genero ?? null)); ?>
                                            <?php ($genero = $generoRaw === 'female' ? 'Mujer' : ($generoRaw === 'male' ? 'Hombre' : ($generoRaw === 'other' ? 'Otro' : $generoRaw))); ?>
                                            <?php if($genero): ?>
                                                <div class="flex items-start justify-between gap-3 py-1">
                                                    <span class="font-semibold text-zinc-900">Género</span>
                                                    <span class="text-right text-zinc-800"><?php echo e($genero); ?></span>
                                                </div>
                                            <?php endif; ?>

                                        </div>

                                        <div class="space-y-2 text-zinc-800">
                                            <?php if(!$lentes): ?>
                                                <p class="text-sm text-zinc-600">Sin fórmula (solo montura).</p>
                                            <?php else: ?>
                                                <?php ($tipoLente = (string) ($lentes['tipo_lente_necesitas'] ?? '')); ?>
                                                <?php ($lensType = (string) ($lentes['lens_type'] ?? '')); ?>
                                                <?php ($naraLevel = (string) ($lentes['nara_level'] ?? '')); ?>
                                                <?php ($lensColor = (string) ($lentes['color'] ?? '')); ?>
                                                <?php ($rxSphereMax = $lentes['rx_sphere_max'] ?? null); ?>
                                                <?php ($rxCylMax = $lentes['rx_cyl_max'] ?? null); ?>
                                                <?php ($precioMontura = $lentes['precio_montura'] ?? null); ?>
                                                <?php ($precioLentes = $lentes['precio_lentes'] ?? null); ?>
                                                <?php ($precioTotal = $lentes['precio_total'] ?? null); ?>
                                                <?php ($prescriptionIdValue = (int) ($meta['prescription_id'] ?? $carritoMeta['prescription_id'] ?? 0)); ?>
                                                <?php ($sinFormula = (bool) ($lentes['no_prescription'] ?? false)
                                                    || (bool) ($lentes['plano_neutro'] ?? false)
                                                    || ((string) ($lentes['formula_mode'] ?? '') === 'sin_formula')
                                                    || ($prescriptionIdValue === 0 && ($tipoLente === 'con_aumento_monofocal' || $tipoLente === ''))); ?>
                                                <?php ($tipoLenteLabel = $sinFormula ? 'Sin fórmula' : ($tipoLente === 'con_aumento_monofocal' ? 'Monofocal' : ($tipoLente === 'progresivos' ? 'Progresivos' : ($tipoLente === 'bifocal' ? 'Bifocal' : ($tipoLente === 'ocupacional' ? 'Ocupacional' : ($tipoLente ?: '—')))))); ?>
                                                <?php ($lensTypeLabel = \App\Services\Gafas\GafaLensPricing::lensTypeDisplayLabel($lensType, $polyEnabled, $sinFormula)); ?>
                                                <?php ($lensTypeSummaryLabel = $sinFormula && $polyEnabled && $lensTypeLabel !== '' ? $lensTypeLabel . ' (Nara POLY 1.59)' : $lensTypeLabel); ?>
                                                <?php ($naraLabel = \App\Services\Gafas\GafaLensPricing::naraLevelOptions()[$naraLevel] ?? strtoupper($naraLevel)); ?>

                                                <p><span class="font-semibold">Tipo:</span> <?php echo e($tipoLenteLabel); ?></p>
                                                <?php if($lensType): ?>
                                                    <p><span class="font-semibold"><?php echo e($sinFormula ? 'Tratamiento' : 'Diseño'); ?>:</span> <?php echo e($lensTypeSummaryLabel); ?></p>
                                                <?php elseif($sinFormula): ?>
                                                    <p><span class="font-semibold">Tratamiento:</span> Solo montura</p>
                                                <?php endif; ?>
                                                <?php if(!$sinFormula && $naraLevel): ?>
                                                    <p><span class="font-semibold">NARA:</span> <?php echo e($naraLabel); ?></p>
                                                <?php endif; ?>
                                                <?php if($lensColor): ?>
                                                    <p><span class="font-semibold">Color:</span> <?php echo e($lensColor); ?></p>
                                                <?php endif; ?>
                                                <?php if(!$sinFormula): ?>
                                                    <?php ($od = is_array($manual['od'] ?? null) ? $manual['od'] : []); ?>
                                                    <?php ($oi = is_array($manual['oi'] ?? null) ? $manual['oi'] : []); ?>
                                                    <?php ($hasManualRx = (string) ($od['esfera'] ?? '') !== '' || (string) ($od['cilindro'] ?? '') !== '' || (string) ($od['eje'] ?? '') !== '' || (string) ($oi['esfera'] ?? '') !== '' || (string) ($oi['cilindro'] ?? '') !== '' || (string) ($oi['eje'] ?? '') !== ''); ?>

                                                    <?php if($hasManualRx): ?>
                                                        <div class="pt-2">
                                                            <p class="text-[12px] font-semibold uppercase tracking-wide text-zinc-700">Fórmula</p>
                                                            <div class="mt-2 grid gap-2 sm:grid-cols-2 text-[12px] text-zinc-800">
                                                                <div class="rounded-2xl border border-zinc-200 bg-white px-3 py-2">
                                                                    <p class="font-semibold text-zinc-900">Ojo derecho (OD)</p>
                                                                    <p>Esfera: <?php echo e((string) ($od['esfera'] ?? '') !== '' ? $od['esfera'] : '—'); ?></p>
                                                                    <p>Cilindro: <?php echo e((string) ($od['cilindro'] ?? '') !== '' ? $od['cilindro'] : '—'); ?></p>
                                                                    <p>Eje: <?php echo e((string) ($od['eje'] ?? '') !== '' ? $od['eje'] : '—'); ?></p>
                                                                    <p>Adición: <?php echo e((string) ($od['adicion'] ?? '') !== '' ? $od['adicion'] : '—'); ?></p>
                                                                </div>
                                                                <div class="rounded-2xl border border-zinc-200 bg-white px-3 py-2">
                                                                    <p class="font-semibold text-zinc-900">Ojo izquierdo (OI)</p>
                                                                    <p>Esfera: <?php echo e((string) ($oi['esfera'] ?? '') !== '' ? $oi['esfera'] : '—'); ?></p>
                                                                    <p>Cilindro: <?php echo e((string) ($oi['cilindro'] ?? '') !== '' ? $oi['cilindro'] : '—'); ?></p>
                                                                    <p>Eje: <?php echo e((string) ($oi['eje'] ?? '') !== '' ? $oi['eje'] : '—'); ?></p>
                                                                    <p>Adición: <?php echo e((string) ($oi['adicion'] ?? '') !== '' ? $oi['adicion'] : '—'); ?></p>
                                                                </div>
                                                            </div>

                                                            <div class="mt-2 rounded-2xl border border-zinc-200 bg-white px-3 py-2 text-[12px] text-zinc-800">
                                                                <p><span class="font-semibold">DNP:</span> <?php echo e((string) ($manual['distancia_pupilar'] ?? '') !== '' ? $manual['distancia_pupilar'] : '—'); ?></p>
                                                                <p><span class="font-semibold">Año de nacimiento:</span> <?php echo e((string) ($manual['ano_nacimiento'] ?? '') !== '' ? $manual['ano_nacimiento'] : '—'); ?></p>
                                                            </div>
                                                        </div>
                                                    <?php elseif($rxSphereMax !== null || $rxCylMax !== null): ?>
                                                        <p><span class="font-semibold">RX máx:</span> Esfera <?php echo e($rxSphereMax ?? '—'); ?>, Cilindro <?php echo e($rxCylMax ?? '—'); ?></p>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <?php if($precioMontura !== null || $precioLentes !== null || $precioTotal !== null): ?>
                                                    <div class="space-y-0.5 pt-2">
                                                        <?php if($precioMontura !== null): ?>
                                                            <p><span class="font-semibold">Montura:</span> <?php echo e(number_format((float) $precioMontura, 0, ',', '.')); ?> <?php echo e($pago->moneda); ?></p>
                                                        <?php endif; ?>
                                                        <?php if($precioLentes !== null): ?>
                                                            <p><span class="font-semibold">Lentes:</span> <?php echo e(number_format((float) $precioLentes, 0, ',', '.')); ?> <?php echo e($pago->moneda); ?></p>
                                                        <?php endif; ?>
                                                        <?php if($precioTotal !== null): ?>
                                                            <p><span class="font-semibold">Total:</span> <?php echo e(number_format((float) $precioTotal, 0, ',', '.')); ?> <?php echo e($pago->moneda); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php echo $__env->make('admin.partials.auto_refresh', [
        'enabled' => true,
        'mode' => 'on-change',
        'checkUrl' => route('dashboard.proximos-envios.pulse'),
        'watchToken' => (string) ($autoRefreshToken ?? ''),
        'intervalMs' => 10000,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-toggle-form]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var id = btn.getAttribute('data-toggle-form');
                    var el = document.getElementById(id);
                    if (!el) return;
                    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard_empty_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\norla\Desktop\Proyectos con Ronaldo\copia_optica\resources\views/dashboard/proximos_envios.blade.php ENDPATH**/ ?>