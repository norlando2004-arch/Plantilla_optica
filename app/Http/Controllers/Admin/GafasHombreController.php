<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloqueContenido;
use App\Models\Producto;
use App\Services\GafasHombrePromoContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GafasHombreController extends Controller
{
    use Concerns\HandlesProductoImages;
    use Concerns\HandlesGafasExcelImport;

    private const TIPO = 'gafas';
    private const GENERO = 'male';

    private const IMG_MID_ZOOM = 0.99;

    public function index()
    {
        $productos = Producto::query()
            ->where('tipo', self::TIPO)
            ->whereIn('genero_objetivo', [self::GENERO, 'unisex'])
            ->latest('id')
            ->get();

        return view('admin.gafas_hombre.index', [
            'productos' => $productos,
            'promoImage' => GafasHombrePromoContent::load(),
        ]);
    }

    public function updatePromoImage(Request $request): RedirectResponse
    {
        $request->validate([
            'promo_image_file' => ['nullable', 'file', 'image', 'max:51200'],
            'promo_image_files' => ['nullable', 'array', 'max:20'],
            'promo_image_files.*' => ['nullable', 'file', 'image', 'max:51200'],
            'remove_promo_image' => ['nullable', 'boolean'],
            'remove_promo_image_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $removePromoImage = $request->boolean('remove_promo_image');
        $removePromoImageId = (int) $request->input('remove_promo_image_id', 0);

        $block = BloqueContenido::query()->firstOrCreate(
            ['clave' => GafasHombrePromoContent::BLOCK_KEY],
            [
                'titulo' => 'Gafas hombre: promo de cabecera',
                'cuerpo' => null,
                'datos' => GafasHombrePromoContent::defaults(),
                'esta_activo' => true,
                'orden' => 1,
            ]
        );

        if ($request->hasFile('promo_image_files')) {
            $this->appendContentBlockImages($block, GafasHombrePromoContent::FIELD_IMAGE, (array) $request->file('promo_image_files'));
        } elseif ($request->hasFile('promo_image_file')) {
            $this->appendContentBlockImages($block, GafasHombrePromoContent::FIELD_IMAGE, [$request->file('promo_image_file')]);
        } elseif ($removePromoImageId > 0) {
            $this->removeContentBlockImageById($block, GafasHombrePromoContent::FIELD_IMAGE, $removePromoImageId);
        } elseif ($removePromoImage) {
            $this->clearContentBlockImages($block, GafasHombrePromoContent::FIELD_IMAGE);
        }

        return redirect()
            ->route('admin.gafas-hombre.index')
            ->with('status', 'Imagen promo de /gafas-hombre actualizada.');
    }

    public function create()
    {
        return view('admin.gafas_hombre.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'incluye' => ['nullable', 'string', 'max:255'],
            'categoria' => ['required', 'in:male,female,unisex,ninos,ninas,descanso'],
            'material_montura' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'array', 'required_with:uploaded_image'],
            'color.*' => ['nullable', 'string', 'max:80'],
            'recomendado_para' => ['nullable', 'string', 'max:255'],
            'ancho_total_montura' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }
                $parsed = $this->parseMeasure((string) $value);
                if ($parsed === null) {
                    $fail('El ancho total de la montura debe ser un número (cm). Ej: 13.9');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El ancho total de la montura no puede ser negativo.');
                }
            }],
            'ancho_lente' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }
                $parsed = $this->parseMeasure((string) $value);
                if ($parsed === null) {
                    $fail('El ancho del lente debe ser un número (cm). Ej: 5.0');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El ancho del lente no puede ser negativo.');
                }
            }],
            'alto_lente' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }
                $parsed = $this->parseMeasure((string) $value);
                if ($parsed === null) {
                    $fail('El alto del lente debe ser un número (cm). Ej: 4.5');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El alto del lente no puede ser negativo.');
                }
            }],
            'puente' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }
                $parsed = $this->parseMeasure((string) $value);
                if ($parsed === null) {
                    $fail('El puente debe ser un número (cm). Ej: 1.9');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El puente no puede ser negativo.');
                }
            }],
            'largo_patillas' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }
                $parsed = $this->parseMeasure((string) $value);
                if ($parsed === null) {
                    $fail('El largo de patillas debe ser un número (cm). Ej: 14.2');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El largo de patillas no puede ser negativo.');
                }
            }],
            'precio' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                $parsed = $this->parseMoney((string) $value);
                if ($parsed === null) {
                    $fail('El precio no tiene un formato válido. Ej: 50.000');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El precio no puede ser negativo.');
                }
            }],
            'precio_oferta' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }
                $parsed = $this->parseMoney((string) $value);
                if ($parsed === null) {
                    $fail('El precio oferta no tiene un formato válido. Ej: 50.000');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El precio oferta no puede ser negativo.');
                }
            }],
            'clip_on_compatible' => ['nullable', 'in:0,1'],
            'progresivos' => ['nullable', 'in:0,1'],
            'poly' => ['nullable', 'in:0,1'],
            'tipo_formula' => ['nullable', 'string', 'max:255'],
            'existencias' => ['nullable', 'integer', 'min:0'],
            'esta_activo' => ['nullable', 'boolean'],
            'imagen_url' => ['nullable', 'url', 'max:2048'],
            'imagen_url_2' => ['nullable', 'url', 'max:2048'],
            'imagen_url_2' => ['nullable', 'url', 'max:2048'],
            'uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'uploaded_color_images' => ['nullable', 'array', 'max:20'],
            'uploaded_color_images.*' => ['nullable', 'file', 'image', 'max:51200'],
            'uploaded_color_images_color' => ['nullable', 'array'],
            'uploaded_color_images_color.*' => ['nullable', 'array'],
            'uploaded_color_images_color.*.*' => ['nullable', 'string', 'max:80'],
            'existing_color_variants' => ['nullable', 'array'],
            'existing_color_variants.*.name' => ['nullable', 'string', 'max:80'],
            'existing_color_variants.*.images' => ['nullable', 'array'],
            'existing_color_variants.*.images.*' => ['nullable', 'string'],
            'existing_camera_variants' => ['nullable', 'array'],
            'existing_camera_variants.*.name' => ['nullable', 'string', 'max:80'],
            'existing_camera_variants.*.images' => ['nullable', 'array'],
            'existing_camera_variants.*.images.*' => ['nullable', 'string'],
            'existing_camera_variants.*.delete_images' => ['nullable', 'array'],
            'existing_camera_variants.*.delete_images.*' => ['nullable', 'string'],
            'imagenes' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }

                $urls = $this->parseGalleryUrls((string) $value);
                if (count($urls) > 12) {
                    $fail('Puedes agregar máximo 12 imágenes adicionales.');
                    return;
                }

                foreach ($urls as $url) {
                    if (strlen($url) > 2048) {
                        $fail('Cada URL de imagen adicional debe tener máximo 2048 caracteres.');
                        return;
                    }
                    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                        $fail('Las imágenes adicionales deben ser URLs válidas (una por línea).');
                        return;
                    }
                }
            }],
            'image_pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'image_pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'image_zoom' => ['nullable', 'numeric', 'min:0.5', 'max:1'],
        ]);

        $slug = $this->makeUniqueSlug($validated['nombre']);

        $meta = [];
        if (array_key_exists('imagen_url', $validated) && $validated['imagen_url']) {
            $meta['imagen_url'] = $validated['imagen_url'];
            $meta['imagen_url_manual'] = $validated['imagen_url'];
            $meta['imagen_alt'] = $validated['nombre'];
            unset($meta['uploaded_imagen_name'], $meta['uploaded_imagen_size'], $meta['uploaded_imagen_mime']);

            $zoom = array_key_exists('image_zoom', $validated) ? (float) $validated['image_zoom'] : self::IMG_MID_ZOOM;
            $zoom = max(0.5, min(1, $zoom));
            $meta['image_pos_x'] = array_key_exists('image_pos_x', $validated) ? (float) $validated['image_pos_x'] : 50;
            $meta['image_pos_y'] = array_key_exists('image_pos_y', $validated) ? (float) $validated['image_pos_y'] : 50;
            $meta['image_zoom'] = $zoom;
        }

        $uploadedImage = $request->file('uploaded_image');
        if ($uploadedImage && $uploadedImage->isValid()) {
            $storedPath = $this->storeUploadedImage($uploadedImage);
            if ($storedPath !== null) {
                $manualUrl = (string) ($validated['imagen_url'] ?? '');
                if ($manualUrl !== '') {
                    $meta['imagen_url_manual'] = $manualUrl;
                }

                $meta['imagen_url'] = $storedPath;
                $meta['imagen_alt'] = $validated['nombre'];
                $meta['uploaded_imagen_name'] = $uploadedImage->getClientOriginalName() ?: null;

                $zoom = array_key_exists('image_zoom', $validated) ? (float) $validated['image_zoom'] : self::IMG_MID_ZOOM;
                $zoom = max(0.5, min(1, $zoom));
                $meta['image_pos_x'] = array_key_exists('image_pos_x', $validated) ? (float) $validated['image_pos_x'] : 50;
                $meta['image_pos_y'] = array_key_exists('image_pos_y', $validated) ? (float) $validated['image_pos_y'] : 50;
                $meta['image_zoom'] = $zoom;
            }
        }

        if (array_key_exists('imagen_url_2', $validated) && $validated['imagen_url_2']) {
            $meta['imagen_url_2'] = $validated['imagen_url_2'];
        }

        if (array_key_exists('imagenes', $validated)) {
            $extra = $this->parseGalleryUrls($validated['imagenes'] ?? null, $meta['imagen_url'] ?? null);
            if ($extra) {
                $meta['imagenes'] = $extra;
            }
        }

        $uploadedColorVariants = $this->buildUploadedColorVariants(
            $request,
            true,
            'uploaded_color_variant_images',
            'uploaded_color_variant_images_color',
            'uploaded_color_variant_images_order'
        );
        if ($uploadedColorVariants) {
            $meta['color_variants'] = $uploadedColorVariants;
        }

        $uploadedCameraVariants = $this->buildUploadedColorVariants($request, false, 'uploaded_color_images', 'uploaded_color_images_color', 'uploaded_color_images_order', true);
        if ($uploadedCameraVariants !== []) {
            $meta['camera_color_variants'] = $uploadedCameraVariants;
        }

        $precio = $this->parseMoney($validated['precio']);
        $precioOferta = array_key_exists('precio_oferta', $validated) && $validated['precio_oferta'] !== null && $validated['precio_oferta'] !== ''
            ? $this->parseMoney($validated['precio_oferta'])
            : null;

        $medidas = [
            'ancho_total_montura_cm' => $this->parseMeasure($validated['ancho_total_montura'] ?? null),
            'ancho_lente_cm' => $this->parseMeasure($validated['ancho_lente'] ?? null),
            'alto_lente_cm' => $this->parseMeasure($validated['alto_lente'] ?? null),
            'puente_cm' => $this->parseMeasure($validated['puente'] ?? null),
            'largo_patillas_cm' => $this->parseMeasure($validated['largo_patillas'] ?? null),
        ];
        $medidas = array_filter($medidas, static fn ($v) => $v !== null);

        $caracteristicas = [];
        if ($medidas) {
            $caracteristicas['medidas'] = $medidas;
        }
        $caracteristicas['recomendado_para'] = trim((string) ($validated['recomendado_para'] ?? 'No especificado')) !== ''
            ? trim((string) ($validated['recomendado_para'] ?? 'No especificado'))
            : 'No especificado';
        $caracteristicas['incluye'] = trim((string) ($validated['incluye'] ?? 'No especificado')) !== ''
            ? trim((string) ($validated['incluye'] ?? 'No especificado'))
            : 'No especificado';
        $caracteristicas['clip_on_compatible'] = (bool) ((int) ($validated['clip_on_compatible'] ?? '0'));
        $caracteristicas['progresivos'] = (bool) ((int) ($validated['progresivos'] ?? '0'));
        $caracteristicas['poly'] = (bool) ((int) ($validated['poly'] ?? '0'));
        $caracteristicas['tipo_formula'] = trim((string) ($validated['tipo_formula'] ?? 'Bajas')) !== ''
            ? trim((string) ($validated['tipo_formula'] ?? 'Bajas'))
            : 'Bajas';

        $resolvedColorList = $this->normalizeSelectedColorNames($validated['color'] ?? []);
        $resolvedColor = (string) ($resolvedColorList[0] ?? '');
        if ($resolvedColor === '' && !empty($meta['color_variants'][0]['name'])) {
            $resolvedColor = (string) $meta['color_variants'][0]['name'];
        }
        $colorStockMap = $this->normalizeColorStockMap($request->input('color_stock', []));
        if ($resolvedColor === '' && $colorStockMap !== []) {
            $resolvedColor = (string) array_key_first($colorStockMap);
        }
        if ($resolvedColor !== '') {
            $meta['color'] = $resolvedColor;
        } else {
            unset($meta['color']);
        }

        if ($resolvedColorList !== []) {
            $meta['color_list'] = $resolvedColorList;
        } else {
            unset($meta['color_list']);
        }

        $stockValidationMessage = $this->validateRelevantColorStock($resolvedColor, $request, $colorStockMap);
        if ($stockValidationMessage !== null) {
            return back()->withInput()->withErrors(['color_stock' => $stockValidationMessage]);
        }

        if ($colorStockMap !== []) {
            $meta['color_stock'] = $colorStockMap;
            if (is_array($meta['color_variants'] ?? null)) {
                $meta['color_variants'] = $this->applyColorStockToVariants($meta['color_variants'], $colorStockMap);
            }
        } else {
            unset($meta['color_stock']);
        }

        $resolvedExistencias = $this->resolveExistenciasValue($colorStockMap, $validated['existencias'] ?? null);

        Producto::query()->create([
            'nombre' => $validated['nombre'],
            'slug' => $slug,
            'tipo' => self::TIPO,
            'genero_objetivo' => $validated['categoria'],
            'material_montura' => $validated['material_montura'] ?? null,
            'color' => $resolvedColor !== '' ? $resolvedColor : null,
            'descripcion' => $validated['descripcion'] ?? null,
            'caracteristicas' => $caracteristicas ?: null,
            'precio' => $precio,
            'precio_oferta' => $precioOferta,
            'moneda' => 'COP',
            'existencias' => $resolvedExistencias,
            'esta_activo' => (bool) ($validated['esta_activo'] ?? true),
            'meta' => $meta ?: null,
        ]);

        return redirect()
            ->route('admin.gafas-hombre.index')
            ->with('status', 'Gafa para hombre agregada.');
    }

    public function edit(Producto $producto)
    {
        $this->assertIsGafaHombre($producto);

        return view('admin.gafas_hombre.edit', [
            'producto' => $producto,
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $this->assertIsGafaHombre($producto);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'incluye' => ['nullable', 'string', 'max:255'],
            'categoria' => ['required', 'in:male,female,unisex,ninos,ninas,descanso'],
            'material_montura' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'array', 'required_with:uploaded_image'],
            'color.*' => ['nullable', 'string', 'max:80'],
            'recomendado_para' => ['nullable', 'string', 'max:255'],
            'ancho_total_montura' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }
                $parsed = $this->parseMeasure((string) $value);
                if ($parsed === null) {
                    $fail('El ancho total de la montura debe ser un número (cm). Ej: 13.9');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El ancho total de la montura no puede ser negativo.');
                }
            }],
            'ancho_lente' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }
                $parsed = $this->parseMeasure((string) $value);
                if ($parsed === null) {
                    $fail('El ancho del lente debe ser un número (cm). Ej: 5.0');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El ancho del lente no puede ser negativo.');
                }
            }],
            'alto_lente' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }
                $parsed = $this->parseMeasure((string) $value);
                if ($parsed === null) {
                    $fail('El alto del lente debe ser un número (cm). Ej: 4.5');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El alto del lente no puede ser negativo.');
                }
            }],
            'puente' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }
                $parsed = $this->parseMeasure((string) $value);
                if ($parsed === null) {
                    $fail('El puente debe ser un número (cm). Ej: 1.9');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El puente no puede ser negativo.');
                }
            }],
            'largo_patillas' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }
                $parsed = $this->parseMeasure((string) $value);
                if ($parsed === null) {
                    $fail('El largo de patillas debe ser un número (cm). Ej: 14.2');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El largo de patillas no puede ser negativo.');
                }
            }],
            'precio' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                $parsed = $this->parseMoney((string) $value);
                if ($parsed === null) {
                    $fail('El precio no tiene un formato válido. Ej: 50.000');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El precio no puede ser negativo.');
                }
            }],
            'precio_oferta' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }
                $parsed = $this->parseMoney((string) $value);
                if ($parsed === null) {
                    $fail('El precio oferta no tiene un formato válido. Ej: 50.000');
                    return;
                }
                if ($parsed < 0) {
                    $fail('El precio oferta no puede ser negativo.');
                }
            }],
            'clip_on_compatible' => ['nullable', 'in:0,1'],
            'progresivos' => ['nullable', 'in:0,1'],
            'poly' => ['nullable', 'in:0,1'],
            'tipo_formula' => ['nullable', 'string', 'max:255'],
            'existencias' => ['nullable', 'integer', 'min:0'],
            'esta_activo' => ['nullable', 'boolean'],
            'imagen_url' => ['nullable', 'url', 'max:2048'],
            'uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'uploaded_color_images' => ['nullable', 'array', 'max:20'],
            'uploaded_color_images.*' => ['nullable', 'file', 'image', 'max:51200'],
            'uploaded_color_images_color' => ['nullable', 'array'],
            'uploaded_color_images_color.*' => ['nullable', 'array'],
            'uploaded_color_images_color.*.*' => ['nullable', 'string', 'max:80'],
            'existing_color_variants' => ['nullable', 'array'],
            'existing_color_variants.*.name' => ['nullable', 'string', 'max:80'],
            'existing_color_variants.*.images' => ['nullable', 'array'],
            'existing_color_variants.*.images.*' => ['nullable', 'string'],
            'existing_camera_variants' => ['nullable', 'array'],
            'existing_camera_variants.*.name' => ['nullable', 'string', 'max:80'],
            'existing_camera_variants.*.images' => ['nullable', 'array'],
            'existing_camera_variants.*.images.*' => ['nullable', 'string'],
            'existing_camera_variants.*.delete_images' => ['nullable', 'array'],
            'existing_camera_variants.*.delete_images.*' => ['nullable', 'string'],
            'imagenes' => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === null || (string) $value === '') {
                    return;
                }

                $urls = $this->parseGalleryUrls((string) $value);
                if (count($urls) > 12) {
                    $fail('Puedes agregar máximo 12 imágenes adicionales.');
                    return;
                }

                foreach ($urls as $url) {
                    if (strlen($url) > 2048) {
                        $fail('Cada URL de imagen adicional debe tener máximo 2048 caracteres.');
                        return;
                    }
                    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                        $fail('Las imágenes adicionales deben ser URLs válidas (una por línea).');
                        return;
                    }
                }
            }],
            'eliminar_imagen' => ['nullable', 'boolean'],
            'image_pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'image_pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'image_zoom' => ['nullable', 'numeric', 'min:0.5', 'max:1'],
        ]);

        $slug = $this->makeUniqueSlug($validated['nombre'], $producto->id);
        $meta = is_array($producto->meta) ? $producto->meta : [];
        $caracteristicas = is_array($producto->caracteristicas) ? $producto->caracteristicas : [];

        $precio = $this->parseMoney($validated['precio']);
        $precioOferta = array_key_exists('precio_oferta', $validated) && $validated['precio_oferta'] !== null && $validated['precio_oferta'] !== ''
            ? $this->parseMoney($validated['precio_oferta'])
            : null;

        if ((bool) ($validated['eliminar_imagen'] ?? false)) {
            $this->deleteStoredImage($meta['imagen_url'] ?? null);
            unset($meta['imagen_url'], $meta['imagen_url_manual'], $meta['imagen_alt'], $meta['image_pos_x'], $meta['image_pos_y'], $meta['image_zoom'], $meta['uploaded_imagen_name'], $meta['uploaded_imagen_size'], $meta['uploaded_imagen_mime']);
        }

        if (array_key_exists('imagen_url', $validated)) {
            $url = trim((string) ($validated['imagen_url'] ?? ''));

            if ($url !== '') {
                $meta['imagen_url'] = $url;
                $meta['imagen_url_manual'] = $url;
                $meta['imagen_alt'] = $validated['nombre'];
                unset($meta['uploaded_imagen_name'], $meta['uploaded_imagen_size'], $meta['uploaded_imagen_mime']);

                $zoom = array_key_exists('image_zoom', $validated)
                    ? (float) $validated['image_zoom']
                    : (float) ($meta['image_zoom'] ?? self::IMG_MID_ZOOM);
                $zoom = max(0.5, min(1, $zoom));

                $meta['image_pos_x'] = array_key_exists('image_pos_x', $validated)
                    ? (float) $validated['image_pos_x']
                    : (float) ($meta['image_pos_x'] ?? 50);
                $meta['image_pos_y'] = array_key_exists('image_pos_y', $validated)
                    ? (float) $validated['image_pos_y']
                    : (float) ($meta['image_pos_y'] ?? 50);
                $meta['image_zoom'] = $zoom;
            }
        }

        $uploadedImage = $request->file('uploaded_image');
        if ($uploadedImage && $uploadedImage->isValid()) {
            $storedPath = $this->storeUploadedImage($uploadedImage);
            if ($storedPath !== null) {
                $this->deleteStoredImage($meta['imagen_url'] ?? null);
                $manualUrl = (string) ($validated['imagen_url'] ?? '');
                if ($manualUrl !== '') {
                    $meta['imagen_url_manual'] = $manualUrl;
                }

                $meta['imagen_url'] = $storedPath;
                $meta['imagen_alt'] = $validated['nombre'];
                $meta['uploaded_imagen_name'] = $uploadedImage->getClientOriginalName() ?: null;

                $zoom = array_key_exists('image_zoom', $validated)
                    ? (float) $validated['image_zoom']
                    : (float) ($meta['image_zoom'] ?? self::IMG_MID_ZOOM);
                $zoom = max(0.5, min(1, $zoom));

                $meta['image_pos_x'] = array_key_exists('image_pos_x', $validated)
                    ? (float) $validated['image_pos_x']
                    : (float) ($meta['image_pos_x'] ?? 50);
                $meta['image_pos_y'] = array_key_exists('image_pos_y', $validated)
                    ? (float) $validated['image_pos_y']
                    : (float) ($meta['image_pos_y'] ?? 50);
                $meta['image_zoom'] = $zoom;
            }
        }

        if (array_key_exists('imagenes', $validated)) {
            $extra = $this->parseGalleryUrls($validated['imagenes'] ?? null, $meta['imagen_url'] ?? null);
            if ($extra) {
                $meta['imagenes'] = $extra;
            } else {
                unset($meta['imagenes']);
            }
        }

        // Fusionar imágenes existentes y nuevas por color
        $existingColorVariants = [];
        if ($request->has('existing_color_variants')) {
            foreach ($request->input('existing_color_variants') as $variantIndex => $variant) {
                $name = $variant['name'] ?? '';
                $images = $variant['images'] ?? [];
                $deleteImages = $variant['delete_images'] ?? [];
                // Ordenar imágenes según images_order
                $imagesOrder = $variant['images_order'] ?? [];
                $orderedImages = $images;
                if (is_array($imagesOrder) && count($imagesOrder) === count($images)) {
                    $imgWithOrder = [];
                    foreach ($images as $idx => $img) {
                        $order = isset($imagesOrder[$idx]) ? (int)$imagesOrder[$idx] : ($idx + 1);
                        $imgWithOrder[] = ['img' => $img, 'order' => $order];
                    }
                    usort($imgWithOrder, function($a, $b) {
                        return $a['order'] <=> $b['order'];
                    });
                    $orderedImages = array_column($imgWithOrder, 'img');
                }
                $orderedImages = array_filter($orderedImages, function($img) use ($deleteImages) {
                    return !in_array($img, $deleteImages, true);
                });
                $names = $this->normalizeSelectedColorNames($name);
                if ($names === []) {
                    $names = ['Gris'];
                }
                usort($names, static fn (string $left, string $right): int => strcasecmp($left, $right));

                if (!empty($orderedImages)) {
                    $existingColorVariants[] = [
                        'name' => implode(', ', $names),
                        'images' => array_values($orderedImages),
                        'stock' => isset($variant['stock']) ? (int) $variant['stock'] : null,
                    ];
                }
            }
        }

        $uploadedColorVariants = $this->buildUploadedColorVariants(
            $request,
            true,
            'uploaded_color_variant_images',
            'uploaded_color_variant_images_color',
            'uploaded_color_variant_images_order'
        );
        $allVariants = array_merge($existingColorVariants, $uploadedColorVariants ?: []);
        $groupedVariants = [];
        foreach ($allVariants as $variant) {
            $variantNames = $this->normalizeSelectedColorNames($variant['name'] ?? '');
            if ($variantNames === []) {
                $variantNames = ['Gris'];
            }
            usort($variantNames, static fn (string $left, string $right): int => strcasecmp($left, $right));

            $variantLabel = implode(', ', $variantNames);
            $groupKey = implode('|', array_map(fn (string $colorName) => $this->normalizeColorKey($colorName), $variantNames));
            if ($groupKey === '') {
                $groupKey = 'gris';
                $variantLabel = 'Gris';
            }

            if (!isset($groupedVariants[$groupKey])) {
                $groupedVariants[$groupKey] = [
                    'name' => $variantLabel,
                    'images' => [],
                    'stock' => $variant['stock'] ?? null,
                ];
            }
            $groupedVariants[$groupKey]['images'] = array_values(array_unique(array_merge(
                $groupedVariants[$groupKey]['images'],
                $variant['images']
            )));
            if (isset($variant['stock'])) {
                $groupedVariants[$groupKey]['stock'] = $variant['stock'];
            }
        }
        $meta['color_variants'] = array_values($groupedVariants);

        $hadExistingCameraPayload = $request->has('existing_camera_variants');
        $existingCameraVariants = [];
        if ($hadExistingCameraPayload) {
            foreach ($request->input('existing_camera_variants', []) as $variant) {
                if (!is_array($variant)) {
                    continue;
                }

                $name = (string) ($variant['name'] ?? '');
                $images = is_array($variant['images'] ?? null) ? $variant['images'] : [];
                $deleteImages = is_array($variant['delete_images'] ?? null) ? $variant['delete_images'] : [];

                $images = array_values(array_filter(array_map(
                    static fn ($img): string => trim((string) $img),
                    $images
                ), static fn (string $img): bool => $img !== '' && !in_array($img, $deleteImages, true)));

                if ($images === []) {
                    continue;
                }

                $names = $this->normalizeSelectedColorNames($name);
                if ($names === []) {
                    $names = ['Gris'];
                }
                usort($names, static fn (string $left, string $right): int => strcasecmp($left, $right));

                $existingCameraVariants[] = [
                    'name' => implode(', ', $names),
                    'images' => $images,
                ];
            }
        }

        $uploadedCameraVariants = $this->buildUploadedColorVariants($request, false, 'uploaded_color_images', 'uploaded_color_images_color', 'uploaded_color_images_order', true);
        $allCameraVariants = array_merge($existingCameraVariants, $uploadedCameraVariants ?: []);

        if ($allCameraVariants !== []) {
            $groupedCameraVariants = [];
            foreach ($allCameraVariants as $variant) {
                if (!is_array($variant)) {
                    continue;
                }

                $variantNames = $this->normalizeSelectedColorNames($variant['name'] ?? '');
                if ($variantNames === []) {
                    $variantNames = ['Gris'];
                }
                usort($variantNames, static fn (string $left, string $right): int => strcasecmp($left, $right));

                $variantLabel = implode(', ', $variantNames);
                $groupKey = implode('|', array_map(fn (string $colorName) => $this->normalizeColorKey($colorName), $variantNames));
                if ($groupKey === '') {
                    $groupKey = 'gris';
                    $variantLabel = 'Gris';
                }

                if (!isset($groupedCameraVariants[$groupKey])) {
                    $groupedCameraVariants[$groupKey] = [
                        'name' => $variantLabel,
                        'images' => [],
                    ];
                }

                $groupedCameraVariants[$groupKey]['images'] = array_values(array_unique(array_merge(
                    $groupedCameraVariants[$groupKey]['images'],
                    array_values(array_filter(array_map(
                        static fn ($img): string => trim((string) $img),
                        is_array($variant['images'] ?? null) ? $variant['images'] : []
                    ), static fn (string $img): bool => $img !== ''))
                )));

                if (trim((string) ($groupedCameraVariants[$groupKey]['image'] ?? '')) === '' && trim((string) ($variant['image'] ?? '')) !== '') {
                    $groupedCameraVariants[$groupKey]['image'] = trim((string) $variant['image']);
                }
            }

            $meta['camera_color_variants'] = array_values(array_filter(
                $groupedCameraVariants,
                static fn (array $variant): bool => is_array($variant['images'] ?? null) && $variant['images'] !== []
            ));
        } elseif ($hadExistingCameraPayload) {
            unset($meta['camera_color_variants']);
        }

        $resolvedColorList = $this->normalizeSelectedColorNames($validated['color'] ?? []);
        $resolvedColor = (string) ($resolvedColorList[0] ?? '');
        if ($resolvedColor === '') {
            $resolvedColor = trim((string) ($producto->color ?? ($meta['color'] ?? '')));
        }
        if ($resolvedColor === '' && !empty($meta['color_variants'][0]['name'])) {
            $resolvedColor = (string) $meta['color_variants'][0]['name'];
        }
        $colorStockMap = $this->normalizeColorStockMap($request->input('color_stock', []));
        if ($resolvedColor === '' && $colorStockMap !== []) {
            $resolvedColor = (string) array_key_first($colorStockMap);
        }
        if ($resolvedColor !== '') {
            $meta['color'] = $resolvedColor;
        } else {
            unset($meta['color']);
        }

        if ($resolvedColorList !== []) {
            $meta['color_list'] = $resolvedColorList;
        } else {
            unset($meta['color_list']);
        }

        $stockValidationMessage = $this->validateRelevantColorStock($resolvedColor !== '' ? $resolvedColor : ($producto->color ?? null), $request, $colorStockMap);
        if ($stockValidationMessage !== null) {
            return back()->withInput()->withErrors(['color_stock' => $stockValidationMessage]);
        }

        if ($colorStockMap !== []) {
            $meta['color_stock'] = $colorStockMap;
            if (is_array($meta['color_variants'] ?? null)) {
                $meta['color_variants'] = $this->applyColorStockToVariants($meta['color_variants'], $colorStockMap);
            }
        } else {
            unset($meta['color_stock']);
        }

        $resolvedExistencias = $this->resolveExistenciasValue($colorStockMap, $validated['existencias'] ?? $producto->existencias);

        $medidas = [
            'ancho_total_montura_cm' => $this->parseMeasure($validated['ancho_total_montura'] ?? null),
            'ancho_lente_cm' => $this->parseMeasure($validated['ancho_lente'] ?? null),
            'alto_lente_cm' => $this->parseMeasure($validated['alto_lente'] ?? null),
            'puente_cm' => $this->parseMeasure($validated['puente'] ?? null),
            'largo_patillas_cm' => $this->parseMeasure($validated['largo_patillas'] ?? null),
        ];
        $medidas = array_filter($medidas, static fn ($v) => $v !== null);
        if ($medidas) {
            $caracteristicas['medidas'] = $medidas;
        } else {
            unset($caracteristicas['medidas']);
        }
        $caracteristicas['recomendado_para'] = trim((string) ($validated['recomendado_para'] ?? 'No especificado')) !== ''
            ? trim((string) ($validated['recomendado_para'] ?? 'No especificado'))
            : 'No especificado';
        $caracteristicas['incluye'] = trim((string) ($validated['incluye'] ?? 'No especificado')) !== ''
            ? trim((string) ($validated['incluye'] ?? 'No especificado'))
            : 'No especificado';
        $caracteristicas['clip_on_compatible'] = (bool) ((int) ($validated['clip_on_compatible'] ?? '0'));
        $caracteristicas['progresivos'] = (bool) ((int) ($validated['progresivos'] ?? '0'));
        $caracteristicas['poly'] = (bool) ((int) ($validated['poly'] ?? '0'));
        $caracteristicas['tipo_formula'] = trim((string) ($validated['tipo_formula'] ?? 'Bajas')) !== ''
            ? trim((string) ($validated['tipo_formula'] ?? 'Bajas'))
            : 'Bajas';

        $producto->update([
            'nombre' => $validated['nombre'],
            'slug' => $slug,
            'genero_objetivo' => $validated['categoria'],
            'material_montura' => $validated['material_montura'] ?? null,
            'color' => $resolvedColor !== '' ? $resolvedColor : null,
            'descripcion' => $validated['descripcion'] ?? null,
            'caracteristicas' => $caracteristicas ?: null,
            'precio' => $precio,
            'precio_oferta' => $precioOferta,
            'existencias' => $resolvedExistencias,
            'esta_activo' => (bool) ($validated['esta_activo'] ?? true),
            'meta' => $meta ?: null,
        ]);

        return redirect()
            ->route('admin.gafas-hombre.index')
            ->with('status', 'Gafa actualizada.');
    }

    public function destroy(Producto $producto)
    {
        $this->assertIsGafaHombre($producto);

        $this->deleteAllProductoImages(is_array($producto->meta) ? $producto->meta : []);
        $producto->delete();

        return redirect()
            ->route('admin.gafas-hombre.index')
            ->with('status', 'Gafa eliminada.');
    }

    public function showImportForm()
    {
        return $this->showExcelImportForm('admin.gafas_hombre.import');
    }

    public function importFromExcel(Request $request)
    {
        return $this->importFromExcelGeneric($request, self::TIPO, self::GENERO, 'admin.gafas-hombre.index');
    }

    public function downloadTemplate()
    {
        return $this->downloadExcelTemplate('Plantilla_Gafas_Hombre.xlsx');
    }

    private function assertIsGafaHombre(Producto $producto): void
    {
        abort_unless($producto->tipo === self::TIPO, 404);
    }
}
