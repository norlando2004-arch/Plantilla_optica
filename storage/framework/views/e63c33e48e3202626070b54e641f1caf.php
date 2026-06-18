<?php $__env->startSection('title', 'Ubicación (Mapa)'); ?>

<?php $__env->startSection('content'); ?>
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Ubicación</h1>
            <p class="mt-1 text-sm text-zinc-600">Configura nombre, descripción, imagen superior y coordenadas del mapa para cada sede.</p>
        </div>
        <a href="<?php echo e(url('/')); ?>#ubicacion" class="rounded-xl px-3 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-200 hover:bg-zinc-50">Ver landing</a>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-12">
        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-12">
            <form id="landingLocationForm" method="POST" action="<?php echo e(route('configuracion.landing-location.update')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>

                <div id="locationCardsWrap" class="grid gap-6 lg:grid-cols-3">
                    <?php $__currentLoopData = $baseLocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="rounded-2xl border-2 border-zinc-300 bg-white p-5 shadow-sm" data-location-card data-index="<?php echo e($i); ?>">
                            <div class="mb-4 flex items-center justify-between gap-2 border-b-2 border-zinc-300 pb-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-900 text-white text-xs font-bold" data-location-number>
                                        <?php echo e($i + 1); ?>

                                    </div>
                                    <p class="text-sm font-bold text-zinc-800" data-location-title>Sede <?php echo e($i + 1); ?></p>
                                </div>
                                <button type="button" class="text-xs font-semibold text-red-600 hover:text-red-700" data-delete-btn>Eliminar</button>
                            </div>
                            <div class="mt-4 grid gap-4">
                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Nombre</label>
                                    <input
                                        data-name-input
                                        data-index="<?php echo e($i); ?>"
                                        name="locations[<?php echo e($i); ?>][name]"
                                        value="<?php echo e(old("locations.$i.name", $loc['name'])); ?>"
                                        class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm"
                                        placeholder="Bogotá"
                                    >
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Descripción</label>
                                    <textarea
                                        data-description-input
                                        data-index="<?php echo e($i); ?>"
                                        name="locations[<?php echo e($i); ?>][description]"
                                        maxlength="180"
                                        class="mt-2 min-h-[92px] w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm"
                                        placeholder="Escribe una descripción para esta sede"
                                    ><?php echo e(old("locations.$i.description", $loc['description'] ?? '')); ?></textarea>
                                    <p class="mt-1 text-[11px] text-zinc-500">Máximo 180 caracteres.</p>
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-zinc-700">Imagen de arriba</label>
                                    <input
                                        data-image-input
                                        data-index="<?php echo e($i); ?>"
                                        name="locations[<?php echo e($i); ?>][image_file]"
                                        type="file"
                                        accept="image/*"
                                        class="mt-2 block w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white"
                                    >
                                    <label class="mt-2 inline-flex items-center gap-2 text-xs text-zinc-600">
                                        <input type="checkbox" name="locations[<?php echo e($i); ?>][remove_image]" value="1" class="rounded border-zinc-300">
                                        Quitar imagen subida y volver a imagen por defecto
                                    </label>
                                </div>

                                <div class="rounded-xl border border-zinc-200 bg-white p-3">
                                    <p class="text-xs font-semibold text-zinc-700">Ubicación del mapa (abajo)</p>
                                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                        <div>
                                            <label class="text-[11px] font-semibold text-zinc-600">Latitud</label>
                                            <input data-lat-input data-index="<?php echo e($i); ?>" name="locations[<?php echo e($i); ?>][lat]" value="<?php echo e(old("locations.$i.lat", $loc['lat'])); ?>" class="mt-1 w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm" inputmode="decimal">
                                        </div>
                                        <div>
                                            <label class="text-[11px] font-semibold text-zinc-600">Longitud</label>
                                            <input data-lng-input data-index="<?php echo e($i); ?>" name="locations[<?php echo e($i); ?>][lng]" value="<?php echo e(old("locations.$i.lng", $loc['lng'])); ?>" class="mt-1 w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm" inputmode="decimal">
                                        </div>
                                        <div>
                                            <label class="text-[11px] font-semibold text-zinc-600">Zoom</label>
                                            <input data-zoom-input data-index="<?php echo e($i); ?>" name="locations[<?php echo e($i); ?>][zoom]" value="<?php echo e(old("locations.$i.zoom", $loc['zoom'])); ?>" class="mt-1 w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm" inputmode="numeric">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="mt-5 flex items-center justify-between gap-3">
                    <p class="text-xs text-zinc-500">Puedes agregar todas las sedes que necesites; se acomodan automáticamente en el landing.</p>
                    <button type="button" id="addLocationBtn" class="rounded-xl border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-50">
                        + Agregar sede
                    </button>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-2xl bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-800">Guardar</button>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border border-zinc-200 bg-white p-6 lg:col-span-12">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold">Vista previa del landing</h2>
                <span class="text-xs text-zinc-500">Así se ve en la página</span>
            </div>

            <div class="mt-4 rounded-3xl border border-zinc-200 bg-white p-8">
                <h3 class="text-center text-4xl font-bold text-black">Ubicación</h3>
                <div id="previewLocations" class="mt-8 grid gap-6 lg:grid-cols-3"></div>
            </div>

            <div class="mt-4 rounded-2xl border border-zinc-200 bg-white p-4">
                <p class="text-xs font-semibold text-zinc-600">Estado</p>
                <p id="previewStatus" class="mt-1 text-sm text-zinc-700">Listo para guardar.</p>
            </div>
        </section>
    </div>

    <script>
        (() => {
            const form = document.getElementById('landingLocationForm');
            const cardsWrap = document.getElementById('locationCardsWrap');
            const addBtn = document.getElementById('addLocationBtn');
            const previewWrap = document.getElementById('previewLocations');
            const statusEl = document.getElementById('previewStatus');
            if (!form || !cardsWrap || !addBtn || !previewWrap || !statusEl) return;

            const baseImages = <?php echo json_encode(array_map(fn ($l) => $l['image_url'], $baseLocations), 512) ?>;
            const selectedImages = [...baseImages];
            let objectUrls = new Map();

            const toNum = (value, fallback) => {
                const cleaned = String(value ?? '').trim().replace(',', '.');
                const n = Number(cleaned);
                return Number.isFinite(n) ? n : fallback;
            };

            const clamp = (n, min, max) => Math.max(min, Math.min(max, n));

            const mapSrc = (lat, lng, zoom) => {
                return `https://www.google.com/maps?q=${encodeURIComponent(lat + ',' + lng)}&z=${encodeURIComponent(String(zoom))}&output=embed`;
            };

            const getCards = () => Array.from(cardsWrap.querySelectorAll('[data-location-card]'));

            const buildCard = (index) => {
                const div = document.createElement('div');
                div.innerHTML = `<article class="rounded-2xl border-2 border-zinc-300 bg-white p-5 shadow-sm" data-location-card data-index="${index}">
                    <div class="mb-4 flex items-center justify-between gap-2 border-b-2 border-zinc-300 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-900 text-white text-xs font-bold" data-location-number>${index + 1}</div>
                            <p class="text-sm font-bold text-zinc-800" data-location-title>Sede ${index + 1}</p>
                        </div>
                        <button type="button" class="text-xs font-semibold text-red-600 hover:text-red-700" data-delete-btn>Eliminar</button>
                    </div>
                    <div class="mt-4 grid gap-4">
                        <div>
                            <label class="text-xs font-semibold text-zinc-700">Nombre</label>
                            <input data-name-input data-index="${index}" name="locations[${index}][name]" value="" class="mt-2 w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm" placeholder="Bogotá">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-zinc-700">Descripción</label>
                            <textarea data-description-input data-index="${index}" name="locations[${index}][description]" maxlength="180" class="mt-2 min-h-[92px] w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm" placeholder="Escribe una descripción para esta sede"></textarea>
                            <p class="mt-1 text-[11px] text-zinc-500">Máximo 180 caracteres.</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-zinc-700">Imagen de arriba</label>
                            <input data-image-input data-index="${index}" name="locations[${index}][image_file]" type="file" accept="image/*" class="mt-2 block w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white">
                            <label class="mt-2 inline-flex items-center gap-2 text-xs text-zinc-600">
                                <input type="checkbox" name="locations[${index}][remove_image]" value="1" class="rounded border-zinc-300">
                                Quitar imagen subida y volver a imagen por defecto
                            </label>
                        </div>
                        <div class="rounded-xl border border-zinc-200 bg-white p-3">
                            <p class="text-xs font-semibold text-zinc-700">Ubicación del mapa (abajo)</p>
                            <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                <div>
                                    <label class="text-[11px] font-semibold text-zinc-600">Latitud</label>
                                    <input data-lat-input data-index="${index}" name="locations[${index}][lat]" value="4.7110" class="mt-1 w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm" inputmode="decimal">
                                </div>
                                <div>
                                    <label class="text-[11px] font-semibold text-zinc-600">Longitud</label>
                                    <input data-lng-input data-index="${index}" name="locations[${index}][lng]" value="-74.0721" class="mt-1 w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm" inputmode="decimal">
                                </div>
                                <div>
                                    <label class="text-[11px] font-semibold text-zinc-600">Zoom</label>
                                    <input data-zoom-input data-index="${index}" name="locations[${index}][zoom]" value="15" class="mt-1 w-full rounded-lg border border-zinc-200 px-3 py-2 text-sm" inputmode="numeric">
                                </div>
                            </div>
                        </div>
                    </div>
                </article>`;
                return div.firstElementChild;
            };

            const reindexCards = () => {
                getCards().forEach((card, i) => {
                    card.setAttribute('data-index', i);
                    card.querySelector('[data-location-number]').textContent = String(i + 1);
                    card.querySelector('[data-location-title]').textContent = `Sede ${i + 1}`;

                    card.querySelectorAll('[data-name-input], [data-description-input], [data-image-input], [data-lat-input], [data-lng-input], [data-zoom-input]').forEach((field) => {
                        const fieldName = field.hasAttribute('data-name-input') ? 'name' :
                                        field.hasAttribute('data-description-input') ? 'description' :
                                        field.hasAttribute('data-image-input') ? 'image_file' :
                                        field.hasAttribute('data-lat-input') ? 'lat' :
                                        field.hasAttribute('data-lng-input') ? 'lng' : 'zoom';
                        field.setAttribute('name', `locations[${i}][${fieldName}]`);
                        field.setAttribute('data-index', i);
                    });
                });
            };

            const render = () => {
                const cards = [];
                getCards().forEach((card, i) => {
                    const nameInput = card.querySelector('[data-name-input]');
                    const descriptionInput = card.querySelector('[data-description-input]');
                    const latInput = card.querySelector('[data-lat-input]');
                    const lngInput = card.querySelector('[data-lng-input]');
                    const zoomInput = card.querySelector('[data-zoom-input]');

                    const name = (nameInput?.value || '').trim() || `Sede ${i + 1}`;
                    const description = (descriptionInput?.value || '').trim();
                    const lat = clamp(toNum(latInput?.value, 4.7110), -90, 90);
                    const lng = clamp(toNum(lngInput?.value, -74.0721), -180, 180);
                    const zoom = clamp(Math.round(toNum(zoomInput?.value, 15)), 1, 20);
                    const image = selectedImages[i] || '/images/optica.png';
                    const safeDescription = description
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');

                    cards.push(`<article><div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white"><img src="${image}" alt="${name}" class="h-48 w-full object-cover" loading="lazy" draggable="false"></div><h4 class="mt-4 text-2xl font-bold text-zinc-900">${name}</h4><div class="mt-3 overflow-hidden rounded-xl border border-zinc-200 bg-white"><iframe class="h-64 w-full" src="${mapSrc(lat, lng, zoom)}" title="Mapa ${name}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>${safeDescription ? `<p class="mt-3 whitespace-pre-line break-words [overflow-wrap:anywhere] text-sm leading-6 text-zinc-600">${safeDescription}</p>` : ''}</article>`);
                });

                previewWrap.innerHTML = cards.join('');
                statusEl.textContent = 'Vista previa actualizada (aún no guardada).';
            };

            const attachEventListeners = () => {
                const imageInputs = Array.from(form.querySelectorAll('[data-image-input]'));
                imageInputs.forEach((input) => {
                    input.removeEventListener('change', handleImageChange);
                    input.addEventListener('change', handleImageChange);
                });

                const deleteButtons = Array.from(cardsWrap.querySelectorAll('[data-delete-btn]'));
                deleteButtons.forEach((btn) => {
                    btn.removeEventListener('click', handleDelete);
                    btn.addEventListener('click', handleDelete);
                });
            };

            const handleImageChange = (e) => {
                const input = e.target;
                const idx = Number(input.getAttribute('data-index') || 0);
                const file = input.files && input.files[0] ? input.files[0] : null;
                if (!file) {
                    if (objectUrls.has(idx)) {
                        URL.revokeObjectURL(objectUrls.get(idx));
                        objectUrls.delete(idx);
                    }
                    selectedImages[idx] = baseImages[idx] || '/images/optica.png';
                    render();
                    return;
                }

                if (objectUrls.has(idx)) {
                    URL.revokeObjectURL(objectUrls.get(idx));
                }

                const url = URL.createObjectURL(file);
                objectUrls.set(idx, url);
                selectedImages[idx] = url;
                render();
            };

            const handleDelete = (e) => {
                e.preventDefault();
                const btn = e.target;
                const card = btn.closest('[data-location-card]');
                if (!card) return;

                const cards = getCards();
                if (cards.length <= 3) {
                    alert('Debes mantener mínimo 3 sedes.');
                    return;
                }

                if (objectUrls.has(cards.indexOf(card))) {
                    URL.revokeObjectURL(objectUrls.get(cards.indexOf(card)));
                    objectUrls.delete(cards.indexOf(card));
                }

                card.remove();
                reindexCards();
                attachEventListeners();
                render();
            };

            addBtn.addEventListener('click', () => {
                const nextIndex = getCards().length;
                const newCard = buildCard(nextIndex);
                cardsWrap.appendChild(newCard);
                selectedImages[nextIndex] = '/images/optica.png';
                reindexCards();
                attachEventListeners();
                render();
            });

            form.addEventListener('input', render);
            attachEventListeners();
            reindexCards();
            render();

            window.addEventListener('beforeunload', () => {
                objectUrls.forEach((url) => {
                    URL.revokeObjectURL(url);
                });
            });
        })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.dashboard_layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\norla\Desktop\Proyectos con Ronaldo\copia_optica\resources\views/admin/landing_location.blade.php ENDPATH**/ ?>