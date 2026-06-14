@php($isEdit = isset($producto))
@php($meta = $isEdit && is_array($producto->meta) ? $producto->meta : [])
@php($img = (string)($meta['imagen_url'] ?? ''))
@php($manualImg = (string)($meta['imagen_url_manual'] ?? ((str_starts_with($img, 'data:')) ? '' : $img)))
@php($extraImgs = is_array($meta['imagenes'] ?? null) ? array_values(array_filter(array_map(static fn ($u) => is_string($u) ? trim($u) : '', $meta['imagenes']), static fn ($u) => $u !== '')) : [])
@php($extraImgsText = old('imagenes', implode("\n", $extraImgs)))

@php($caracteristicas = $isEdit && is_array($producto->caracteristicas) ? $producto->caracteristicas : [])
@php($medidas = is_array($caracteristicas['medidas'] ?? null) ? $caracteristicas['medidas'] : [])
@php($incluyeDefault = old('incluye', (string) ($caracteristicas['incluye'] ?? $meta['incluye'] ?? 'No especificado')))

@php($categoriaDefault = old('categoria', $isEdit ? ($producto->genero_objetivo ?? 'male') : 'male'))
@php($materialDefault = old('material_montura', $isEdit ? ($producto->material_montura ?? '') : ''))
@php($frameColorOptions = [
    ['name' => 'Gris', 'hex' => '#cec9bc'],
    ['name' => 'Rosa', 'hex' => '#e6c0c3'],
    ['name' => 'Negro', 'hex' => '#2a2020'],
    ['name' => 'Azul', 'hex' => '#b6d6e7'],
    ['name' => 'Marron', 'hex' => '#6f4e37'],
    ['name' => 'Carey', 'hex' => '#7b5a46'],
    ['name' => 'Transparente', 'hex' => '#e5e7eb'],
    ['name' => 'Rojo', 'hex' => '#9f2b2b'],
    ['name' => 'Verde', 'hex' => '#466b48'],
    ['name' => 'Morado', 'hex' => '#6d4c8b'],
    ['name' => 'Dorado', 'hex' => '#c6a75a'],
    ['name' => 'Plateado', 'hex' => '#b5bcc9'],
    ['name' => 'Nude', 'hex' => '#d7b39d'],
    ['name' => 'Blanco', 'hex' => '#f5f5f4'],
])
@php($frameColorDefaultRaw = old('color', $isEdit ? ((string) ($producto->color ?? '')) : ''))
@php($frameColorDefault = trim($frameColorDefaultRaw) !== '' ? $frameColorDefaultRaw : 'Gris')

@php($fmtCm = function ($v) {
    if ($v === null || $v === '') return '';
    $v = (float) $v;
    $s = rtrim(rtrim(number_format($v, 1, '.', ''), '0'), '.');
    return $s;
})

@php($precioDefault = $isEdit && $producto->precio !== null ? number_format((float)$producto->precio, 0, ',', '.') : '')
@php($precioOfertaDefault = $isEdit && $producto->precio_oferta !== null ? number_format((float)$producto->precio_oferta, 0, ',', '.') : '')

@php($placeholderSvg = "<svg xmlns='http://www.w3.org/2000/svg' width='1200' height='900' viewBox='0 0 1200 900'><rect width='1200' height='900' fill='#f4f4f5'/><path d='M420 585l110-140 120 150 90-110 160 200H420z' fill='#d4d4d8'/><circle cx='520' cy='360' r='44' fill='#d4d4d8'/><text x='50%' y='85%' dominant-baseline='middle' text-anchor='middle' fill='#a1a1aa' font-family='Arial, sans-serif' font-size='32'>Sin imagen</text></svg>")
@php($placeholderImg = 'data:image/svg+xml;utf8,' . rawurlencode($placeholderSvg))
@php($midZoom = 0.99)
@php($posX = (float) old('image_pos_x', $meta['image_pos_x'] ?? 50))
@php($posY = (float) old('image_pos_y', $meta['image_pos_y'] ?? 50))
@php($zoom = (float) old('image_zoom', $meta['image_zoom'] ?? $midZoom))
@php($zoom = max(0.5, min(1, $zoom)))
@php($zoomPct = $zoom <= $midZoom
    ? (($zoom - 0.5) / max(0.0001, ($midZoom - 0.5))) * 50
    : 50 + (($zoom - $midZoom) / max(0.0001, (1 - $midZoom))) * 50)
@php($t = max(0, min(1, ($zoom - 0.5) / 0.5)))
@php($posXEff = 50 + (($posX - 50) * $t))
@php($posYEff = 50 + (($posY - 50) * $t))

<div class="grid gap-5 lg:grid-cols-2">
    <div class="rounded-3xl border border-zinc-200 bg-white p-5">
        <p class="text-sm font-semibold text-zinc-900">Información</p>

        <div class="mt-4 grid gap-4">
            <div>
                <label class="text-xs font-semibold text-zinc-700">Nombre</label>
                <input name="nombre" value="{{ old('nombre', $isEdit ? $producto->nombre : '') }}" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div class="grid gap-2">
                    <label class="text-sm font-semibold text-zinc-700">Categoría</label>
                    <select name="categoria" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">
                        <option value="male" {{ $categoriaDefault === 'male' ? 'selected' : '' }}>Hombre</option>
                        <option value="female" {{ $categoriaDefault === 'female' ? 'selected' : '' }}>Mujer</option>
                        <option value="ninos" {{ $categoriaDefault === 'ninos' ? 'selected' : '' }}>Niños</option>
                        <option value="ninas" {{ $categoriaDefault === 'ninas' ? 'selected' : '' }}>Niñas</option>
                        <option value="unisex" {{ $categoriaDefault === 'unisex' ? 'selected' : '' }}>Todos</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-zinc-700">Material</label>
                    <input name="material_montura" value="{{ $materialDefault }}" placeholder="TR90" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold text-zinc-700">Medidas (cm)</p>
                <div class="mt-2 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-zinc-600">Ancho total de la montura</label>
                        <input name="ancho_total_montura" value="{{ old('ancho_total_montura', $fmtCm($medidas['ancho_total_montura_cm'] ?? null)) }}" placeholder="13.9" inputmode="decimal" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-zinc-600">Ancho del lente</label>
                        <input name="ancho_lente" value="{{ old('ancho_lente', $fmtCm($medidas['ancho_lente_cm'] ?? null)) }}" placeholder="5.0" inputmode="decimal" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-zinc-600">Alto del lente</label>
                        <input name="alto_lente" value="{{ old('alto_lente', $fmtCm($medidas['alto_lente_cm'] ?? null)) }}" placeholder="4.5" inputmode="decimal" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-zinc-600">Puente</label>
                        <input name="puente" value="{{ old('puente', $fmtCm($medidas['puente_cm'] ?? null)) }}" placeholder="1.9" inputmode="decimal" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-zinc-600">Largo de patillas</label>
                        <input name="largo_patillas" value="{{ old('largo_patillas', $fmtCm($medidas['largo_patillas_cm'] ?? null)) }}" placeholder="14.2" inputmode="decimal" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                    </div>
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-zinc-700">Descripción</label>
                <textarea name="descripcion" rows="4" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">{{ old('descripcion', $isEdit ? ($producto->descripcion ?? '') : '') }}</textarea>
            </div>

            <div>
                <label class="text-xs font-semibold text-zinc-700">Incluye</label>
                <input name="incluye" value="{{ $incluyeDefault }}" placeholder="Estuche, paño y líquido" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
            </div>
        </div>
    </div>

    <div class="grid gap-5">
        <div class="rounded-3xl border border-zinc-200 bg-white p-5">
            <p class="text-sm font-semibold text-zinc-900">Precio</p>
            <p class="mt-1 text-sm text-zinc-500">Puedes usar punto para miles. Ej: 50.000</p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-xs font-semibold text-zinc-700">Precio</label>
                    <input name="precio" value="{{ old('precio', $precioDefault) }}" placeholder="50.000" inputmode="numeric" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                </div>
                <div>
                    <label class="text-xs font-semibold text-zinc-700">Precio oferta (opcional)</label>
                    <input name="precio_oferta" value="{{ old('precio_oferta', $precioOfertaDefault) }}" placeholder="45.000" inputmode="numeric" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-zinc-200 bg-white p-5">
            <p class="text-sm font-semibold text-zinc-900">Inventario</p>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-xs font-semibold text-zinc-700">Existencias (opcional)</label>
                    <input name="existencias" value="{{ old('existencias', $isEdit ? ($producto->existencias ?? '') : '') }}" inputmode="numeric" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                </div>
                <div class="flex items-end">
                    <input type="hidden" name="esta_activo" value="0" />
                    <label class="flex items-center gap-3 rounded-2xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm">
                        <input type="checkbox" name="esta_activo" value="1" {{ old('esta_activo', $isEdit ? (int)$producto->esta_activo : 1) ? 'checked' : '' }} class="h-4 w-4 rounded border-zinc-300" />
                        Activo (visible en la web)
                    </label>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-zinc-200 bg-white p-5">
            <p class="text-sm font-semibold text-zinc-900">Imagen</p>
            <p class="mt-1 text-sm text-zinc-500">Sube un archivo o usa URL manual. El archivo subido tiene prioridad.</p>

            <div class="mt-4">
                <label class="text-xs font-semibold text-zinc-700">Archivo de imagen (opcional)</label>
                <input id="gafaHombreUploadedImage" type="file" name="uploaded_image" accept="image/*" class="mt-2 block w-full text-sm text-zinc-700 file:mr-3 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800" />
                @if($isEdit && !empty($meta['uploaded_imagen_name']))
                    <p class="mt-2 text-xs font-semibold text-emerald-700">Imagen subida guardada: {{ $meta['uploaded_imagen_name'] }}</p>
                @endif
            </div>

            <div id="gafaHombreColorPrompt" class="mt-4 hidden rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                <p class="text-base font-semibold text-zinc-800">Color: <span id="gafaHombreColorText">{{ $frameColorDefault }}</span></p>
                <p class="mt-1 text-xs text-zinc-500">Selecciona el color de la montura para esta imagen.</p>

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    @foreach($frameColorOptions as $option)
                        <label class="inline-flex cursor-pointer items-center">
                            <input
                                type="radio"
                                name="color"
                                value="{{ $option['name'] }}"
                                class="peer sr-only"
                                {{ $frameColorDefault === $option['name'] ? 'checked' : '' }}
                            />
                            <span class="inline-flex h-9 w-9 rounded-full border-2 border-white shadow-sm ring-2 ring-zinc-300 transition peer-checked:ring-zinc-800" style="background-color: {{ $option['hex'] }};"></span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mt-4">
                <label class="text-xs font-semibold text-zinc-700">Subir imágenes por color (opcional)</label>
                <p class="mt-1 text-xs text-zinc-500">Puedes subir varias fotos y asignarle un color a cada una para que cambien igual que en gafas niñas.</p>
                <input id="gafaHombreColorImages" type="file" name="uploaded_color_images[]" accept="image/*" multiple class="mt-2 block w-full text-sm text-zinc-700 file:mr-3 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800" />

                <div id="gafaHombreColorImagesMap" class="mt-3 hidden rounded-2xl border border-zinc-200 bg-zinc-50 p-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs font-semibold text-zinc-700">Asigna color a cada imagen:</p>
                        <span id="gafaHombreColorImagesCount" class="text-[11px] font-semibold text-zinc-500"></span>
                    </div>
                    <div id="gafaHombreColorImagesRows" class="mt-3 grid gap-3 sm:grid-cols-2"></div>
                </div>
            </div>

            <input type="hidden" id="gafaHombreImageUrl" name="imagen_url" value="{{ old('imagen_url', $isEdit ? $manualImg : '') }}" />

            @if($isEdit && !empty($meta['imagen_url']))
                <div class="mt-3">
                    <label class="inline-flex items-center gap-2 text-sm text-zinc-600">
                        <input type="checkbox" name="eliminar_imagen" value="1" class="h-4 w-4 rounded border-zinc-300" />
                        Eliminar imagen
                    </label>
                </div>
            @endif

            <div hidden>
                <input type="hidden" name="image_zoom" value="{{ number_format($zoom, 2, '.', '') }}">
                <input type="hidden" name="image_pos_x" value="{{ (int) $posX }}">
                <input type="hidden" name="image_pos_y" value="{{ (int) $posY }}">
                <input id="gafaHombreZoomPct" type="range" min="0" max="100" step="1" value="{{ (int) round($zoomPct) }}">
                <input id="gafaHombreZoom" type="hidden" name="image_zoom" value="{{ number_format($zoom, 2, '.', '') }}">
                <span id="gafaHombreZoomVal"></span>
                <input id="gafaHombrePosX" name="image_pos_x" type="range" min="0" max="100" step="1" value="{{ (int) $posX }}">
                <span id="gafaHombrePosXVal"></span>
                <input id="gafaHombrePosY" name="image_pos_y" type="range" min="0" max="100" step="1" value="{{ (int) $posY }}">
                <span id="gafaHombrePosYVal"></span>
                <img id="gafaHombrePreviewBg" src="{{ $img ?: $placeholderImg }}" alt="" onerror="this.onerror=null; this.src='{{ $placeholderImg }}';">
                <img id="gafaHombrePreviewFg" src="{{ $img ?: $placeholderImg }}" alt="" onerror="this.onerror=null; this.src='{{ $placeholderImg }}';">
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const urlInput = document.getElementById('gafaHombreImageUrl');
        const previewBg = document.getElementById('gafaHombrePreviewBg');
        const previewFg = document.getElementById('gafaHombrePreviewFg');
        const uploadedInput = document.getElementById('gafaHombreUploadedImage');
        const zoomPct = document.getElementById('gafaHombreZoomPct');
        const zoomReal = document.getElementById('gafaHombreZoom');
        const posX = document.getElementById('gafaHombrePosX');
        const posY = document.getElementById('gafaHombrePosY');
        const zoomVal = document.getElementById('gafaHombreZoomVal');
        const posXVal = document.getElementById('gafaHombrePosXVal');
        const posYVal = document.getElementById('gafaHombrePosYVal');
        const colorPrompt = document.getElementById('gafaHombreColorPrompt');
        const colorText = document.getElementById('gafaHombreColorText');
        const colorInputs = Array.from(document.querySelectorAll('input[name="color"]'));
        const colorImagesInput = document.getElementById('gafaHombreColorImages');
        const colorImagesMap = document.getElementById('gafaHombreColorImagesMap');
        const colorImagesRows = document.getElementById('gafaHombreColorImagesRows');
        const colorImagesCount = document.getElementById('gafaHombreColorImagesCount');
        const colorOptions = @json($frameColorOptions);
        let colorImageObjectUrls = [];

        if (!urlInput || !previewBg || !previewFg || !zoomPct || !posX || !posY) return;

        const placeholder = @json($placeholderImg);
        const MID_ZOOM = Number(@json($midZoom));

        const setPreview = (value) => {
            const url = String(value || '').trim();
            const next = url !== '' ? url : placeholder;
            previewBg.src = next;
            previewFg.src = next;
        };

        const setPreviewFromFile = () => {
            if (!uploadedInput || !uploadedInput.files || !uploadedInput.files[0]) {
                setPreview(urlInput.value);
                return;
            }

            const fileUrl = URL.createObjectURL(uploadedInput.files[0]);
            previewBg.src = fileUrl;
            previewFg.src = fileUrl;
        };

        const hasUploadedFile = () => !!(uploadedInput && uploadedInput.files && uploadedInput.files[0]);

        const syncColorPrompt = () => {
            if (!colorPrompt) return;

            colorPrompt.classList.toggle('hidden', !hasUploadedFile());

            if (!colorText) return;
            const checked = colorInputs.find((input) => input.checked);
            colorText.textContent = checked ? checked.value : 'Gris';
        };

        const buildColorSelect = (name, selected) => {
            const select = document.createElement('select');
            select.name = name;
            select.className = 'rounded-xl border border-zinc-300 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-500';

            colorOptions.forEach((option) => {
                const op = document.createElement('option');
                op.value = String(option.name || '');
                op.textContent = String(option.name || '');
                if (op.value === selected) {
                    op.selected = true;
                }
                select.appendChild(op);
            });

            return select;
        };

        const releaseColorImageUrls = () => {
            colorImageObjectUrls.forEach((u) => {
                try {
                    URL.revokeObjectURL(u);
                } catch (_) {
                    // noop
                }
            });
            colorImageObjectUrls = [];
        };

        const syncColorImageRows = () => {
            if (!colorImagesInput || !colorImagesMap || !colorImagesRows) return;

            releaseColorImageUrls();

            const files = Array.from(colorImagesInput.files || []);
            colorImagesRows.innerHTML = '';
            colorImagesMap.classList.toggle('hidden', files.length === 0);

            if (colorImagesCount) {
                colorImagesCount.textContent = files.length > 0 ? `${files.length} imagen${files.length === 1 ? '' : 'es'}` : '';
            }

            if (!files.length) return;

            const selectedColorInput = colorInputs.find((input) => input.checked);
            const selectedColor = selectedColorInput ? selectedColorInput.value : 'Gris';

            files.forEach((file, index) => {
                const card = document.createElement('div');
                card.className = 'overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm';

                const previewWrap = document.createElement('div');
                previewWrap.className = 'relative aspect-[4/3] w-full bg-zinc-100';

                const previewImg = document.createElement('img');
                previewImg.className = 'absolute inset-0 h-full w-full object-contain';
                previewImg.alt = file.name || `Imagen ${index + 1}`;
                const objectUrl = URL.createObjectURL(file);
                colorImageObjectUrls.push(objectUrl);
                previewImg.src = objectUrl;

                previewWrap.appendChild(previewImg);

                const body = document.createElement('div');
                body.className = 'grid gap-2 p-3';

                const name = document.createElement('p');
                name.className = 'truncate text-xs font-semibold text-zinc-700';
                name.title = file.name || `Imagen ${index + 1}`;
                name.textContent = file.name || `Imagen ${index + 1}`;

                const meta = document.createElement('p');
                meta.className = 'text-[11px] text-zinc-500';
                const mb = Number(file.size || 0) / (1024 * 1024);
                meta.textContent = `${mb.toFixed(2)} MB`;

                const select = buildColorSelect('uploaded_color_images_color[]', selectedColor);

                body.appendChild(name);
                body.appendChild(meta);
                body.appendChild(select);

                card.appendChild(previewWrap);
                card.appendChild(body);
                colorImagesRows.appendChild(card);
            });
        };

        const applyFrame = () => {
            const pctRaw = Number(zoomPct.value || '50');
            const pct = Math.max(0, Math.min(100, Number.isFinite(pctRaw) ? pctRaw : 50));

            const z = pct <= 50
                ? 0.5 + (pct / 50) * (MID_ZOOM - 0.5)
                : MID_ZOOM + ((pct - 50) / 50) * (1 - MID_ZOOM);

            const x = Number(posX.value || '50');
            const y = Number(posY.value || '50');

            const factor = Math.max(0, Math.min(1, (z - 0.5) / 0.5));
            const xEff = 50 + (x - 50) * factor;
            const yEff = 50 + (y - 50) * factor;

            previewBg.style.objectPosition = `${xEff}% ${yEff}%`;
            previewFg.style.objectPosition = `${xEff}% ${yEff}%`;
            previewFg.style.transformOrigin = `${xEff}% ${yEff}%`;
            previewFg.style.transform = `scale(${z})`;

            if (posXVal) posXVal.textContent = String(Math.round(x));
            if (posYVal) posYVal.textContent = String(Math.round(y));

            const shown = (Math.round(z * 100) / 100).toFixed(2);
            if (zoomVal) zoomVal.textContent = shown;
            if (zoomReal) zoomReal.value = shown;
        };

        urlInput.addEventListener('input', () => {
            if (uploadedInput && uploadedInput.files && uploadedInput.files[0]) {
                setPreviewFromFile();
                return;
            }
            setPreview(urlInput.value);
        });
        urlInput.addEventListener('paste', () => setTimeout(() => {
            if (uploadedInput && uploadedInput.files && uploadedInput.files[0]) {
                setPreviewFromFile();
                return;
            }
            setPreview(urlInput.value);
        }, 0));
        if (uploadedInput) {
            uploadedInput.addEventListener('change', () => {
                setPreviewFromFile();
                syncColorPrompt();
            });
        }
        if (colorImagesInput) {
            colorImagesInput.addEventListener('change', syncColorImageRows);
        }
        colorInputs.forEach((input) => {
            input.addEventListener('change', syncColorPrompt);
        });
        zoomPct.addEventListener('input', applyFrame);
        posX.addEventListener('input', applyFrame);
        posY.addEventListener('input', applyFrame);

        previewBg.addEventListener('error', () => { previewBg.src = placeholder; });
        previewFg.addEventListener('error', () => { previewFg.src = placeholder; });

        applyFrame();
        syncColorPrompt();
        syncColorImageRows();
    })();
</script>
