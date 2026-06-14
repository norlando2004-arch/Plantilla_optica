@php($isEdit = isset($producto))
@php($meta = $isEdit && is_array($producto->meta) ? $producto->meta : [])
@php($img = (string)($meta['imagen_url'] ?? ''))
@php($manualImgFallback = (str_starts_with($img, 'data:') || filter_var($img, FILTER_VALIDATE_URL) === false) ? '' : $img)
@php($manualImg = (string)($meta['imagen_url_manual'] ?? $manualImgFallback))
@php($extraImgs = is_array($meta['imagenes'] ?? null) ? array_values(array_filter(array_map(static fn ($u) => is_string($u) ? trim($u) : '', $meta['imagenes']), static fn ($u) => $u !== '')) : [])
@php($extraImgsText = old('imagenes', implode("\n", $extraImgs)))

@php($caracteristicas = $isEdit && is_array($producto->caracteristicas) ? $producto->caracteristicas : [])
@php($medidas = is_array($caracteristicas['medidas'] ?? null) ? $caracteristicas['medidas'] : [])
@php($recomendadoParaDefault = old('recomendado_para', (string) ($caracteristicas['recomendado_para'] ?? $meta['recomendado_para'] ?? 'No especificado')))
@php($incluyeDefault = old('incluye', (string) ($caracteristicas['incluye'] ?? $meta['incluye'] ?? 'No especificado')))
@php($clipOnCompatibleDefault = old('clip_on_compatible', isset($caracteristicas['clip_on_compatible']) ? ((string) ((int) ((bool) $caracteristicas['clip_on_compatible']))) : (isset($meta['clip_on_compatible']) ? ((string) ((int) ((bool) $meta['clip_on_compatible']))) : '0')))
@php($compatProgresivosDefault = old('progresivos', isset($caracteristicas['progresivos']) ? ((string) ((int) ((bool) $caracteristicas['progresivos']))) : '0'))
@php($compatPolyDefault = old('poly', isset($caracteristicas['poly']) ? ((string) ((int) ((bool) $caracteristicas['poly']))) : '0'))
@php($tipoFormulaValue = (string) ($caracteristicas['tipo_formula'] ?? 'Bajas'))
@php($tipoFormulaDefault = old('tipo_formula', trim($tipoFormulaValue) !== '' ? $tipoFormulaValue : 'Bajas'))

@php($isGirlsModule = request()->routeIs('admin.gafas-ninas.*'))
@php($isBoysModule = request()->routeIs('admin.gafas-ninos.*'))
@php($isMenModule = request()->routeIs('admin.gafas-hombre.*'))
@php($isPolarModule = request()->routeIs('admin.gafas-polarizadas.*'))
@php($isDescansoModule = request()->routeIs('admin.gafas-descanso.*'))
@php($categoriaModuloDefault = $isGirlsModule ? 'ninas' : ($isBoysModule ? 'ninos' : ($isMenModule ? 'male' : ($isPolarModule ? 'gafas_polarizadas' : ($isDescansoModule ? 'descanso' : 'female')))))
@php($categoriaDefault = old('categoria', $isEdit
    ? ($producto->genero_objetivo ?? $categoriaModuloDefault)
    : $categoriaModuloDefault))
@php($categoriaLabelMap = [
    'female' => 'Gafas mujeres',
    'male' => 'Gafas hombres',
    'ninas' => 'Gafas niñas',
    'ninos' => 'Gafas niños',
    'gafas_polarizadas' => 'Gafas polarizadas',
    'descanso' => 'Gafas deportivas',
    'unisex' => 'Todas',
])
@php($categoriaLabel = $categoriaLabelMap[$categoriaModuloDefault] ?? 'Categoría fija')
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
@php($frameColorLegacyList = $isEdit && is_array($meta['color_list'] ?? null)
    ? array_values(array_filter(array_map(static fn ($value) => trim((string) $value), $meta['color_list']), static fn ($value) => $value !== ''))
    : [])
@php(
    $frameColorLegacyList = (function (array $current, array $meta, mixed $producto = null): array {
        if ($current !== []) {
            return $current;
        }

        $firstVariantName = trim((string) ($meta['color_variants'][0]['name'] ?? ''));
        if ($firstVariantName !== '') {
            $fromVariant = array_values(array_filter(array_map(static fn ($value) => trim((string) $value), explode(',', $firstVariantName)), static fn ($value) => $value !== ''));
            if ($fromVariant !== []) {
                return $fromVariant;
            }
        }

        if (is_array($meta['color_stock'] ?? null) && $meta['color_stock'] !== []) {
            $firstColorStockKey = trim((string) array_key_first($meta['color_stock']));
            if ($firstColorStockKey !== '') {
                $fromStock = array_values(array_filter(array_map(static fn ($value) => trim((string) $value), explode(',', $firstColorStockKey)), static fn ($value) => $value !== ''));
                if ($fromStock !== []) {
                    return $fromStock;
                }
            }
        }

        $singleColor = trim((string) ($producto?->color ?? ''));
        return $singleColor !== '' ? [$singleColor] : [];
    })($frameColorLegacyList, $meta, $isEdit ? $producto : null)
)
@php($frameColorDefaultRaw = old('color', $isEdit ? $frameColorLegacyList : []))
@php($frameColorDefaultList = is_array($frameColorDefaultRaw)
    ? array_values(array_filter(array_map(static fn ($value) => trim((string) $value), $frameColorDefaultRaw), static fn ($value) => $value !== ''))
    : (trim((string) $frameColorDefaultRaw) !== '' ? [trim((string) $frameColorDefaultRaw)] : []))
@php($frameColorDefault = $frameColorDefaultList !== [] ? ($frameColorDefaultList[0] ?? 'Gris') : 'Gris')
@php($colorStockMap = is_array(old('color_stock', is_array($meta['color_stock'] ?? null) ? $meta['color_stock'] : [])) ? old('color_stock', is_array($meta['color_stock'] ?? null) ? $meta['color_stock'] : []) : [])
@php($frameColorStockDefault = old('color_stock.' . $frameColorDefault, $colorStockMap[$frameColorDefault] ?? ''))
@php($existingColorVariants = $isEdit && is_array($meta['color_variants'] ?? null)
    ? array_values(array_filter(array_map(function ($variant) use ($colorStockMap) {
        if (!is_array($variant)) {
            return null;
        }

        $name = trim((string) ($variant['name'] ?? ''));
        $images = [];

        foreach ((array) ($variant['images'] ?? []) as $imgUrl) {
            $imgUrl = trim((string) $imgUrl);
            if ($imgUrl !== '' && !in_array($imgUrl, $images, true)) {
                $images[] = $imgUrl;
            }
        }

        $singleImage = trim((string) ($variant['image'] ?? ''));
        if ($singleImage !== '' && !in_array($singleImage, $images, true)) {
            array_unshift($images, $singleImage);
        }

        if ($images === []) {
            return null;
        }

        $names = array_values(array_filter(array_map(static fn ($value) => trim((string) $value), explode(',', $name !== '' ? $name : 'Color')), static fn ($value) => $value !== ''));
        if ($names === []) {
            $names = [$name !== '' ? $name : 'Color'];
        }

        $variantStock = isset($variant['stock']) && $variant['stock'] !== '' ? (int) $variant['stock'] : null;
        $mappedStocks = [];
        foreach ($names as $colorName) {
            if (array_key_exists($colorName, $colorStockMap) && $colorStockMap[$colorName] !== null && $colorStockMap[$colorName] !== '') {
                $mappedStocks[] = max(0, (int) $colorStockMap[$colorName]);
            }
        }

        if ($mappedStocks !== []) {
            $firstMappedStock = (int) $mappedStocks[0];
            $allMappedStocksMatch = count(array_unique($mappedStocks)) === 1;

            if ($variantStock === null) {
                $variantStock = $firstMappedStock;
            } elseif ($variantStock === 0 && $allMappedStocksMatch && $firstMappedStock > 0) {
                $variantStock = $firstMappedStock;
            }
        }

        return [
            'name' => implode(', ', $names),
            'names' => $names,
            'stock' => $variantStock,
            'images' => $images,
        ];
    }, $meta['color_variants'])))
    : [])

@php($existingCameraColorVariants = $isEdit && is_array($meta['camera_color_variants'] ?? null)
    ? array_values(array_filter(array_map(function ($variant) {
        if (!is_array($variant)) {
            return null;
        }

        $name = trim((string) ($variant['name'] ?? ''));
        $images = [];

        foreach ((array) ($variant['images'] ?? []) as $imgUrl) {
            $imgUrl = trim((string) $imgUrl);
            if ($imgUrl !== '' && !in_array($imgUrl, $images, true)) {
                $images[] = $imgUrl;
            }
        }

        $singleImage = trim((string) ($variant['image'] ?? ''));
        if ($singleImage !== '' && !in_array($singleImage, $images, true)) {
            array_unshift($images, $singleImage);
        }

        if ($images === []) {
            return null;
        }

        return [
            'name' => $name !== '' ? $name : 'Gris',
            'images' => $images,
        ];
    }, $meta['camera_color_variants'])))
    : [])

@php($initialGroupStockMap = [])
@php(
    collect($existingColorVariants)->each(function ($variant) use (&$initialGroupStockMap) {
        if (!is_array($variant) || !isset($variant['stock']) || $variant['stock'] === null || $variant['stock'] === '') {
            return;
        }

        $names = array_values(array_unique(array_filter(array_map(static fn ($value) => trim((string) $value), (array) ($variant['names'] ?? [])), static fn ($value) => $value !== '')));
        if ($names === []) {
            return;
        }

        usort($names, static fn (string $left, string $right) => strcasecmp($left, $right));
        $signature = implode('||', $names);
        if ($signature === '') {
            return;
        }

        $initialGroupStockMap[$signature] = (string) ((int) $variant['stock']);
    })
)

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

<div class="grid gap-5">
    <div class="grid gap-2">
        <label class="text-sm font-semibold text-zinc-700">Nombre</label>
        <input name="nombre" value="{{ old('nombre', $isEdit ? $producto->nombre : '') }}" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" required />
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div class="grid gap-2">
            <label class="text-sm font-semibold text-zinc-700">Categoría</label>
            <input type="hidden" name="categoria" value="{{ $categoriaModuloDefault }}" />
            <div class="w-full rounded-2xl border border-zinc-200 bg-zinc-100 px-4 py-3 text-sm font-medium text-zinc-700">{{ $categoriaLabel }}</div>
        </div>
        <div class="grid gap-2">
            <label class="text-sm font-semibold text-zinc-700">Material</label>
            <input name="material_montura" value="{{ $materialDefault }}" placeholder="TR90" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div class="grid gap-2">
            <label class="text-sm font-semibold text-zinc-700">Recomendado para</label>
            <input name="recomendado_para" value="{{ $recomendadoParaDefault }}" placeholder="Cara de sandía (forma ovalada)" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
        </div>
        <div class="grid gap-2">
            <label class="text-sm font-semibold text-zinc-700">Clip-on compatible</label>
            <select name="clip_on_compatible" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">
                <option value="0" {{ $clipOnCompatibleDefault === '0' ? 'selected' : '' }}>No</option>
                <option value="1" {{ $clipOnCompatibleDefault === '1' ? 'selected' : '' }}>Sí</option>
            </select>
        </div>
    </div>

    <div class="grid gap-2">
        <label class="text-sm font-semibold text-zinc-700">Medidas (cm)</label>
        <div class="grid gap-5 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-semibold text-zinc-700">Ancho total de la montura</label>
                <input name="ancho_total_montura" inputmode="decimal" value="{{ old('ancho_total_montura', $fmtCm($medidas['ancho_total_montura_cm'] ?? null)) }}" placeholder="13.9" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-semibold text-zinc-700">Ancho del lente</label>
                <input name="ancho_lente" inputmode="decimal" value="{{ old('ancho_lente', $fmtCm($medidas['ancho_lente_cm'] ?? null)) }}" placeholder="5.0" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-semibold text-zinc-700">Alto del lente</label>
                <input name="alto_lente" inputmode="decimal" value="{{ old('alto_lente', $fmtCm($medidas['alto_lente_cm'] ?? null)) }}" placeholder="4.5" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-semibold text-zinc-700">Puente</label>
                <input name="puente" inputmode="decimal" value="{{ old('puente', $fmtCm($medidas['puente_cm'] ?? null)) }}" placeholder="1.9" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
            </div>
            <div class="grid gap-2 md:col-span-2">
                <label class="text-sm font-semibold text-zinc-700">Largo de patillas</label>
                <input name="largo_patillas" inputmode="decimal" value="{{ old('largo_patillas', $fmtCm($medidas['largo_patillas_cm'] ?? null)) }}" placeholder="14.2" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
            </div>
        </div>
    </div>

    <div class="grid gap-2">
        <label class="text-sm font-semibold text-zinc-700">Incluye</label>
        <input name="incluye" value="{{ $incluyeDefault }}" placeholder="Estuche, microfibra y pin" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-5">
        <p class="text-sm font-semibold text-zinc-900">Compatibilidad</p>
        @php($__fpActual = (!empty($showFormulaPermitida) && isset($producto) && is_array($producto->meta ?? null) && array_key_exists('formula_permitida', $producto->meta ?? [])) ? ($producto->meta['formula_permitida'] ? '1' : '0') : '1')
        @php($__fpOld = old('formula_permitida', $__fpActual))
        <div class="mt-4 grid gap-5 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-semibold text-zinc-700">Progresivos</label>
                <select name="progresivos" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">
                    <option value="0" {{ $compatProgresivosDefault === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ $compatProgresivosDefault === '1' ? 'selected' : '' }}>Sí</option>
                </select>
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-semibold text-zinc-700">Tipo de fórmula</label>
                <input name="tipo_formula" value="{{ $tipoFormulaDefault }}" placeholder="Bajas" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-semibold text-zinc-700">Poly</label>
                <select name="poly" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">
                    <option value="0" {{ $compatPolyDefault === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ $compatPolyDefault === '1' ? 'selected' : '' }}>Sí</option>
                </select>
            </div>
            @if(!empty($showFormulaPermitida))
                <div class="grid gap-2 md:col-span-2">
                    <label class="text-sm font-semibold text-zinc-700">¿Permite fórmula médica?</label>
                    <p class="text-xs text-zinc-500">Si es <strong>No</strong>, el cliente irá directo al pago con el precio de la montura, sin opciones de lente con fórmula.</p>
                    <div class="flex gap-6 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="formula_permitida" value="1" class="accent-zinc-900"
                                {{ $__fpOld === '1' ? 'checked' : '' }}>
                            <span class="text-sm font-semibold text-zinc-800">Sí</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="formula_permitida" value="0" class="accent-zinc-900"
                                {{ $__fpOld === '0' ? 'checked' : '' }}>
                            <span class="text-sm font-semibold text-zinc-800">No</span>
                        </label>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div class="grid gap-2">
            <label class="text-sm font-semibold text-zinc-700">Precio (COP)</label>
            <input name="precio" inputmode="decimal" value="{{ old('precio', $precioDefault) }}" placeholder="50.000" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" required />
        </div>
        <div class="grid gap-2">
            <label class="text-sm font-semibold text-zinc-700">Precio oferta (opcional)</label>
            <input name="precio_oferta" inputmode="decimal" value="{{ old('precio_oferta', $precioOfertaDefault) }}" placeholder="45.000" class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
        </div>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div class="grid gap-2">
            <label class="text-sm font-semibold text-zinc-700">Stock total (respaldo)</label>
            <p class="text-xs text-zinc-500">Aquí ves la suma total de las gafas. Se calcula automáticamente desde el stock por color.</p>
            <input name="existencias" type="number" min="0" value="{{ old('existencias', $isEdit ? $producto->existencias : '') }}" readonly tabindex="-1" class="w-full cursor-not-allowed rounded-2xl border border-zinc-200 bg-zinc-100 px-4 py-3 text-sm text-zinc-700 outline-none" />
        </div>

        <label class="flex items-center gap-3 rounded-2xl border border-zinc-200 bg-white px-4 py-3">
            <input type="hidden" name="esta_activo" value="0" />
            <input type="checkbox" name="esta_activo" value="1" class="h-4 w-4 rounded border-zinc-300" {{ old('esta_activo', $isEdit ? $producto->esta_activo : true) ? 'checked' : '' }} />
            <span class="text-sm font-semibold text-zinc-700">Activo</span>
        </label>
    </div>


    <div class="grid gap-2">
        <label class="text-sm font-semibold text-zinc-700">Descripción (opcional)</label>
        <textarea name="descripcion" rows="5" class="w-full resize-y rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400">{{ old('descripcion', $isEdit ? $producto->descripcion : '') }}</textarea>
    </div>

    <div class="rounded-3xl border border-zinc-200 bg-white p-5">
        <p class="text-sm font-semibold text-zinc-900">Imagen</p>
        <p class="mt-1 text-sm text-zinc-500">Sube un archivo o pega URL manual. Si subes archivo, tiene prioridad.</p>

        @if($isEdit && $img)
            <div class="mt-4 flex items-center gap-4">
                <img src="{{ $img }}" alt="{{ e($producto->nombre) }}" class="h-20 w-20 rounded-2xl border border-zinc-200 object-cover" />
                <label class="flex items-center gap-2 text-sm font-semibold text-zinc-700">
                    <input type="checkbox" name="eliminar_imagen" value="1" class="h-4 w-4 rounded border-zinc-300" />
                    Eliminar imagen actual
                </label>
            </div>
        @endif

        <div class="mt-4">
            <label class="text-sm font-semibold text-zinc-700">Archivo de imagen (opcional)</label>
            <input id="gafaMujerUploadedImage" type="file" name="uploaded_image" accept="image/*" class="mt-2 block w-full text-sm text-zinc-700 file:mr-3 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800" />
            @if($isEdit && !empty($meta['uploaded_imagen_name']))
                <p class="mt-2 text-xs font-semibold text-emerald-700">Imagen subida guardada: {{ $meta['uploaded_imagen_name'] }}</p>
            @endif

            <div id="gafaMujerUploadedPreviewCard" class="mt-3 {{ $img ? '' : 'hidden' }} max-w-[320px] rounded-2xl border border-zinc-200 bg-zinc-50 p-3">
                <p class="text-xs font-semibold text-zinc-700">Vista previa inmediata</p>

                <div class="mt-2 overflow-hidden rounded-2xl border border-zinc-200 bg-white">
                    <div class="relative aspect-[4/3] w-full bg-zinc-100">
                        <img
                            id="gafaMujerUploadedInlinePreview"
                            src="{{ $img ?: $placeholderImg }}"
                            alt="Vista previa del archivo"
                            class="absolute inset-0 h-full w-full object-contain p-2"
                            onerror="this.onerror=null; this.src='{{ $placeholderImg }}';"
                        >
                    </div>
                </div>

                <div class="mt-3 min-w-0">
                    <p id="gafaMujerUploadedInlineName" class="truncate text-xs font-semibold text-zinc-700">{{ $isEdit && !empty($meta['uploaded_imagen_name']) ? $meta['uploaded_imagen_name'] : 'La imagen aparecerá aquí al seleccionarla' }}</p>
                    <p class="mt-1 text-[11px] text-zinc-500">Así el administrador puede confirmar visualmente el archivo antes de guardar.</p>
                </div>
            </div>

            <div id="gafaMujerColorPrompt" class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                <p class="text-sm font-semibold text-zinc-800">Color principal: <span id="gafaMujerColorText">{{ $frameColorDefault }}</span></p>
                <p class="mt-1 text-[11px] text-zinc-500">Selecciona el color de la gafa y ajusta el stock de ese color.</p>

                <div class="mt-3 flex flex-wrap items-center gap-2" id="colorCheckboxGroup">
                    @foreach($frameColorOptions as $option)
                        <label class="inline-flex cursor-pointer items-center">
                            <input
                                type="checkbox"
                                name="color[]"
                                value="{{ $option['name'] }}"
                                class="peer sr-only color-checkbox"
                                data-color-name="{{ $option['name'] }}"
                                data-color-hex="{{ $option['hex'] }}"
                                {{ in_array($option['name'], old('color', $frameColorDefaultList), true) ? 'checked' : '' }}
                            />
                            <span class="inline-flex h-9 w-9 rounded-full border-2 border-white shadow-sm ring-2 ring-zinc-300 transition peer-checked:ring-zinc-800" style="background-color: {{ $option['hex'] }};"></span>
                        </label>
                    @endforeach
                </div>

                <div class="mt-3 flex items-center gap-4">
                    <div id="combinedColorCircle"></div>
                    <span class="text-xs text-zinc-700">Vista previa de colores seleccionados</span>
                </div>

                <div class="mt-3 max-w-[220px]">
                    <label for="gafaMujerPrimaryColorStock" class="text-xs font-semibold text-zinc-700">Stock para estos colores</label>
                    <input id="gafaMujerPrimaryColorStock" type="number" min="0" value="{{ $frameColorStockDefault }}" placeholder="0" class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm outline-none focus:border-zinc-400" />
                    <div id="gafaMujerPrimaryColorStockHiddenInputs"></div>
                </div>

                @error('color_stock')
                    <p class="mt-3 text-sm font-semibold text-rose-700">{{ $message }}</p>
                @enderror

                <div id="gafaMujerClientStockError" class="mt-3 hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700"></div>
                <script>
                // Función para generar un SVG de círculo "pie chart" con los colores seleccionados
                function renderCombinedColorCircle(colors) {
                    const size = 48;
                    if (!colors.length) {
                        document.getElementById('combinedColorCircle').innerHTML = '';
                        return;
                    }
                    const angleStep = 360 / colors.length;
                    let paths = '';
                    for (let i = 0; i < colors.length; i++) {
                        const startAngle = angleStep * i - 90;
                        const endAngle = angleStep * (i + 1) - 90;
                        const x1 = size/2 + (size/2) * Math.cos(Math.PI * startAngle / 180);
                        const y1 = size/2 + (size/2) * Math.sin(Math.PI * startAngle / 180);
                        const x2 = size/2 + (size/2) * Math.cos(Math.PI * endAngle / 180);
                        const y2 = size/2 + (size/2) * Math.sin(Math.PI * endAngle / 180);
                        const largeArc = angleStep > 180 ? 1 : 0;
                        paths += `<path d="M${size/2},${size/2} L${x1},${y1} A${size/2},${size/2} 0 ${largeArc} 1 ${x2},${y2} Z" fill="${colors[i]}" />`;
                    }
                    document.getElementById('combinedColorCircle').innerHTML = `<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">${paths}</svg>`;
                }

                function getSelectedColors() {
                    return Array.from(document.querySelectorAll('.color-checkbox:checked')).map(cb => cb.getAttribute('data-color-hex'));
                }

                function updateCombinedColorCircle() {
                    renderCombinedColorCircle(getSelectedColors());
                }

                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('.color-checkbox').forEach(cb => {
                        cb.addEventListener('change', updateCombinedColorCircle);
                    });
                    updateCombinedColorCircle();
                });
                </script>
            </div>
        </div>

        <div class="mt-4">
            <label class="text-sm font-semibold text-zinc-700">Subir imágenes por color (opcional)</label>
            <p class="text-sm text-zinc-500">Puedes subir varias fotos y asignarle color a cada una. Luego en la ficha pública, al tocar un color, cambiará a su foto.</p>

            @if($existingColorVariants !== [])
                <div class="mt-3 rounded-2xl border border-zinc-200 bg-zinc-50 p-3">
                    <p class="text-xs font-semibold text-zinc-700">Imágenes por color guardadas actualmente</p>
                    <p class="mt-1 text-[11px] text-zinc-500">Así puedes ver lo que ya está subido antes de cambiar colores o stock.</p>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach($existingColorVariants as $variantIndex => $variant)
                            <div class="rounded-2xl border border-zinc-200 bg-white p-3" data-existing-variant-card="{{ $variantIndex }}">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-xs font-semibold text-zinc-800">Colores actuales: <span data-existing-variant-name-label>{{ $variant['name'] }}</span></p>
                                    @if($variant['stock'] !== null)
                                        <p class="text-[11px] font-semibold text-zinc-500">Stock actual: {{ $variant['stock'] }}</p>
                                    @endif
                                </div>

                                <input type="hidden" name="existing_color_variants[{{ $variantIndex }}][name]" value="{{ $variant['name'] }}" data-existing-variant-name />
                                <input type="hidden" data-existing-variant-colors value="{{ implode(',', $variant['names'] ?? [$variant['name']]) }}" />

                                @foreach($variant['images'] as $imgUrl)
                                    <input type="hidden" name="existing_color_variants[{{ $variantIndex }}][images][]" value="{{ $imgUrl }}" />
                                @endforeach


                                <div class="mt-2 grid grid-cols-3 gap-2">
                                    @foreach($variant['images'] as $imgIdx => $imgUrl)
                                        <div class="relative aspect-square overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50">
                                            <input type="number" name="existing_color_variants[{{ $variantIndex }}][images_order][{{ $imgIdx }}]" value="{{ $imgIdx + 1 }}" min="1" step="1" inputmode="numeric" pattern="[0-9]*" class="absolute top-1 left-1 z-10 w-8 h-8 rounded-full bg-zinc-800 text-white text-xs font-bold text-center opacity-80 select-none border-0 outline-none no-spinner" style="padding-left:0.5em;pointer-events:auto;" title="Orden de la imagen" oninput="handleImageOrderInputRealtime(this, {{ $variantIndex }})" onkeydown="return handleImageOrderKeydown(event, this, {{ $variantIndex }})" />
                                            <script>
                                            // Reordena automáticamente los inputs de orden de imagen para mantener el orden único y consecutivo
                                            function handleImageOrderInputRealtime(input, variantIndex) {
                                                const container = input.closest('.grid');
                                                if (!container) return;
                                                let orderInputs = Array.from(container.querySelectorAll('input[name^="existing_color_variants[' + variantIndex + '][images_order]"]'));
                                                let imageDivs = Array.from(container.querySelectorAll('div.relative.aspect-square'));
                                                let values = orderInputs.map(i => parseInt(i.value));
                                                const changedIdx = orderInputs.indexOf(input);
                                                let newOrder = parseInt(input.value);
                                                const maxOrder = orderInputs.length;
                                                if (isNaN(newOrder) || newOrder < 1) return; // No mover si vacío o inválido
                                                // Si el usuario pone un número mayor al máximo, lo mueve al final
                                                let safeOrder = Math.min(newOrder, maxOrder);
                                                let indices = orderInputs.map((_, idx) => idx);
                                                indices.splice(changedIdx, 1);
                                                indices.splice(safeOrder - 1, 0, changedIdx);
                                                for (let i = 0; i < orderInputs.length; i++) {
                                                    orderInputs[indices[i]].value = i + 1;
                                                    if (imageDivs[indices[i]]) {
                                                        container.appendChild(imageDivs[indices[i]]);
                                                    }
                                                }
                                            }

                                            // Al presionar Enter, aplica el cambio de orden y no envía el formulario
                                            function handleImageOrderKeydown(e, input, variantIndex) {
                                                if (e.key === 'Enter') {
                                                    e.preventDefault();
                                                    handleImageOrderInputRealtime(input, variantIndex);
                                                    input.blur();
                                                    return false;
                                                }
                                                return true;
                                            }
                                            </script>
                                            </style>
                                            <style>
                                            /* Oculta las flechas de los input type number solo en Chrome, Edge, Safari */
                                            input.no-spinner::-webkit-outer-spin-button,
                                            input.no-spinner::-webkit-inner-spin-button {
                                              -webkit-appearance: none;
                                              margin: 0;
                                            }
                                            input.no-spinner {
                                              -moz-appearance: textfield;
                                            }
                                            </style>
                                            <script>
                                            // Reordena automáticamente los inputs de orden de imagen para mantener el orden único y consecutivo
                                            function handleImageOrderInput(input, variantIndex) {
                                                // Encuentra todos los inputs de orden en el bloque
                                                const container = input.closest('.grid');
                                                if (!container) return;
                                                let orderInputs = Array.from(container.querySelectorAll('input[name^="existing_color_variants[' + variantIndex + '][images_order]"]'));
                                                // Obtiene los valores actuales y el input editado
                                                let values = orderInputs.map(i => parseInt(i.value) || 1);
                                                const changedIdx = orderInputs.indexOf(input);
                                                let newOrder = Math.max(1, Math.min(values.length, parseInt(input.value) || 1));
                                                // Si el valor no cambió, no hace nada
                                                if (values[changedIdx] === newOrder) return;
                                                // Elimina el valor anterior
                                                values.splice(changedIdx, 1);
                                                // Inserta el nuevo valor en la posición deseada
                                                values.splice(newOrder - 1, 0, values[changedIdx]);
                                                // Reasigna los valores para que sean consecutivos y únicos
                                                let used = 1;
                                                for (let i = 0; i < orderInputs.length; i++) {
                                                    if (i === newOrder - 1) {
                                                        orderInputs[changedIdx].value = used++;
                                                    } else {
                                                        if (i < changedIdx) {
                                                            orderInputs[i].value = used++;
                                                        } else {
                                                            orderInputs[i].value = used++;
                                                        }
                                                    }
                                                }
                                            }
                                            </script>
                                            <img src="{{ $imgUrl }}" alt="{{ $variant['name'] }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy" />
                                            <!-- Botón eliminar imagen guardada -->
                                            <button type="button" class="absolute top-1 right-1 z-10 p-0 rounded-full bg-white shadow hover:bg-red-100 border border-red-300" style="width:22px;height:22px;display:flex;align-items:center;justify-content:center;" title="Eliminar imagen" onclick="this.closest('.relative').style.display='none'; this.nextElementSibling.value='{{ $imgUrl }}';">
                                                <svg viewBox="0 0 20 20" fill="red" width="14" height="14" aria-hidden="true"><path fill-rule="evenodd" d="M6.707 6.707a1 1 0 010-1.414 1 1 0 011.414 0L10 7.586l1.879-1.88a1 1 0 111.415 1.415L11.415 9l1.88 1.879a1 1 0 01-1.415 1.415L10 10.415l-1.879 1.88a1 1 0 01-1.414-1.415L8.586 9 6.707 7.121z" clip-rule="evenodd"/></svg>
                                            </button>
                                            <input type="hidden" name="existing_color_variants[{{ $variantIndex }}][delete_images][]" value="" />
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-3">
                                    <p class="text-[11px] font-semibold text-zinc-600">Cambiar colores</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        @foreach($frameColorOptions as $option)
                                            <label class="inline-flex cursor-pointer items-center">
                                                <input
                                                    type="checkbox"
                                                    name="existing_variant_color_pick_{{ $variantIndex }}[]"
                                                    value="{{ $option['name'] }}"
                                                    class="peer sr-only"
                                                    data-existing-variant-color-checkbox="{{ $variantIndex }}"
                                                    {{ in_array($option['name'], $variant['names'] ?? [$variant['name']], true) ? 'checked' : '' }}
                                                />
                                                <span class="inline-flex h-8 w-8 rounded-full border-2 border-white shadow-sm ring-2 ring-zinc-300 transition peer-checked:ring-zinc-800" style="background-color: {{ $option['hex'] }};"></span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="mt-3 flex items-center gap-2" data-existing-variant-preview-row>
                                        <div class="shrink-0" data-existing-variant-preview-wrap></div>
                                        <p class="text-[11px] text-zinc-500">Vista previa de colores seleccionados</p>
                                    </div>
                                </div>

                                <div class="mt-3 max-w-[220px]">
                                    <label class="text-[11px] font-semibold text-zinc-600">Stock para este grupo</label>
                                    <input
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        value="{{ $variant['stock'] !== null ? $variant['stock'] : '' }}"
                                        name="existing_color_variants[{{ $variantIndex }}][stock]"
                                        data-existing-variant-stock="{{ $variantIndex }}"
                                        data-existing-variant-initial-stock="{{ $variant['stock'] !== null ? $variant['stock'] : '' }}"
                                        class="mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-400"
                                    />
                                    <input
                                        type="hidden"
                                        name="existing_color_variants[{{ $variantIndex }}][stock]"
                                        value="{{ $variant['stock'] !== null ? $variant['stock'] : '' }}"
                                        data-existing-variant-stock-hidden="{{ $variantIndex }}"
                                    />
                                    <div data-existing-variant-stock-hidden-wrap></div>
                                    <p class="mt-1 text-[11px] text-zinc-500" data-existing-variant-stock-hint>La primera imagen de esta combinación maneja el stock.</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-4 rounded-2xl border border-zinc-200 bg-zinc-50 p-4">
                <label for="gafaMujerVariantColorImages" class="text-sm font-semibold text-zinc-900">Subir imagenes por color</label>
                <p class="mt-1 text-xs text-zinc-600">Estas imagenes son para la ficha publica. Al tocar un color, cambiara a su foto correspondiente.</p>
                <input id="gafaMujerVariantColorImages" type="file" name="uploaded_color_variant_images[]" accept="image/*" multiple class="mt-3 block w-full text-sm text-zinc-700 file:mr-3 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800" />

                <div id="gafaMujerVariantColorImagesMap" class="mt-3 hidden rounded-2xl border border-zinc-200 bg-white p-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs font-semibold text-zinc-700">Asigna color a cada imagen (ficha publica):</p>
                        <span id="gafaMujerVariantColorImagesCount" class="text-[11px] font-semibold text-zinc-500"></span>
                    </div>
                    <div id="gafaMujerVariantColorImagesRows" class="mt-3 grid gap-3 sm:grid-cols-2"></div>
                </div>
            </div>

            @if($isEdit)
            <div class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 p-4">
                <label for="gafaMujerColorImages" class="text-sm font-semibold text-zinc-900">Subir imagen de camara 3D</label>
                <p class="mt-1 text-xs text-zinc-600">Estas imagenes se usan para la camara virtual y solo se pueden asignar a los colores seleccionados arriba.</p>
                <input id="gafaMujerColorImages" type="file" name="uploaded_color_images[]" accept="image/*" multiple class="mt-3 block w-full text-sm text-zinc-700 file:mr-3 file:rounded-xl file:border-0 file:bg-zinc-900 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800" />

                @if($isEdit && $existingCameraColorVariants !== [])
                    <div class="mt-4 rounded-2xl border border-zinc-200 bg-white p-3">
                        <p class="text-xs font-semibold text-zinc-800">Imagenes de camara 3D guardadas</p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach($existingCameraColorVariants as $cameraVariantIndex => $variant)
                                @php($cameraVariantNames = array_values(array_filter(array_map(static fn ($value) => trim((string) $value), explode(',', (string) ($variant['name'] ?? ''))), static fn ($value) => $value !== '')))
                                <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-2" data-camera-variant-card="{{ $cameraVariantIndex }}">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-[11px] font-semibold text-zinc-700">Grupo: <span data-camera-variant-name-label>{{ $variant['name'] }}</span></p>
                                    </div>

                                    <input type="hidden" name="existing_camera_variants[{{ $cameraVariantIndex }}][name]" value="{{ $variant['name'] }}" data-camera-variant-name />
                                    <input type="hidden" data-camera-variant-colors value="{{ implode(',', $cameraVariantNames) }}" />

                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach(($variant['images'] ?? []) as $savedCameraImage)
                                            <div class="relative" data-camera-existing-image>
                                                <input type="hidden" name="existing_camera_variants[{{ $cameraVariantIndex }}][images][]" value="{{ $savedCameraImage }}" />
                                                <img src="{{ $savedCameraImage }}" alt="{{ $variant['name'] }}" class="h-14 w-14 rounded-lg border border-zinc-200 object-cover" loading="lazy" />
                                                <button type="button" class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full border border-rose-300 bg-white text-rose-600" title="Eliminar imagen" onclick="this.closest('[data-camera-existing-image]').classList.add('hidden'); this.nextElementSibling.value='{{ $savedCameraImage }}';">
                                                    <svg viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3" aria-hidden="true"><path fill-rule="evenodd" d="M6.707 6.707a1 1 0 010-1.414 1 1 0 011.414 0L10 7.586l1.879-1.88a1 1 0 111.415 1.415L11.415 9l1.88 1.879a1 1 0 01-1.415 1.415L10 10.415l-1.879 1.88a1 1 0 01-1.414-1.415L8.586 9 6.707 7.121z" clip-rule="evenodd"/></svg>
                                                </button>
                                                <input type="hidden" name="existing_camera_variants[{{ $cameraVariantIndex }}][delete_images][]" value="" />
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-3">
                                        <p class="text-[11px] font-semibold text-zinc-600">Seleccionar grupo de colores</p>
                                        <div class="mt-2 flex flex-wrap items-center gap-2" data-camera-variant-group-options></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            @else
            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-800">Fotos de cámara 3D disponibles al editar</p>
                <p class="mt-1 text-xs text-amber-700">Primero crea el producto con todas sus fotos y colores. Una vez guardado, podrás subir las fotos de cámara 3D desde la edición.</p>
            </div>
            @endif

            <div id="gafaMujerColorImagesMap" class="mt-3 hidden rounded-2xl border border-zinc-200 bg-zinc-50 p-3">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold text-zinc-700">Asigna color a cada imagen (solo colores de arriba):</p>
                    <span id="gafaMujerColorImagesCount" class="text-[11px] font-semibold text-zinc-500"></span>
                </div>
                <div id="gafaMujerColorImagesRows" class="mt-3 grid gap-3 sm:grid-cols-2"></div>
            </div>
        </div>

        <input type="hidden" id="gafaMujerImageUrl" name="imagen_url" value="{{ old('imagen_url', $isEdit ? $manualImg : '') }}" />

        <div hidden>
            <input type="hidden" name="image_zoom" value="{{ number_format($zoom, 2, '.', '') }}">
            <input type="hidden" name="image_pos_x" value="{{ (int) $posX }}">
            <input type="hidden" name="image_pos_y" value="{{ (int) $posY }}">
            <input id="gafaMujerZoomPct" type="range" min="0" max="100" step="1" value="{{ (int) round($zoomPct) }}">
            <input id="gafaMujerZoom" type="hidden" name="image_zoom" value="{{ number_format($zoom, 2, '.', '') }}">
            <span id="gafaMujerZoomVal"></span>
            <input id="gafaMujerPosX" name="image_pos_x" type="range" min="0" max="100" step="1" value="{{ (int) $posX }}">
            <span id="gafaMujerPosXVal"></span>
            <input id="gafaMujerPosY" name="image_pos_y" type="range" min="0" max="100" step="1" value="{{ (int) $posY }}">
            <span id="gafaMujerPosYVal"></span>
            <img id="gafaMujerPreviewBg" src="{{ $img ?: $placeholderImg }}" alt="" onerror="this.onerror=null; this.src='{{ $placeholderImg }}';">
            <img id="gafaMujerPreviewFg" src="{{ $img ?: $placeholderImg }}" alt="" onerror="this.onerror=null; this.src='{{ $placeholderImg }}';">
        </div>
    </div>
</div>

<script>
    (() => {
        const urlInput = document.getElementById('gafaMujerImageUrl');
        const previewBg = document.getElementById('gafaMujerPreviewBg');
        const previewFg = document.getElementById('gafaMujerPreviewFg');
        const uploadedInput = document.getElementById('gafaMujerUploadedImage');
        const uploadedPreviewCard = document.getElementById('gafaMujerUploadedPreviewCard');
        const uploadedPreviewImg = document.getElementById('gafaMujerUploadedInlinePreview');
        const uploadedPreviewName = document.getElementById('gafaMujerUploadedInlineName');
        const zoomPct = document.getElementById('gafaMujerZoomPct');
        const zoomReal = document.getElementById('gafaMujerZoom');
        const posX = document.getElementById('gafaMujerPosX');
        const posY = document.getElementById('gafaMujerPosY');
        const zoomVal = document.getElementById('gafaMujerZoomVal');
        const posXVal = document.getElementById('gafaMujerPosXVal');
        const posYVal = document.getElementById('gafaMujerPosYVal');
        const colorPrompt = document.getElementById('gafaMujerColorPrompt');
        const colorText = document.getElementById('gafaMujerColorText');
        const colorInputs = Array.from(document.querySelectorAll('input[name="color[]"]'));
        const variantColorImagesInput = document.getElementById('gafaMujerVariantColorImages');
        const variantColorImagesMap = document.getElementById('gafaMujerVariantColorImagesMap');
        const variantColorImagesRows = document.getElementById('gafaMujerVariantColorImagesRows');
        const variantColorImagesCount = document.getElementById('gafaMujerVariantColorImagesCount');
        const colorImagesInput = document.getElementById('gafaMujerColorImages');
        const colorImagesMap = document.getElementById('gafaMujerColorImagesMap');
        const colorImagesRows = document.getElementById('gafaMujerColorImagesRows');
        const colorImagesCount = document.getElementById('gafaMujerColorImagesCount');
        const totalStockInput = document.querySelector('input[name="existencias"]');
        const primaryColorStockInput = document.getElementById('gafaMujerPrimaryColorStock');
        const primaryColorStockHiddenInputs = document.getElementById('gafaMujerPrimaryColorStockHiddenInputs');
        const clientStockError = document.getElementById('gafaMujerClientStockError');
        const colorOptions = @json($frameColorOptions);
        const initialColorStocks = @json($colorStockMap);
        const initialGroupStocks = @json($initialGroupStockMap);
        const colorStockValues = { ...initialColorStocks, ...initialGroupStocks };
        let colorImageObjectUrls = [];
        let variantImageObjectUrls = [];
        let uploadedPreviewObjectUrl = null;
        let selectedVariantImageEntries = [];
        let selectedColorImageEntries = [];

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

        const buildColorImageEntryId = (file) => {
            return [file.name || 'imagen', file.size || 0, file.lastModified || 0].join('__');
        };

        const getSelectedFrameColorNames = () => {
            return colorInputs
                .filter((input) => input.checked)
                .map((input) => String(input.value || '').trim())
                .filter((value) => value !== '');
        };

        const getPrimarySelectedColor = () => {
            const selectedColors = getSelectedFrameColorNames();
            return selectedColors[0] || 'Gris';
        };

        const getAvailableCameraColorNames = () => {
            const selectedColors = getSelectedFrameColorNames();
            if (selectedColors.length > 0) {
                return Array.from(new Set(selectedColors));
            }

            const fromExisting = [];
            const existingCards = Array.from(document.querySelectorAll('[data-existing-variant-card]'));

            existingCards.forEach((card) => {
                const hiddenColors = card.querySelector('input[data-existing-variant-colors]');
                const hiddenName = card.querySelector('input[data-existing-variant-name]');
                const raw = String((hiddenColors && hiddenColors.value) || (hiddenName && hiddenName.value) || '').trim();
                if (raw === '') {
                    return;
                }

                raw
                    .split(',')
                    .map((value) => String(value || '').trim())
                    .filter((value) => value !== '')
                    .forEach((value) => {
                        if (!fromExisting.includes(value)) {
                            fromExisting.push(value);
                        }
                    });
            });

            if (fromExisting.length > 0) {
                return fromExisting;
            }

            return ['Gris'];
        };

        const getDefaultCameraColorNames = () => {
            const available = getAvailableCameraColorNames();
            const selectedColors = getSelectedFrameColorNames();

            if (selectedColors.length > 0) {
                const normalized = selectedColors.filter((color) => available.includes(color));
                if (normalized.length > 0) {
                    return Array.from(new Set(normalized));
                }
            }

            return available;
        };

        const normalizeCameraColorGroup = (colors) => {
            const normalized = Array.from(new Set((Array.isArray(colors) ? colors : [colors])
                .map((value) => String(value || '').trim())
                .filter((value) => value !== '')));

            return normalized;
        };

        const getAvailableCameraColorGroups = () => {
            const groups = [];
            const seen = new Set();

            const pushGroup = (rawGroup) => {
                const group = normalizeCameraColorGroup(rawGroup);
                if (group.length === 0) {
                    return;
                }

                const signature = getColorGroupSignature(group);
                if (signature === '' || seen.has(signature)) {
                    return;
                }

                seen.add(signature);
                groups.push(group);
            };

            pushGroup(getSelectedFrameColorNames());

            const existingCards = Array.from(document.querySelectorAll('[data-existing-variant-card]'));
            existingCards.forEach((card) => {
                const hiddenColors = card.querySelector('input[data-existing-variant-colors]');
                const hiddenName = card.querySelector('input[data-existing-variant-name]');
                const raw = String((hiddenColors && hiddenColors.value) || (hiddenName && hiddenName.value) || '').trim();
                if (raw === '') {
                    return;
                }

                pushGroup(raw.split(',').map((value) => String(value || '').trim()));
            });

            if (groups.length > 0) {
                return groups;
            }

            const fallbackNames = getAvailableCameraColorNames();
            if (fallbackNames.length > 0) {
                return [fallbackNames];
            }

            return [['Gris']];
        };

        const getAvailableNormalColorGroups = () => {
            const groups = [];
            const seen = new Set();

            const pushGroup = (rawGroup) => {
                const group = Array.from(new Set((Array.isArray(rawGroup) ? rawGroup : [rawGroup])
                    .map((value) => String(value || '').trim())
                    .filter((value) => value !== '')));

                if (group.length === 0) {
                    return;
                }

                const signature = getColorGroupSignature(group);
                if (signature === '' || seen.has(signature)) {
                    return;
                }

                seen.add(signature);
                groups.push(group);
            };

            pushGroup(getSelectedFrameColorNames());

            const existingCards = Array.from(document.querySelectorAll('[data-existing-variant-card]'));
            existingCards.forEach((card) => {
                const hiddenColors = card.querySelector('input[data-existing-variant-colors]');
                const hiddenName = card.querySelector('input[data-existing-variant-name]');
                const raw = String((hiddenColors && hiddenColors.value) || (hiddenName && hiddenName.value) || '').trim();
                if (raw === '') {
                    return;
                }

                pushGroup(raw.split(',').map((value) => String(value || '').trim()));
            });

            if (groups.length > 0) {
                return groups;
            }

            const selected = getSelectedFrameColorNames();
            if (selected.length > 0) {
                return [selected];
            }

            return [['Gris']];
        };

        const getEntrySelectedColors = (entry) => {
            if (entry && Array.isArray(entry.colors)) {
                const normalizedColors = entry.colors
                    .map((color) => String(color || '').trim())
                    .filter((color) => color !== '');

                if (normalizedColors.length > 0) {
                    return canonicalizeColorNames(normalizedColors);
                }
            }

            const fallbackColor = String(entry && entry.color ? entry.color : 'Gris').trim() || 'Gris';
            return canonicalizeColorNames([fallbackColor]);
        };

        const getColorGroupSignature = (colors) => {
            return Array.from(new Set((Array.isArray(colors) ? colors : [colors])
                .map((color) => String(color || '').trim())
                .filter((color) => color !== '')))
                .sort((left, right) => left.localeCompare(right))
                .join('||');
        };

        const getEntryColorGroupSignature = (entry) => getColorGroupSignature(getEntrySelectedColors(entry));

        // ── Cámara 3D: reglas de asignación de grupo (solo para uploaded_color_images[]) ──
        const getMaxCamera3DImageCount = () => {
            const groups = getAvailableCameraColorGroups();
            return Math.max(0, groups.length);
        };

        const enforceUniqueCamera3DGroupAssignments = () => {
            const availableGroups = getAvailableCameraColorGroups();
            if (!Array.isArray(availableGroups) || availableGroups.length === 0) {
                return;
            }

            const groupBySignature = new Map();
            availableGroups.forEach((group) => {
                groupBySignature.set(getColorGroupSignature(group), group);
            });
            const availableSignatures = Array.from(groupBySignature.keys()).filter((sig) => sig !== '');
            const maxImages = availableSignatures.length;

            // 1) Limitar cantidad de imágenes al número de grupos disponibles.
            const currentEntries = selectedColorImageEntries.filter((entry) => entry && entry.file);
            if (currentEntries.length > maxImages) {
                const keepIds = new Set(currentEntries.slice(0, maxImages).map((entry) => entry.id));
                selectedColorImageEntries = selectedColorImageEntries.filter((entry) => entry && entry.file && keepIds.has(entry.id));
                syncColorImageInputFiles();
            }

            // Mantener cualquier selección manual válida; solo corregimos grupos inexistentes.
            const fallbackSignature = availableSignatures[0] || '';
            selectedColorImageEntries.forEach((entry) => {
                if (!entry || !entry.file) {
                    return;
                }

                const sig = getEntryColorGroupSignature(entry) || '';
                if (groupBySignature.has(sig)) {
                    return;
                }

                const fallbackColors = groupBySignature.get(fallbackSignature) || ['Gris'];
                entry.colors = [...fallbackColors];
                entry.color = entry.colors[0] || 'Gris';
                entry.order = 1;
            });
        };

        const getDuplicateCamera3DGroupSignatures = () => {
            const counts = new Map();

            selectedColorImageEntries
                .filter((entry) => entry && entry.file)
                .forEach((entry) => {
                    const sig = String(getEntryColorGroupSignature(entry) || '').trim();
                    if (sig === '') {
                        return;
                    }

                    counts.set(sig, (counts.get(sig) || 0) + 1);
                });

            return new Set(
                Array.from(counts.entries())
                    .filter(([, count]) => count > 1)
                    .map(([sig]) => sig)
            );
        };

        const validateCamera3DGroupsBeforeSubmit = () => {
            const entries = selectedColorImageEntries.filter((entry) => entry && entry.file);
            const maxImages = getMaxCamera3DImageCount();

            if (entries.length > maxImages) {
                return `Solo puedes subir ${maxImages} imagen${maxImages === 1 ? '' : 'es'} de camara 3D porque solo hay ${maxImages} grupo${maxImages === 1 ? '' : 's'} de colores disponible${maxImages === 1 ? '' : 's'}.`;
            }

            const duplicates = getDuplicateCamera3DGroupSignatures();
            if (duplicates.size === 0) {
                return null;
            }

            const duplicateLabels = Array.from(duplicates)
                .map((signature) => signature.split('||').join(', '));

            return `No puedes guardar imagenes de camara 3D con los mismos colores repetidos. Revisa estos grupos: ${duplicateLabels.join(' / ')}.`;
        };

        const applyCamera3DGroupLocksToUi = () => {
            if (!colorImagesRows) return;

            const fields = Array.from(colorImagesRows.querySelectorAll('[data-color-palette-field]'));
            if (fields.length === 0) return;

            const selectedByKey = new Map();
            fields.forEach((field) => {
                const key = String(field.dataset.colorPaletteField || '').trim();
                const sig = String(field.dataset.colorGroupSignature || '').trim();
                if (key !== '') {
                    selectedByKey.set(key, sig);
                }
            });

            const duplicates = getDuplicateCamera3DGroupSignatures();

            fields.forEach((field) => {
                const mySig = String(field.dataset.colorGroupSignature || '').trim();

                const radios = Array.from(field.querySelectorAll('input[type="radio"]'));
                radios.forEach((radio) => {
                    const isChecked = Boolean(radio.checked);
                    const optionLabel = radio.closest('label');
                    const pie = radio.nextElementSibling;
                    if (optionLabel) {
                        optionLabel.classList.add('cursor-pointer');
                        optionLabel.classList.remove('cursor-not-allowed');
                        optionLabel.classList.remove('opacity-50');
                    }
                    if (pie && pie.classList) {
                        pie.classList.remove('ring-zinc-300', 'ring-zinc-800', 'ring-rose-400');
                        if (isChecked) {
                            pie.classList.add(duplicates.has(mySig) ? 'ring-rose-400' : 'ring-zinc-800');
                        } else {
                            pie.classList.add('ring-zinc-300');
                        }
                    }
                });

                // La etiqueta visible solo se resalta cuando la selección actual está duplicada.
                const isDup = mySig !== '' && duplicates.has(mySig);
                const label = field.querySelector('[data-camera3d-group-label]');
                if (label) {
                    label.classList.toggle('underline', isDup);
                    label.classList.toggle('decoration-rose-500', isDup);
                    label.classList.toggle('decoration-2', isDup);
                    label.classList.toggle('text-rose-700', isDup);
                    if (!isDup) {
                        label.classList.remove('text-rose-700');
                        label.classList.remove('underline');
                        label.classList.remove('decoration-rose-500');
                        label.classList.remove('decoration-2');
                    }
                }
            });
        };

        const getGroupStockValue = (colors) => {
            const sig = getColorGroupSignature(colors) || 'Gris';
            if (Object.prototype.hasOwnProperty.call(colorStockValues, sig)) {
                return String(colorStockValues[sig]);
            }
            // Fallback: single-color lookup against initialColorStocks (backward compat)
            const normalizedColors = Array.from(new Set((Array.isArray(colors) ? colors : [colors])
                .map((color) => String(color || '').trim())
                .filter((color) => color !== '')));
            if (normalizedColors.length === 1) {
                const singleColor = normalizedColors[0];
                if (Object.prototype.hasOwnProperty.call(colorStockValues, singleColor)) {
                    return String(colorStockValues[singleColor]);
                }
            }
            return '';
        };

        const setGroupStockValue = (colors, value) => {
            const sig = getColorGroupSignature(colors) || 'Gris';
            colorStockValues[sig] = value;
        };

        const getNextColorImageOrder = (targetColors, excludedEntryId = null) => {
            const normalizedGroupSignature = getColorGroupSignature(targetColors) || 'Gris';
            let maxOrder = 0;

            selectedColorImageEntries.forEach((entry) => {
                if (!entry || !entry.file || entry.id === excludedEntryId) {
                    return;
                }

                const entryGroupSignature = getEntryColorGroupSignature(entry) || 'Gris';
                if (entryGroupSignature !== normalizedGroupSignature) {
                    return;
                }

                const entryOrder = Math.max(1, Number.parseInt(entry.order || '1', 10) || 1);
                if (entryOrder > maxOrder) {
                    maxOrder = entryOrder;
                }
            });

            return maxOrder + 1;
        };

        const normalizeColorImageOrders = (targetGroupSignatures = null) => {
            const normalizedTargets = Array.isArray(targetGroupSignatures)
                ? new Set(targetGroupSignatures
                    .map((groupSignature) => String(groupSignature || '').trim() || 'Gris'))
                : null;

            const groupedEntries = new Map();

            selectedColorImageEntries.forEach((entry, index) => {
                if (!entry || !entry.file) {
                    return;
                }

                const groupSignature = getEntryColorGroupSignature(entry) || 'Gris';
                if (normalizedTargets && !normalizedTargets.has(groupSignature)) {
                    return;
                }

                if (!groupedEntries.has(groupSignature)) {
                    groupedEntries.set(groupSignature, []);
                }

                groupedEntries.get(groupSignature).push({
                    entry,
                    index,
                    order: Math.max(1, Number.parseInt(entry.order || '1', 10) || 1),
                });
            });

            groupedEntries.forEach((items) => {
                items
                    .sort((left, right) => {
                        if (left.order === right.order) {
                            return left.index - right.index;
                        }

                        return left.order - right.order;
                    })
                    .forEach((item, orderIndex) => {
                        item.entry.order = orderIndex + 1;
                    });
            });
        };

        const reorderColorImageEntry = (entryId, targetColors, desiredOrder) => {
            const normalizedGroupSignature = getColorGroupSignature(targetColors) || 'Gris';
            const targetEntry = selectedColorImageEntries.find((entry) => entry && entry.file && entry.id === entryId);

            if (!targetEntry) {
                return;
            }

            const siblingEntries = [];

            selectedColorImageEntries.forEach((entry, index) => {
                if (!entry || !entry.file || entry.id === entryId) {
                    return;
                }

                const entryGroupSignature = getEntryColorGroupSignature(entry) || 'Gris';
                if (entryGroupSignature !== normalizedGroupSignature) {
                    return;
                }

                siblingEntries.push({
                    entry,
                    index,
                    order: Math.max(1, Number.parseInt(entry.order || '1', 10) || 1),
                });
            });

            siblingEntries.sort((left, right) => {
                if (left.order === right.order) {
                    return left.index - right.index;
                }

                return left.order - right.order;
            });

            const orderedEntries = siblingEntries.map((item) => item.entry);
            const safeDesiredOrder = Math.max(1, Number.parseInt(desiredOrder || '1', 10) || 1);
            const insertionIndex = Math.min(safeDesiredOrder - 1, orderedEntries.length);

            orderedEntries.splice(insertionIndex, 0, targetEntry);

            orderedEntries.forEach((entry, index) => {
                entry.order = index + 1;
            });
        };

        const syncColorImageInputFiles = () => {
            if (!colorImagesInput) return;

            if (typeof DataTransfer === 'undefined') {
                return;
            }

            const dataTransfer = new DataTransfer();
            selectedColorImageEntries.forEach((entry) => {
                if (entry && entry.file) {
                    dataTransfer.items.add(entry.file);
                }
            });
            colorImagesInput.files = dataTransfer.files;
        };

        const syncVariantImageInputFiles = () => {
            if (!variantColorImagesInput) return;

            if (typeof DataTransfer === 'undefined') {
                return;
            }

            const dataTransfer = new DataTransfer();
            selectedVariantImageEntries.forEach((entry) => {
                if (entry && entry.file) {
                    dataTransfer.items.add(entry.file);
                }
            });
            variantColorImagesInput.files = dataTransfer.files;
        };

        const appendSelectedColorImages = (files) => {
            const availableGroups = getAvailableCameraColorGroups();
            const groupBySignature = new Map();
            availableGroups.forEach((group) => {
                groupBySignature.set(getColorGroupSignature(group), group);
            });
            const availableSignatures = Array.from(groupBySignature.keys()).filter((sig) => sig !== '');

            const maxImages = availableSignatures.length;
            if (maxImages === 0) {
                return;
            }

            files.forEach((file) => {
                if (selectedColorImageEntries.filter((entry) => entry && entry.file).length >= maxImages) {
                    return;
                }

                const entryId = buildColorImageEntryId(file);
                const alreadyExists = selectedColorImageEntries.some((entry) => entry && entry.id === entryId);
                if (alreadyExists) {
                    return;
                }

                const fallbackSig = availableSignatures[0] || '';
                const colors = groupBySignature.get(fallbackSig) || getDefaultCameraColorNames();
                const normalizedColors = normalizeCameraColorGroup(colors);

                selectedColorImageEntries.push({
                    id: entryId,
                    file,
                    colors: [...normalizedColors],
                    color: normalizedColors[0] || 'Gris',
                    order: 1,
                });
            });

            enforceUniqueCamera3DGroupAssignments();
            syncColorImageInputFiles();
        };

        const appendSelectedVariantImages = (files) => {
            const fallbackColors = getSelectedFrameColorNames();
            const defaultColors = fallbackColors.length > 0 ? fallbackColors : ['Gris'];

            files.forEach((file) => {
                const entryId = buildColorImageEntryId(file);
                const alreadyExists = selectedVariantImageEntries.some((entry) => entry.id === entryId);
                if (alreadyExists) {
                    return;
                }

                selectedVariantImageEntries.push({
                    id: entryId,
                    file,
                    colors: [...defaultColors],
                    color: defaultColors[0] || 'Gris',
                    order: selectedVariantImageEntries.length + 1,
                });
            });

            syncVariantImageInputFiles();
        };

        const syncInlineUploadPreview = () => {
            if (!uploadedPreviewCard || !uploadedPreviewImg || !uploadedPreviewName) return;

            if (uploadedPreviewObjectUrl) {
                try {
                    URL.revokeObjectURL(uploadedPreviewObjectUrl);
                } catch (_) {
                    // noop
                }
                uploadedPreviewObjectUrl = null;
            }

            const file = uploadedInput && uploadedInput.files ? uploadedInput.files[0] : null;
            if (file) {
                uploadedPreviewObjectUrl = URL.createObjectURL(file);
                uploadedPreviewImg.src = uploadedPreviewObjectUrl;
                uploadedPreviewName.textContent = file.name || 'Archivo seleccionado';
                uploadedPreviewCard.classList.remove('hidden');
                return;
            }

            const fallbackSrc = String(previewFg.getAttribute('src') || '').trim();
            if (fallbackSrc !== '' && fallbackSrc !== placeholder) {
                uploadedPreviewImg.src = fallbackSrc;
                uploadedPreviewName.textContent = uploadedPreviewName.textContent.trim() !== ''
                    ? uploadedPreviewName.textContent
                    : 'Imagen actual';
                uploadedPreviewCard.classList.remove('hidden');
                return;
            }

            uploadedPreviewImg.src = placeholder;
            uploadedPreviewName.textContent = 'La imagen aparecerá aquí al seleccionarla';
            uploadedPreviewCard.classList.add('hidden');
        };

        const hideClientStockError = () => {
            if (!clientStockError) return;

            clientStockError.textContent = '';
            clientStockError.classList.add('hidden');
        };

        const showClientStockError = (message) => {
            if (!clientStockError) return;

            clientStockError.textContent = String(message || 'Debes revisar el stock por color antes de guardar.');
            clientStockError.classList.remove('hidden');
        };

        const collectCurrentColorStockMap = () => {
            const stockMap = {};

            if (primaryColorStockInput) {
                const primaryColor = String(primaryColorStockInput.dataset.currentColor || '').trim();
                const primaryValue = String(primaryColorStockInput.value ?? '').trim();

                if (primaryColor !== '' && primaryValue !== '') {
                    stockMap[primaryColor] = primaryValue;
                }
            }

            const namedStockInputs = Array.from(document.querySelectorAll('input[name^="color_stock["]'));
            namedStockInputs.forEach((input) => {
                const match = String(input.name || '').match(/^color_stock\[(.*)\]$/);
                const color = match && match[1] ? String(match[1]).trim() : '';
                const value = String(input.value ?? '').trim();

                if (color !== '' && value !== '') {
                    stockMap[color] = value;

                    // Compatibilidad: si llega una clave de grupo ("Gris, Rosa"),
                    // también expone cada color individual para la validación cliente.
                    color
                        .split(',')
                        .map((part) => String(part || '').trim())
                        .filter((part) => part !== '')
                        .forEach((part) => {
                            if (!Object.prototype.hasOwnProperty.call(stockMap, part)) {
                                stockMap[part] = value;
                            }
                        });
                }
            });

            return stockMap;
        };

        const validateColorStockBeforeSubmit = () => {
            const selectedColors = getSelectedFrameColorNames();
            const relevantColors = new Set(selectedColors);

            const stockMap = collectCurrentColorStockMap();

            if (relevantColors.size === 0) {
                return null;
            }

            // Si existe stock para la combinación exacta de colores, ya es válido.
            const groupKey = selectedColors.join(', ');
            if (groupKey !== '' && Object.prototype.hasOwnProperty.call(stockMap, groupKey)) {
                const groupValue = String(stockMap[groupKey] ?? '').trim();
                if (groupValue !== '') {
                    return null;
                }
            }

            const missingColors = Array.from(relevantColors).filter((color) => !Object.prototype.hasOwnProperty.call(stockMap, color));
            if (missingColors.length > 0) {
                return `Falta indicar el stock para estos colores: ${missingColors.join(', ')}.`;
            }

            return null;
        };

        const renderPrimaryColorStockHiddenInputs = () => {
            if (!primaryColorStockHiddenInputs) return;

            primaryColorStockHiddenInputs.innerHTML = '';

            if (!primaryColorStockInput) return;

            const currentColors = getSelectedFrameColorNames();
            const currentValue = String(primaryColorStockInput.value ?? '').trim();
            if (currentColors.length === 0 || currentValue === '') return;

            // La combinación principal maneja un único stock de grupo.
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `color_stock[${currentColors.join(', ')}]`;
            hidden.value = currentValue;
            primaryColorStockHiddenInputs.appendChild(hidden);
        };

        const hasPrimaryColorOwner = () => Boolean(primaryColorStockInput);

        const parseColorNameList = (rawValue) => {
            return Array.from(new Set(String(rawValue || '')
                .split(',')
                .map((value) => String(value || '').trim())
                .filter((value) => value !== '')));
        };

        const syncDuplicateColorStockValues = (targetColor) => {
            const normalizedTarget = String(targetColor || '').trim();
            if (normalizedTarget === '') return;

            if (primaryColorStockInput) {
                const primaryColor = String(primaryColorStockInput.dataset.currentColor || '').trim();
                if (primaryColor === normalizedTarget) {
                    const groupVal = getGroupStockValue(getSelectedFrameColorNames());
                    primaryColorStockInput.value = groupVal;
                }
            }

            const existingCards = Array.from(document.querySelectorAll('[data-existing-variant-card]'));
            existingCards.forEach((card) => {
                const hiddenName = card.querySelector('input[data-existing-variant-name]');
                const hiddenColors = card.querySelector('input[data-existing-variant-colors]');
                const stockInput = card.querySelector('input[data-existing-variant-stock]');
                if (!hiddenName || !stockInput) return;

                const colorList = parseColorNameList((hiddenColors && hiddenColors.value) || hiddenName.value);
                if (!colorList.includes(normalizedTarget)) return;

                stockInput.value = getGroupStockValue(colorList);
            });

            const paletteFields = Array.from(document.querySelectorAll('[data-color-palette-field]'));
            const targetSig = getColorGroupSignature([normalizedTarget]);
            paletteFields.forEach((field) => {
                const groupColors = Array.from(field.querySelectorAll('input[type="hidden"][name^="uploaded_color_images_color["], input[type="hidden"][name^="uploaded_color_variant_images_color["]'))
                    .map((input) => String(input.value || '').trim())
                    .filter((value) => value !== '');
                // Only sync cards whose exact group signature matches the target single-color signature
                if (getColorGroupSignature(groupColors) !== targetSig) return;

                const stockInput = field.querySelector('[data-color-stock-input]');
                if (!stockInput) return;

                stockInput.value = getGroupStockValue(groupColors);
            });
        };

        const refreshColorStockEditors = () => {
            const paletteFields = Array.from(document.querySelectorAll('[data-color-palette-field]'));
            const existingCards = Array.from(document.querySelectorAll('[data-existing-variant-card]'));
            const seenColors = new Set();
            const seenColorGroups = new Set();
            const primaryOwnedColor = hasPrimaryColorOwner() && primaryColorStockInput
                ? String(primaryColorStockInput.dataset.currentColor || '').trim()
                : '';

            if (primaryOwnedColor !== '') {
                seenColors.add(primaryOwnedColor);
            }

            existingCards.forEach((card) => {
                const hiddenName = card.querySelector('input[data-existing-variant-name]');
                const hiddenColors = card.querySelector('input[data-existing-variant-colors]');
                const stockInput = card.querySelector('input[data-existing-variant-stock]');
                const stockHiddenInput = card.querySelector('input[data-existing-variant-stock-hidden]');
                const stockHint = card.querySelector('[data-existing-variant-stock-hint]');
                const stockHiddenWrap = card.querySelector('[data-existing-variant-stock-hidden-wrap]');
                if (!hiddenName || !stockInput || !stockHint || !stockHiddenWrap) return;

                const initialVariantStock = String(stockInput.dataset.existingVariantInitialStock || '').trim();

                const groupColors = parseColorNameList((hiddenColors && hiddenColors.value) || hiddenName.value);
                const groupSignature = getColorGroupSignature(groupColors) || 'Gris';

                groupColors.forEach((color) => {
                    if (color !== '' && !seenColors.has(color)) {
                        seenColors.add(color);
                    }
                });

                const primaryGroupSignature = getColorGroupSignature(getSelectedFrameColorNames());
                const isPrimaryOwned = groupSignature !== '' && groupSignature === primaryGroupSignature;
                const isDuplicateGroup = seenColorGroups.has(groupSignature);

                if (!isPrimaryOwned && !seenColorGroups.has(groupSignature)) {
                    seenColorGroups.add(groupSignature);
                }

                const resolvedGroupStock = String(getGroupStockValue(groupColors) ?? '').trim();
                if (resolvedGroupStock !== '') {
                    stockInput.value = resolvedGroupStock;
                }

                if (stockHiddenInput) {
                    stockHiddenInput.value = String(stockInput.value ?? '').trim();
                }

                const isDisabled = groupColors.length === 0 || isPrimaryOwned || isDuplicateGroup;
                stockInput.disabled = isDisabled;
                stockInput.readOnly = isDisabled;
                stockInput.classList.toggle('bg-zinc-100', isDisabled);
                stockInput.classList.toggle('text-zinc-400', isDisabled);
                stockInput.classList.toggle('cursor-not-allowed', isDisabled);

                stockHiddenWrap.innerHTML = '';
                if (!isDisabled) {
                    const hiddenStock = document.createElement('input');
                    hiddenStock.type = 'hidden';
                    hiddenStock.name = `color_stock[${groupColors.join(', ')}]`;
                    hiddenStock.value = stockInput.value;
                    stockHiddenWrap.appendChild(hiddenStock);
                }

                stockHint.textContent = isPrimaryOwned
                    ? 'El stock de esta combinación se maneja arriba en Color principal.'
                    : (isDuplicateGroup
                        ? `El stock de esta combinación se maneja en la primera imagen con ${groupColors.join(', ')}.`
                        : 'La primera imagen de esta combinación maneja el stock.');
            });

            paletteFields.forEach((field) => {
                const groupSignature = String(field.dataset.colorGroupSignature || '').trim() || 'Gris';
                const groupColors = Array.from(field.querySelectorAll('input[type="hidden"][name^="uploaded_color_images_color["], input[type="hidden"][name^="uploaded_color_variant_images_color["]'))
                    .map((input) => String(input.value || '').trim())
                    .filter((value) => value !== '');
                const stockInput = field.querySelector('[data-color-stock-input]');
                const stockHint = field.querySelector('[data-color-stock-hint]');
                const stockHiddenWrap = field.querySelector('[data-color-stock-hidden-wrap]');
                if (!stockInput || !stockHint || !stockHiddenWrap) return;

                groupColors.forEach((color) => {
                    if (color !== '' && !seenColors.has(color)) {
                        seenColors.add(color);
                    }
                });

                const primaryGroupSignature = getColorGroupSignature(getSelectedFrameColorNames());
                const isPrimaryOwned = groupSignature !== '' && groupSignature === primaryGroupSignature;
                const isDuplicateGroup = seenColorGroups.has(groupSignature);

                if (!isPrimaryOwned && !seenColorGroups.has(groupSignature)) {
                    seenColorGroups.add(groupSignature);
                }

                stockInput.value = getGroupStockValue(groupColors);

                const isDisabled = groupColors.length === 0 || isPrimaryOwned || isDuplicateGroup;
                stockInput.disabled = isDisabled;
                stockInput.readOnly = isDisabled;
                stockInput.classList.toggle('bg-zinc-100', isDisabled);
                stockInput.classList.toggle('text-zinc-400', isDisabled);
                stockInput.classList.toggle('cursor-not-allowed', isDisabled);
                field.dataset.colorGroupOwner = !isDisabled ? 'true' : 'false';

                stockHiddenWrap.innerHTML = '';
                if (!isDisabled) {
                    const hiddenStock = document.createElement('input');
                    hiddenStock.type = 'hidden';
                    hiddenStock.name = `color_stock[${groupColors.join(', ')}]`;
                    hiddenStock.value = stockInput.value;
                    stockHiddenWrap.appendChild(hiddenStock);
                }

                stockHint.textContent = isPrimaryOwned
                    ? 'El stock de esta combinacion se maneja arriba en Color principal.'
                    : (isDuplicateGroup
                        ? `El stock de esta combinacion se maneja en la primera imagen con ${groupColors.join(', ')}.`
                        : 'La primera imagen de esta combinacion maneja el stock.');
            });
        };

        const setupExistingVariantEditors = () => {
            const cards = Array.from(document.querySelectorAll('[data-existing-variant-card]'));
            cards.forEach((card) => {
                const hiddenName = card.querySelector('input[data-existing-variant-name]');
                const hiddenColors = card.querySelector('input[data-existing-variant-colors]');
                const nameLabel = card.querySelector('[data-existing-variant-name-label]');
                const stockInput = card.querySelector('input[data-existing-variant-stock]');
                const stockHiddenInput = card.querySelector('input[data-existing-variant-stock-hidden]');
                const stockHiddenWrap = card.querySelector('[data-existing-variant-stock-hidden-wrap]');
                const previewWrap = card.querySelector('[data-existing-variant-preview-wrap]');
                const checkboxes = Array.from(card.querySelectorAll('input[type="checkbox"][data-existing-variant-color-checkbox]'));

                if (!hiddenName || !stockInput || !stockHiddenWrap || !previewWrap || checkboxes.length === 0) return;

                const initialVariantStock = String(stockInput.dataset.existingVariantInitialStock || '').trim();

                const applyColors = (nextColorsRaw) => {
                    const nextColors = Array.from(new Set((Array.isArray(nextColorsRaw) ? nextColorsRaw : [nextColorsRaw])
                        .map((value) => String(value || '').trim())
                        .filter((value) => value !== '')));
                    const resolvedColors = nextColors.length > 0 ? nextColors : ['Gris'];
                    const labelValue = resolvedColors.join(', ');

                    hiddenName.value = labelValue;
                    if (hiddenColors) {
                        hiddenColors.value = resolvedColors.join(',');
                    }

                    if (nameLabel) {
                        nameLabel.textContent = labelValue;
                    }

                    previewWrap.innerHTML = '';
                    previewWrap.appendChild(buildColorPieChartEl(resolvedColors));

                    const resolvedGroupStock = String(getGroupStockValue(resolvedColors) ?? '').trim();
                    const currentValue = String(stockInput.value ?? '').trim();
                    const fallbackStock = initialVariantStock !== '' ? initialVariantStock : currentValue;
                    const nextStockValue = resolvedGroupStock !== '' ? resolvedGroupStock : fallbackStock;
                    stockInput.value = nextStockValue;
                    if (stockHiddenInput) {
                        stockHiddenInput.value = nextStockValue;
                    }

                    if (nextStockValue !== '') {
                        setGroupStockValue(resolvedColors, nextStockValue);
                    }

                    stockHiddenWrap.innerHTML = '';
                    const hiddenStockApply = document.createElement('input');
                    hiddenStockApply.type = 'hidden';
                    hiddenStockApply.name = `color_stock[${resolvedColors.join(', ')}]`;
                    hiddenStockApply.value = stockInput.value;
                    stockHiddenWrap.appendChild(hiddenStockApply);

                    resolvedColors.forEach((color) => syncDuplicateColorStockValues(color));
                    refreshColorStockEditors();
                };

                let currentColors = checkboxes
                    .filter((checkbox) => checkbox.checked)
                    .map((checkbox) => String(checkbox.value || '').trim())
                    .filter((value) => value !== '');

                if (currentColors.length === 0) {
                    const fallback = parseColorNameList((hiddenColors && hiddenColors.value) || hiddenName.value);
                    currentColors = fallback.length > 0 ? fallback : ['Gris'];
                    checkboxes.forEach((checkbox) => {
                        checkbox.checked = currentColors.includes(String(checkbox.value || '').trim());
                    });
                }
                applyColors(currentColors);

                checkboxes.forEach((checkbox) => {
                    checkbox.addEventListener('change', () => {
                        const selected = checkboxes
                            .filter((candidate) => candidate.checked)
                            .map((candidate) => String(candidate.value || '').trim())
                            .filter((value) => value !== '');
                        applyColors(selected);
                        syncTotalStock();
                    });
                });

                stockInput.addEventListener('input', () => {
                    const colors = parseColorNameList((hiddenColors && hiddenColors.value) || hiddenName.value);
                    if (stockHiddenInput) {
                        stockHiddenInput.value = String(stockInput.value ?? '').trim();
                    }
                    setGroupStockValue(colors, stockInput.value);
                    colors.forEach((color) => syncDuplicateColorStockValues(color));
                    refreshColorStockEditors();
                    syncTotalStock();
                });
            });
        };

        const setupExistingCameraVariantEditors = () => {
            const cards = Array.from(document.querySelectorAll('[data-camera-variant-card]'));
            cards.forEach((card) => {
                const hiddenName = card.querySelector('input[data-camera-variant-name]');
                const hiddenColors = card.querySelector('input[data-camera-variant-colors]');
                const nameLabel = card.querySelector('[data-camera-variant-name-label]');
                const groupOptionsWrap = card.querySelector('[data-camera-variant-group-options]');
                const variantCardId = String(card.dataset.cameraVariantCard || '').trim();
                const availableGroups = getAvailableCameraColorGroups();

                if (!hiddenName || !groupOptionsWrap || availableGroups.length === 0 || variantCardId === '') {
                    return;
                }

                const groupsBySignature = new Map();
                availableGroups.forEach((group) => {
                    groupsBySignature.set(getColorGroupSignature(group), group);
                });

                let selectedGroupSignature = getColorGroupSignature(parseColorNameList((hiddenColors && hiddenColors.value) || hiddenName.value));
                if (!groupsBySignature.has(selectedGroupSignature)) {
                    selectedGroupSignature = getColorGroupSignature(availableGroups[0]);
                }

                const applyGroup = (groupSignature) => {
                    const resolvedColors = groupsBySignature.get(groupSignature) || availableGroups[0] || ['Gris'];
                    const labelValue = resolvedColors.join(', ');

                    hiddenName.value = labelValue;
                    if (hiddenColors) {
                        hiddenColors.value = resolvedColors.join(',');
                    }

                    if (nameLabel) {
                        nameLabel.textContent = labelValue;
                    }
                };

                groupOptionsWrap.innerHTML = '';
                availableGroups.forEach((group, groupIndex) => {
                    const signature = getColorGroupSignature(group);

                    const optionLabel = document.createElement('label');
                    optionLabel.className = 'inline-flex cursor-pointer items-center';

                    const radio = document.createElement('input');
                    radio.type = 'radio';
                    radio.name = `existing_camera_group_${variantCardId}`;
                    radio.value = signature;
                    radio.className = 'peer sr-only';
                    radio.checked = signature === selectedGroupSignature;

                    radio.addEventListener('change', () => {
                        if (!radio.checked) return;
                        selectedGroupSignature = signature;
                        applyGroup(selectedGroupSignature);
                    });

                    const pie = buildColorPieChartEl(group);
                    pie.className = 'inline-flex items-center justify-center rounded-full ring-2 ring-zinc-300 transition peer-checked:ring-zinc-800';
                    pie.title = `Grupo ${groupIndex + 1}: ${group.join(', ')}`;

                    optionLabel.appendChild(radio);
                    optionLabel.appendChild(pie);
                    groupOptionsWrap.appendChild(optionLabel);
                });

                applyGroup(selectedGroupSignature);
            });
        };

        const syncColorPrompt = () => {
            if (!colorPrompt) return;

            const selectedColors = getSelectedFrameColorNames();
            const selectedColor = selectedColors[0] || 'Gris';

            if (colorText) {
                colorText.textContent = selectedColors.length > 0 ? selectedColors.join(', ') : selectedColor;
            }

            if (primaryColorStockInput) {
                primaryColorStockInput.dataset.currentColor = selectedColor;
                primaryColorStockInput.value = getGroupStockValue(selectedColors);
            }

            renderPrimaryColorStockHiddenInputs();
            refreshColorStockEditors();
        };

        const colorNameToHex = Object.fromEntries(colorOptions.map((o) => [String(o.name), String(o.hex || '#cec9bc')]));
        const colorNameOrder = new Map(colorOptions.map((option, index) => [String(option.name || '').trim().toLowerCase(), index]));

        const canonicalizeColorNames = (colors) => {
            const normalized = Array.from(new Set((Array.isArray(colors) ? colors : [colors])
                .map((value) => String(value || '').trim())
                .filter((value) => value !== '')));

            normalized.sort((left, right) => {
                const leftKey = left.toLowerCase();
                const rightKey = right.toLowerCase();
                const leftIndex = colorNameOrder.has(leftKey) ? Number(colorNameOrder.get(leftKey)) : Number.MAX_SAFE_INTEGER;
                const rightIndex = colorNameOrder.has(rightKey) ? Number(colorNameOrder.get(rightKey)) : Number.MAX_SAFE_INTEGER;

                if (leftIndex !== rightIndex) {
                    return leftIndex - rightIndex;
                }

                return left.localeCompare(right, 'es', { sensitivity: 'base' });
            });

            return normalized;
        };

        const buildColorPieChartEl = (colorNames) => {
            const size = 48;
            const container = document.createElement('div');
            container.className = 'inline-flex items-center justify-center';
            if (!colorNames.length) return container;
            const hexColors = colorNames.map((n) => colorNameToHex[n] || '#cec9bc');

            // A single 360deg arc path is unreliable in SVG and can look white/empty.
            // Render a solid circle for one-color groups.
            if (hexColors.length === 1) {
                container.innerHTML = `<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" style="border-radius:50%;overflow:hidden;border:2px solid #e4e4e7;"><circle cx="${size / 2}" cy="${size / 2}" r="${size / 2}" fill="${hexColors[0]}" /></svg>`;
                return container;
            }

            const angleStep = 360 / hexColors.length;
            let paths = '';
            for (let i = 0; i < hexColors.length; i++) {
                const startAngle = angleStep * i - 90;
                const endAngle = angleStep * (i + 1) - 90;
                const x1 = size/2 + (size/2) * Math.cos(Math.PI * startAngle / 180);
                const y1 = size/2 + (size/2) * Math.sin(Math.PI * startAngle / 180);
                const x2 = size/2 + (size/2) * Math.cos(Math.PI * endAngle / 180);
                const y2 = size/2 + (size/2) * Math.sin(Math.PI * endAngle / 180);
                const largeArc = angleStep > 180 ? 1 : 0;
                paths += `<path d="M${size/2},${size/2} L${x1},${y1} A${size/2},${size/2} 0 ${largeArc} 1 ${x2},${y2} Z" fill="${hexColors[i]}" />`;
            }
            container.innerHTML = `<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" style="border-radius:50%;overflow:hidden;border:2px solid #e4e4e7;">${paths}</svg>`;
            return container;
        };

        const buildColorPaletteField = (entry, fieldIndex, uniqueKey, initialOrder = 1) => {
            const wrap = document.createElement('div');
            wrap.className = 'grid gap-2';
            wrap.dataset.colorPaletteField = String(uniqueKey);

            const selectedColors = new Set(getEntrySelectedColors(entry));

            const hiddenColorsWrap = document.createElement('div');
            hiddenColorsWrap.dataset.colorPaletteHiddenWrap = String(uniqueKey);

            const hiddenOrder = document.createElement('input');
            hiddenOrder.type = 'hidden';
            hiddenOrder.name = 'uploaded_color_images_order[]';
            hiddenOrder.value = String(initialOrder);
            hiddenOrder.dataset.colorOrderHidden = String(uniqueKey);

            const label = document.createElement('p');
            label.className = 'text-[11px] font-semibold text-zinc-600';
            label.dataset.camera3dGroupLabel = String(uniqueKey);
            label.textContent = 'Grupo de colores:';

            const palette = document.createElement('div');
            palette.className = 'flex flex-wrap items-center gap-2';

            const paletteHint = document.createElement('p');
            paletteHint.className = 'text-[11px] text-zinc-500';
            paletteHint.textContent = 'Esta imagen es solo para camara 3D (sin stock).';

            const metaRow = document.createElement('div');
            metaRow.className = 'grid gap-3 sm:grid-cols-[minmax(0,1fr)]';

            const availableGroups = getAvailableCameraColorGroups();
            const groupBySignature = new Map();
            availableGroups.forEach((group) => {
                groupBySignature.set(getColorGroupSignature(group), group);
            });

            let selectedGroupSignature = getColorGroupSignature(getEntrySelectedColors(entry));
            if (!groupBySignature.has(selectedGroupSignature)) {
                selectedGroupSignature = getColorGroupSignature(availableGroups[0] || getDefaultCameraColorNames());
            }

            const syncSelectedColors = () => {
                const fallbackColors = getDefaultCameraColorNames();
                const resolvedFromGroup = groupBySignature.get(selectedGroupSignature) || fallbackColors;
                const resolvedColors = normalizeCameraColorGroup(resolvedFromGroup);
                entry.colors = resolvedColors;
                entry.color = resolvedColors[0] || 'Gris';
                wrap.dataset.colorGroupSignature = getEntryColorGroupSignature(entry) || 'Gris';

                label.textContent = `Colores: ${resolvedColors.join(', ')}`;
                hiddenColorsWrap.innerHTML = '';

                resolvedColors.forEach((colorName) => {
                    const hiddenColor = document.createElement('input');
                    hiddenColor.type = 'hidden';
                    hiddenColor.name = `uploaded_color_images_color[${fieldIndex}][]`;
                    hiddenColor.value = colorName;
                    hiddenColorsWrap.appendChild(hiddenColor);
                });
            };

            availableGroups.forEach((group, groupIndex) => {
                const signature = getColorGroupSignature(group);

                const groupLabel = document.createElement('label');
                groupLabel.className = 'inline-flex cursor-pointer items-center';

                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = `camera_color_group_${uniqueKey}`;
                radio.value = signature;
                radio.className = 'peer sr-only';
                radio.checked = signature === selectedGroupSignature;

                radio.addEventListener('change', () => {
                    if (!radio.checked) {
                        return;
                    }

                    hideClientStockError();
                    const previousGroupSignature = getEntryColorGroupSignature(entry) || 'Gris';
                    selectedGroupSignature = signature;
                    syncSelectedColors();
                    const nextGroupSignature = getEntryColorGroupSignature(entry) || 'Gris';

                    if (nextGroupSignature !== previousGroupSignature) {
                        entry.order = getNextColorImageOrder(getEntrySelectedColors(entry), entry.id);
                        hiddenOrder.value = String(entry.order);
                        normalizeColorImageOrders([previousGroupSignature, nextGroupSignature]);
                    }

                    syncColorImageRows();
                });

                const pie = buildColorPieChartEl(group);
                pie.className = 'inline-flex items-center justify-center rounded-full ring-2 ring-zinc-300 transition peer-checked:ring-zinc-800';
                pie.title = `Grupo ${groupIndex + 1}: ${group.join(', ')}`;

                groupLabel.appendChild(radio);
                groupLabel.appendChild(pie);
                palette.appendChild(groupLabel);
            });

            metaRow.appendChild(paletteHint);

            syncSelectedColors();

            wrap.appendChild(hiddenColorsWrap);
            wrap.appendChild(hiddenOrder);
            wrap.appendChild(label);
            wrap.appendChild(palette);
            wrap.appendChild(metaRow);

            return wrap;
        };

        const buildVariantColorPaletteField = (entry, fieldIndex, uniqueKey) => {
            const wrap = document.createElement('div');
            wrap.className = 'grid gap-2';
            wrap.dataset.colorPaletteField = String(uniqueKey);

            const hiddenColorsWrap = document.createElement('div');
            hiddenColorsWrap.dataset.colorPaletteHiddenWrap = String(uniqueKey);

            const hiddenOrder = document.createElement('input');
            hiddenOrder.type = 'hidden';
            hiddenOrder.name = 'uploaded_color_variant_images_order[]';
            hiddenOrder.value = String(Math.max(1, Number.parseInt(entry.order || String(fieldIndex + 1), 10) || (fieldIndex + 1)));
            hiddenOrder.dataset.colorOrderHidden = String(uniqueKey);

            const label = document.createElement('p');
            label.className = 'text-[11px] font-semibold text-zinc-600';
            label.textContent = 'Grupo de colores:';

            const palette = document.createElement('div');
            palette.className = 'flex flex-wrap items-center gap-2';

            const paletteHint = document.createElement('p');
            paletteHint.className = 'text-[11px] text-zinc-500';
            paletteHint.textContent = 'Estas imagenes cambian la foto en la ficha publica por color.';

            const previewRow = document.createElement('div');
            previewRow.className = 'mt-1 flex items-center gap-2';

            const previewWrap = document.createElement('div');
            previewWrap.className = 'shrink-0';

            const previewHint = document.createElement('p');
            previewHint.className = 'text-[11px] text-zinc-500';
            previewHint.textContent = 'Vista previa de colores';

            previewRow.appendChild(previewWrap);
            previewRow.appendChild(previewHint);

            const detailGrid = document.createElement('div');
            detailGrid.className = 'mt-1 grid gap-3 sm:grid-cols-2';

            const stockCol = document.createElement('div');
            const stockLabel = document.createElement('label');
            stockLabel.className = 'text-[11px] font-semibold text-zinc-700';
            stockLabel.textContent = 'Stock para este grupo';

            const stockInput = document.createElement('input');
            stockInput.type = 'number';
            stockInput.min = '0';
            stockInput.placeholder = '0';
            stockInput.className = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-400';
            stockInput.dataset.colorStockInput = String(uniqueKey);

            const stockHiddenWrap = document.createElement('div');
            stockHiddenWrap.dataset.colorStockHiddenWrap = String(uniqueKey);

            const stockHint = document.createElement('p');
            stockHint.className = 'mt-1 text-[11px] text-zinc-500';
            stockHint.dataset.colorStockHint = String(uniqueKey);
            stockHint.textContent = 'La primera imagen de esta combinación maneja el stock.';

            stockCol.appendChild(stockLabel);
            stockCol.appendChild(stockInput);
            stockCol.appendChild(stockHiddenWrap);
            stockCol.appendChild(stockHint);

            const orderCol = document.createElement('div');
            const orderLabel = document.createElement('label');
            orderLabel.className = 'text-[11px] font-semibold text-zinc-700';
            orderLabel.textContent = 'Orden';

            const orderInput = document.createElement('input');
            orderInput.type = 'number';
            orderInput.min = '1';
            orderInput.step = '1';
            orderInput.inputMode = 'numeric';
            orderInput.pattern = '[0-9]*';
            orderInput.placeholder = '1';
            orderInput.className = 'mt-2 w-full rounded-2xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:border-zinc-400';
            orderInput.value = hiddenOrder.value;

            const orderHint = document.createElement('p');
            orderHint.className = 'mt-1 text-[11px] text-zinc-500';
            orderHint.textContent = 'Ej: 1, 2, 3';

            orderCol.appendChild(orderLabel);
            orderCol.appendChild(orderInput);
            orderCol.appendChild(orderHint);

            detailGrid.appendChild(stockCol);
            detailGrid.appendChild(orderCol);

            const selectedColors = new Set(getEntrySelectedColors(entry));

            const syncSelectedColors = () => {
                const resolvedColors = canonicalizeColorNames(Array.from(selectedColors));
                entry.colors = resolvedColors.length > 0 ? resolvedColors : ['Gris'];
                entry.color = entry.colors[0] || 'Gris';
                wrap.dataset.colorGroupSignature = getEntryColorGroupSignature(entry) || 'Gris';

                label.textContent = `Colores: ${entry.colors.join(', ')}`;
                hiddenColorsWrap.innerHTML = '';
                entry.colors.forEach((colorName) => {
                    const hiddenColor = document.createElement('input');
                    hiddenColor.type = 'hidden';
                    hiddenColor.name = `uploaded_color_variant_images_color[${fieldIndex}][]`;
                    hiddenColor.value = colorName;
                    hiddenColorsWrap.appendChild(hiddenColor);
                });

                previewWrap.innerHTML = '';
                previewWrap.appendChild(buildColorPieChartEl(entry.colors));

                const groupStockValue = String(getGroupStockValue(entry.colors) ?? '').trim();
                if (groupStockValue !== '') {
                    stockInput.value = groupStockValue;
                }

                stockHiddenWrap.innerHTML = '';
                const hiddenStockSync = document.createElement('input');
                hiddenStockSync.type = 'hidden';
                hiddenStockSync.name = `color_stock[${entry.colors.join(', ')}]`;
                hiddenStockSync.value = String(stockInput.value ?? '').trim();
                stockHiddenWrap.appendChild(hiddenStockSync);
            };

            colorOptions.forEach((option) => {
                const colorName = String(option.name || '').trim();
                if (colorName === '') return;

                const colorLabel = document.createElement('label');
                colorLabel.className = 'inline-flex cursor-pointer items-center';

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'peer sr-only';
                checkbox.value = colorName;
                checkbox.checked = selectedColors.has(colorName);

                checkbox.addEventListener('change', () => {
                    if (checkbox.checked) {
                        selectedColors.add(colorName);
                    } else {
                        selectedColors.delete(colorName);
                    }

                    syncSelectedColors();
                    refreshColorStockEditors();
                    syncTotalStock();
                });

                const swatch = document.createElement('span');
                swatch.className = 'inline-flex h-8 w-8 rounded-full border-2 border-white shadow-sm ring-2 ring-zinc-300 transition peer-checked:ring-zinc-800';
                swatch.style.backgroundColor = String(option.hex || '#cec9bc');
                swatch.title = colorName;

                colorLabel.appendChild(checkbox);
                colorLabel.appendChild(swatch);
                palette.appendChild(colorLabel);
            });

            stockInput.addEventListener('input', () => {
                const nextValue = String(stockInput.value ?? '').trim();
                setGroupStockValue(entry.colors, nextValue);

                stockHiddenWrap.innerHTML = '';
                const hiddenStockInput = document.createElement('input');
                hiddenStockInput.type = 'hidden';
                hiddenStockInput.name = `color_stock[${entry.colors.join(', ')}]`;
                hiddenStockInput.value = nextValue;
                stockHiddenWrap.appendChild(hiddenStockInput);

                entry.colors.forEach((colorName) => syncDuplicateColorStockValues(colorName));
                refreshColorStockEditors();
                syncTotalStock();
            });

            const syncOrderValue = () => {
                const numericValue = Math.max(1, Number.parseInt(orderInput.value || '1', 10) || 1);
                orderInput.value = String(numericValue);
                hiddenOrder.value = String(numericValue);
                entry.order = numericValue;
            };

            orderInput.addEventListener('input', syncOrderValue);
            orderInput.addEventListener('blur', syncOrderValue);

            syncSelectedColors();
            stockInput.value = String(getGroupStockValue(entry.colors) ?? '');

            wrap.appendChild(hiddenColorsWrap);
            wrap.appendChild(hiddenOrder);
            wrap.appendChild(label);
            wrap.appendChild(palette);
            wrap.appendChild(previewRow);
            wrap.appendChild(paletteHint);
            wrap.appendChild(detailGrid);

            return wrap;
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

        const releaseVariantImageUrls = () => {
            variantImageObjectUrls.forEach((u) => {
                try {
                    URL.revokeObjectURL(u);
                } catch (_) {
                    // noop
                }
            });
            variantImageObjectUrls = [];
        };

        const syncTotalStock = () => {
            const ownerStockInputs = [];

            if (primaryColorStockInput && getSelectedFrameColorNames().length > 0) {
                ownerStockInputs.push(primaryColorStockInput);
            }

            document.querySelectorAll('[data-color-group-owner="true"] [data-color-stock-input]').forEach((input) => {
                ownerStockInputs.push(input);
            });

            document.querySelectorAll('[data-existing-variant-card] input[data-existing-variant-stock]:not([disabled])').forEach((input) => {
                ownerStockInputs.push(input);
            });

            const total = ownerStockInputs.reduce((sum, input) => {
                const value = Number(input.value || 0);
                const safeValue = Number.isFinite(value) ? Math.max(0, value) : 0;
                return sum + safeValue;
            }, 0);

            if (totalStockInput) {
                totalStockInput.value = String(total);
            }
        };

        const syncColorImageRows = () => {
            if (!colorImagesInput || !colorImagesMap || !colorImagesRows) return;

            // Cámara 3D: aplicar regla de máximo 1 imagen por grupo y sin duplicados.
            enforceUniqueCamera3DGroupAssignments();

            releaseColorImageUrls();

            const entries = selectedColorImageEntries.filter((entry) => entry && entry.file);
            colorImagesRows.innerHTML = '';
            colorImagesMap.classList.toggle('hidden', entries.length === 0);

            if (colorImagesCount) {
                colorImagesCount.textContent = entries.length > 0 ? `${entries.length} imagen${entries.length === 1 ? '' : 'es'}` : '';
            }

            if (!entries.length) return;

            entries.forEach((entry, index) => {
                const file = entry.file;
                const card = document.createElement('div');
                card.className = 'max-w-[320px] overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm';

                const previewWrap = document.createElement('div');
                previewWrap.className = 'relative aspect-[4/3] w-full bg-zinc-100';

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'absolute right-2 top-2 z-10 inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-600 text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300';
                removeButton.setAttribute('aria-label', `Eliminar ${file.name || `Imagen ${index + 1}`}`);
                removeButton.title = 'Eliminar imagen';
                removeButton.innerHTML = `
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 6h18" />
                        <path d="M8 6V4.75A1.75 1.75 0 0 1 9.75 3h4.5A1.75 1.75 0 0 1 16 4.75V6" />
                        <path d="M6.5 6l.9 12.1A2 2 0 0 0 9.39 20h5.22a2 2 0 0 0 1.99-1.9L17.5 6" />
                        <path d="M10 10.25v5.5" />
                        <path d="M14 10.25v5.5" />
                    </svg>
                `;

                removeButton.addEventListener('click', () => {
                    hideClientStockError();
                    const removedGroupSignature = getEntryColorGroupSignature(entry) || 'Gris';
                    selectedColorImageEntries = selectedColorImageEntries.filter((candidate) => candidate.id !== entry.id);
                    normalizeColorImageOrders([removedGroupSignature]);
                    syncColorImageInputFiles();
                    syncColorImageRows();
                    syncTotalStock();
                });

                const previewImg = document.createElement('img');
                previewImg.className = 'absolute inset-0 h-full w-full object-contain';
                previewImg.alt = file.name || `Imagen ${index + 1}`;
                const objectUrl = URL.createObjectURL(file);
                colorImageObjectUrls.push(objectUrl);
                previewImg.src = objectUrl;

                previewWrap.appendChild(removeButton);
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

                const paletteField = buildColorPaletteField(entry, index, entry.id, entry.order || (index + 1));

                body.appendChild(name);
                body.appendChild(meta);
                body.appendChild(paletteField);

                card.appendChild(previewWrap);
                card.appendChild(body);
                colorImagesRows.appendChild(card);
            });

            // Mark cards that share the same color group signature so users know they belong to the same filter
            const signatureCards = {};
            colorImagesRows.querySelectorAll('[data-color-palette-field]').forEach((field) => {
                const sig = field.dataset.colorGroupSignature || '';
                if (!sig) return;
                if (!signatureCards[sig]) signatureCards[sig] = [];
                signatureCards[sig].push(field.closest('[class*="rounded-2xl"]'));
            });
            Object.entries(signatureCards).forEach(([sig, cards]) => {
                cards.forEach((card, idx) => {
                    if (!card) return;
                    let badge = card.querySelector('[data-group-badge]');
                    if (cards.length > 1) {
                        if (!badge) {
                            badge = document.createElement('div');
                            badge.dataset.groupBadge = sig;
                            badge.className = 'mx-3 mb-2 flex items-center gap-1 rounded-lg bg-blue-50 px-2 py-1 text-[11px] text-blue-700 border border-blue-200';
                            badge.innerHTML = `<svg class="h-3 w-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> <span>Mismo grupo que ${cards.length - 1} otra${cards.length - 1 !== 1 ? 's' : ''} imagen${cards.length - 1 !== 1 ? 'es' : ''} — comparten filtro</span>`;
                            card.appendChild(badge);
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                });
            });

            // Cámara 3D: bloquear grupos ya usados y marcar duplicados en rojo.
            applyCamera3DGroupLocksToUi();

            refreshColorStockEditors();
        };

        const syncVariantImageRows = () => {
            if (!variantColorImagesInput || !variantColorImagesMap || !variantColorImagesRows) return;

            releaseVariantImageUrls();

            const entries = selectedVariantImageEntries.filter((entry) => entry && entry.file);
            variantColorImagesRows.innerHTML = '';
            variantColorImagesMap.classList.toggle('hidden', entries.length === 0);

            if (variantColorImagesCount) {
                variantColorImagesCount.textContent = entries.length > 0 ? `${entries.length} imagen${entries.length === 1 ? '' : 'es'}` : '';
            }

            if (!entries.length) return;

            entries.forEach((entry, index) => {
                const file = entry.file;
                const card = document.createElement('div');
                card.className = 'max-w-[320px] overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm';

                const previewWrap = document.createElement('div');
                previewWrap.className = 'relative aspect-[4/3] w-full bg-zinc-100';

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'absolute right-2 top-2 z-10 inline-flex h-8 w-8 items-center justify-center rounded-full bg-red-600 text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300';
                removeButton.setAttribute('aria-label', `Eliminar ${file.name || `Imagen ${index + 1}`}`);
                removeButton.title = 'Eliminar imagen';
                removeButton.innerHTML = `
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 6h18" />
                        <path d="M8 6V4.75A1.75 1.75 0 0 1 9.75 3h4.5A1.75 1.75 0 0 1 16 4.75V6" />
                        <path d="M6.5 6l.9 12.1A2 2 0 0 0 9.39 20h5.22a2 2 0 0 0 1.99-1.9L17.5 6" />
                        <path d="M10 10.25v5.5" />
                        <path d="M14 10.25v5.5" />
                    </svg>
                `;

                removeButton.addEventListener('click', () => {
                    selectedVariantImageEntries = selectedVariantImageEntries.filter((candidate) => candidate.id !== entry.id);
                    syncVariantImageInputFiles();
                    syncVariantImageRows();
                });

                const previewImg = document.createElement('img');
                previewImg.className = 'absolute inset-0 h-full w-full object-contain';
                previewImg.alt = file.name || `Imagen ${index + 1}`;
                const objectUrl = URL.createObjectURL(file);
                variantImageObjectUrls.push(objectUrl);
                previewImg.src = objectUrl;

                previewWrap.appendChild(removeButton);
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

                const paletteField = buildVariantColorPaletteField(entry, index, entry.id);

                body.appendChild(name);
                body.appendChild(meta);
                body.appendChild(paletteField);

                card.appendChild(previewWrap);
                card.appendChild(body);
                variantColorImagesRows.appendChild(card);
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
                syncInlineUploadPreview();
                return;
            }
            setPreview(urlInput.value);
            syncInlineUploadPreview();
        });
        urlInput.addEventListener('paste', () => setTimeout(() => {
            if (uploadedInput && uploadedInput.files && uploadedInput.files[0]) {
                setPreviewFromFile();
                syncInlineUploadPreview();
                return;
            }
            setPreview(urlInput.value);
            syncInlineUploadPreview();
        }, 0));
        if (uploadedInput) {
            uploadedInput.addEventListener('change', () => {
                setPreviewFromFile();
                syncColorPrompt();
                syncInlineUploadPreview();
                refreshColorStockEditors();
            });
        }
        if (colorImagesInput) {
            colorImagesInput.addEventListener('change', () => {
                appendSelectedColorImages(Array.from(colorImagesInput.files || []));
                syncColorImageRows();
            });
        }
        if (variantColorImagesInput) {
            variantColorImagesInput.addEventListener('change', () => {
                appendSelectedVariantImages(Array.from(variantColorImagesInput.files || []));
                syncVariantImageRows();
            });
        }
        if (primaryColorStockInput) {
            primaryColorStockInput.addEventListener('input', () => {
                hideClientStockError();
                const currentColors = getSelectedFrameColorNames();
                if (currentColors.length > 0) {
                    setGroupStockValue(currentColors, primaryColorStockInput.value);
                }

                renderPrimaryColorStockHiddenInputs();
                refreshColorStockEditors();
                syncTotalStock();
            });
        }
        colorInputs.forEach((input) => {
            input.addEventListener('change', () => {
                hideClientStockError();
                syncColorPrompt();
                setupExistingCameraVariantEditors();
                syncVariantImageRows();
                syncColorImageRows();
                syncTotalStock();
            });
        });

        const ownerForm = urlInput.closest('form');
        if (ownerForm) {
            ownerForm.addEventListener('submit', (event) => {
                const camera3dValidationMessage = validateCamera3DGroupsBeforeSubmit();
                if (camera3dValidationMessage !== null) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    ownerForm.dataset.isSubmitting = 'false';

                    const submitButton = ownerForm.querySelector('[data-submit-button]');
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.removeAttribute('aria-disabled');
                        submitButton.textContent = submitButton.dataset.defaultLabel || submitButton.textContent || 'Guardar';
                    }

                    showClientStockError(camera3dValidationMessage);
                    syncColorImageRows();
                    return;
                }

                const stockValidationMessage = validateColorStockBeforeSubmit();
                if (stockValidationMessage === null) {
                    hideClientStockError();
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();
                ownerForm.dataset.isSubmitting = 'false';

                const submitButton = ownerForm.querySelector('[data-submit-button]');
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.removeAttribute('aria-disabled');
                    submitButton.textContent = submitButton.dataset.defaultLabel || submitButton.textContent || 'Guardar';
                }

                showClientStockError(stockValidationMessage);
                clientStockError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, true);
        }
        zoomPct.addEventListener('input', applyFrame);
        posX.addEventListener('input', applyFrame);
        posY.addEventListener('input', applyFrame);

        previewBg.addEventListener('error', () => { previewBg.src = placeholder; });
        previewFg.addEventListener('error', () => { previewFg.src = placeholder; });

        applyFrame();
        syncColorPrompt();
        setupExistingVariantEditors();
        setupExistingCameraVariantEditors();
        syncVariantImageRows();
        syncColorImageRows();
        syncInlineUploadPreview();
        syncTotalStock();
    })();
</script>

