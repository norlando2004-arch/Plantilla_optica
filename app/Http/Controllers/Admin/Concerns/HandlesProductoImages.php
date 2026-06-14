<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\BloqueContenido;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Producto;

/**
 * Lógica compartida de manejo de imágenes y helpers de producto
 * para los controladores del panel de administración.
 *
 * Las imágenes se guardan en storage/app/public/productos/
 * y se referencian como "/storage/productos/nombre-unico.ext".
 *
 * Las imágenes antiguas en base64 (data:image/...) se conservan
 * y se muestran sin error hasta que el admin las reemplace.
 */
trait HandlesProductoImages
{
    // ──────────────────────────────────────────────
    // Almacenamiento y borrado de imágenes en disco
    // ──────────────────────────────────────────────

    /**
     * Guarda un archivo subido en storage/public/productos y
     * devuelve la URL relativa "/storage/productos/xxx.jpg".
     * Retorna null si el archivo no es válido.
     */
    private function storeUploadedImage(mixed $file, bool $removeWhiteBackground = false): ?string
    {
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            return null;
        }

        $path = $file->store('productos', 'public');

        if ($path === false || $path === '') {
            return null;
        }

        if ($removeWhiteBackground) {
            $path = $this->makeStoredImageBackgroundTransparent($path);
        }

        return '/storage/' . $path;
    }

    private function makeStoredImageBackgroundTransparent(string $relativePath): string
    {
        $relativePath = trim($relativePath);
        if ($relativePath === '' || ! Storage::disk('public')->exists($relativePath)) {
            return $relativePath;
        }

        $sourcePath = Storage::disk('public')->path($relativePath);
        $targetRelativePath = preg_replace('/\.[^.]+$/', '', $relativePath) . '-transparent.png';
        $targetPath = Storage::disk('public')->path($targetRelativePath);

        $command = sprintf(
            'magick %s -alpha set -fuzz 18%% -transparent white PNG32:%s',
            escapeshellarg($sourcePath),
            escapeshellarg($targetPath)
        );

        @exec($command, $output, $exitCode);

        if ($exitCode !== 0 || ! is_file($targetPath) || filesize($targetPath) === 0) {
            return $relativePath;
        }

        Storage::disk('public')->delete($relativePath);

        return $targetRelativePath;
    }

    /**
     * Elimina el archivo físico si la URL apunta a un archivo
     * almacenado en storage (empieza por /storage/).
     * Las URLs externas y las base64 se ignoran silenciosamente.
     */
    private function deleteStoredImage(?string $url): void
    {
        if ($url === null || $url === '') {
            return;
        }

        // No tocar URLs externas ni imágenes base64 antiguas
        if (str_starts_with($url, 'data:') || str_starts_with($url, 'http')) {
            return;
        }

        if (! str_starts_with($url, '/storage/')) {
            return;
        }

        // "/storage/productos/abc.jpg" → "productos/abc.jpg"
        $relativePath = ltrim(substr($url, strlen('/storage')), '/');
        Storage::disk('public')->delete($relativePath);
    }

    /**
     * Elimina todas las imágenes almacenadas en disco asociadas a
     * un producto (imagen principal + variantes de color).
     * Llama a esto desde destroy() antes de borrar el modelo.
     */
    private function deleteAllProductoImages(array $meta): void
    {
        $this->deleteStoredImage($meta['imagen_url'] ?? null);

        if (is_array($meta['color_variants'] ?? null)) {
            foreach ($meta['color_variants'] as $variant) {
                if (! is_array($variant)) {
                    continue;
                }

                foreach ((array) ($variant['images'] ?? []) as $imgUrl) {
                    $this->deleteStoredImage((string) $imgUrl);
                }

                // Campo 'image' como respaldo por si 'images' no existe
                $this->deleteStoredImage((string) ($variant['image'] ?? ''));
            }
        }
    }

    // ──────────────────────────────────────────────
    // Construcción de variantes de color subidas
    // ──────────────────────────────────────────────

    /**
     * Construye el array color_variants a partir de los archivos
     * subidos en el formulario (uploaded_image + uploaded_color_images).
     * Las imágenes se guardan en disco; se retorna la URL relativa.
     */
    private function buildUploadedColorVariants(
        Request $request,
        bool $includePrimaryImage = true,
        string $filesField = 'uploaded_color_images',
        string $colorNamesField = 'uploaded_color_images_color',
        string $imageOrdersField = 'uploaded_color_images_order',
        bool $removeWhiteBackground = false
    ): array
    {
        $files      = $request->file($filesField, []);
        $colorNames = $request->input($colorNamesField, []);
        $imageOrders = $request->input($imageOrdersField, []);
        $colorStockMap = $this->normalizeColorStockMap($request->input('color_stock', []));
        $variants   = [];

        $appendVariantImage = function (array $names, string $image, ?int $order = null) use (&$variants, $colorStockMap): void {
            $names = $this->normalizeSelectedColorNames($names);
            if ($names === []) {
                $names = ['Gris'];
            }

            usort($names, static fn (string $left, string $right): int => strcasecmp($left, $right));

            $name  = implode(', ', $names);
            $image = trim($image);
            $order = $order !== null ? max(1, $order) : null;

            if ($image === '') {
                return;
            }

            $variantKey = $this->normalizeColorGroupKey($names);
            if ($variantKey === '') {
                $variantKey = $this->normalizeColorGroupKey(['Gris']);
                $names = ['Gris'];
                $name = 'Gris';
            }

            if (! array_key_exists($variantKey, $variants)) {
                $variants[$variantKey] = [
                    'name'         => $name,
                    'hex'          => $this->frameColorHex($names[0] ?? $name) ?? '#cec9bc',
                    'image'        => $image,
                    'images'       => [$image],
                    'image_orders' => [$order ?? 999999],
                    'stock'        => $this->colorStockValueForName($colorStockMap, $name),
                ];

                return;
            }

            if (! in_array($image, $variants[$variantKey]['images'], true)) {
                $variants[$variantKey]['images'][] = $image;
                $variants[$variantKey]['image_orders'][] = $order ?? 999999;
            }

            if (trim((string) ($variants[$variantKey]['image'] ?? '')) === '') {
                $variants[$variantKey]['image'] = $image;
            }

            if (($variants[$variantKey]['stock'] ?? null) === null) {
                $variants[$variantKey]['stock'] = $this->colorStockValueForName($colorStockMap, $name);
            }
        };

        // Imagen principal también entra como variante del color primario
        $primaryUploadedImage = $request->file('uploaded_image');
        if ($includePrimaryImage && $primaryUploadedImage && $primaryUploadedImage->isValid()) {
            $primaryPath = $this->storeUploadedImage($primaryUploadedImage);
            if ($primaryPath !== null) {
                $primaryColors = $this->normalizeSelectedColorNames($request->input('color', ['Gris']));
                if ($primaryColors === []) {
                    $primaryColors = ['Gris'];
                }

                $appendVariantImage($primaryColors, $primaryPath);
            }
        }

        if (is_array($files)) {
            foreach ($files as $index => $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }

                $path = $this->storeUploadedImage($file, $removeWhiteBackground);
                if ($path === null) {
                    continue;
                }

                $order = isset($imageOrders[$index]) ? (int) $imageOrders[$index] : null;
                $selectedColors = $this->normalizeSelectedColorNames($colorNames[$index] ?? ['Gris']);
                if ($selectedColors === []) {
                    $selectedColors = ['Gris'];
                }

                $appendVariantImage($selectedColors, $path, $order);
            }
        }

        foreach ($variants as &$variant) {
            $images = array_values((array) ($variant['images'] ?? []));
            $orders = array_values((array) ($variant['image_orders'] ?? []));

            if ($images !== []) {
                $combined = [];

                foreach ($images as $index => $image) {
                    $combined[] = [
                        'image' => $image,
                        'order' => isset($orders[$index]) ? (int) $orders[$index] : 999999,
                        'index' => $index,
                    ];
                }

                usort($combined, static function (array $left, array $right): int {
                    if ($left['order'] === $right['order']) {
                        return $left['index'] <=> $right['index'];
                    }

                    return $left['order'] <=> $right['order'];
                });

                $variant['images'] = array_values(array_map(static fn (array $item) => $item['image'], $combined));
                $variant['image'] = $variant['images'][0] ?? $variant['image'] ?? null;
            }

            unset($variant['image_orders']);
        }
        unset($variant);

        return array_values($variants);
    }

    // ──────────────────────────────────────────────
    // Helpers de contenido (BloqueContenido / promo)
    // ──────────────────────────────────────────────

    private function clearContentBlockImages(BloqueContenido $block, string $fieldKey): void
    {
        $archivos = $block->archivos()->where('field_key', $fieldKey)->get();

        foreach ($archivos as $archivo) {
            if (filled($archivo->ruta_archivo)) {
                Storage::disk('public')->delete($archivo->ruta_archivo);
            }
        }

        $block->archivos()->where('field_key', $fieldKey)->delete();
    }

    private function removeContentBlockImageById(BloqueContenido $block, string $fieldKey, int $assetId): void
    {
        $archivo = $block->archivos()
            ->where('field_key', $fieldKey)
            ->where('id', $assetId)
            ->first();

        if (! $archivo) {
            return;
        }

        if (filled($archivo->ruta_archivo)) {
            Storage::disk('public')->delete($archivo->ruta_archivo);
        }

        $archivo->delete();
    }

    private function appendContentBlockImages(BloqueContenido $block, string $fieldKey, array $files): void
    {
        $nextOrder = (int) $block->archivos()
            ->where('field_key', $fieldKey)
            ->max('orden');

        foreach ($files as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $extension = match (strtolower((string) ($file->getMimeType() ?: ''))) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'image/svg+xml' => 'svg',
                default => $file->guessExtension() ?: 'bin',
            };

            $storedPath = 'config/' . Str::uuid() . '.' . $extension;
            Storage::disk('public')->put($storedPath, (string) file_get_contents($file->getRealPath() ?: $file->getPathname()));

            $nextOrder++;

            $block->archivos()->create([
                'field_key'        => $fieldKey,
                'orden'            => $nextOrder,
                'mime_type'        => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                'original_name'    => $file->getClientOriginalName() ?: null,
                'size_bytes'       => $file->getSize(),
                'contenido_base64' => null,
                'ruta_archivo'     => $storedPath,
            ]);
        }
    }

    // ──────────────────────────────────────────────
    // Helpers de variantes existentes (edit)
    // ──────────────────────────────────────────────

    private function normalizeExistingColorVariants(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $variants = [];

        foreach ($raw as $variant) {
            if (! is_array($variant)) {
                continue;
            }

            $stock = null;
            if (array_key_exists('stock', $variant) && $variant['stock'] !== null && $variant['stock'] !== '') {
                $stock = max(0, (int) $variant['stock']);
            }

            $names = $this->normalizeSelectedColorNames($variant['name'] ?? '');
            if ($names === []) {
                $names = ['Gris'];
            }

            usort($names, static fn (string $left, string $right): int => strcasecmp($left, $right));

            $images = [];
            foreach ((array) ($variant['images'] ?? []) as $image) {
                $image = trim((string) $image);
                if ($image !== '' && ! in_array($image, $images, true)) {
                    $images[] = $image;
                }
            }

            if ($images === []) {
                continue;
            }

            $variantKey = $this->normalizeColorGroupKey($names);
            if ($variantKey === '') {
                $variantKey = $this->normalizeColorGroupKey(['Gris']);
                $names = ['Gris'];
            }

            $label = implode(', ', $names);

            if (! array_key_exists($variantKey, $variants)) {
                $variants[$variantKey] = [
                    'name'   => $label,
                    'hex'    => $this->frameColorHex($names[0] ?? 'Gris') ?? '#cec9bc',
                    'image'  => $images[0],
                    'images' => $images,
                    'stock'  => $stock,
                ];

                continue;
            }

            foreach ($images as $image) {
                if (! in_array($image, $variants[$variantKey]['images'], true)) {
                    $variants[$variantKey]['images'][] = $image;
                }
            }

            if (trim((string) ($variants[$variantKey]['image'] ?? '')) === '') {
                $variants[$variantKey]['image'] = $images[0];
            }

            if ($stock !== null) {
                $variants[$variantKey]['stock'] = $stock;
            }
        }

        return array_values($variants);
    }

    // ──────────────────────────────────────────────
    // Colores
    // ──────────────────────────────────────────────

    private function frameColorHex(string $name): ?string
    {
        $map = [
            'Gris'         => '#cec9bc',
            'Rosa'         => '#e6c0c3',
            'Negro'        => '#2a2020',
            'Azul'         => '#b6d6e7',
            'Marron'       => '#6f4e37',
            'Carey'        => '#7b5a46',
            'Transparente' => '#e5e7eb',
            'Rojo'         => '#9f2b2b',
            'Verde'        => '#466b48',
            'Morado'       => '#6d4c8b',
            'Dorado'       => '#c6a75a',
            'Plateado'     => '#b5bcc9',
            'Nude'         => '#d7b39d',
            'Blanco'       => '#f5f5f4',
        ];

        return $map[$name] ?? null;
    }

    private function normalizeColorKey(?string $value): string
    {
        return Str::lower(trim(Str::ascii((string) $value)));
    }

    private function normalizeColorGroupKey(array $names): string
    {
        $keys = [];

        foreach ($names as $name) {
            $key = $this->normalizeColorKey((string) $name);
            if ($key === '' || in_array($key, $keys, true)) {
                continue;
            }

            $keys[] = $key;
        }

        sort($keys);

        return implode('|', $keys);
    }

    private function normalizeColorStockMap(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $map = [];
        foreach ($raw as $name => $stock) {
            $displayName = trim((string) $name);
            if ($displayName === '' || $stock === null || $stock === '') {
                continue;
            }

            $map[$displayName] = max(0, (int) $stock);
        }

        return $map;
    }

    private function colorStockValueForName(array $colorStockMap, ?string $name): ?int
    {
        $target = $this->normalizeColorKey($name);
        if ($target === '') {
            return null;
        }

        // Prioridad 1: coincidencia exacta de la clave completa.
        foreach ($colorStockMap as $displayName => $stock) {
            if ($this->normalizeColorKey((string) $displayName) === $target) {
                return max(0, (int) $stock);
            }
        }

        // Prioridad 1b: coincidencia de grupo normalizado — maneja diferente orden de colores
        // entre el JS (orden colorOptions) y el PHP (orden alfabético).
        // Ambos lados se separan por coma, se normalizan individualmente y se ordenan antes de comparar.
        $targetParts = array_values(array_filter(
            array_map(
                fn (string $v) => $this->normalizeColorKey($v),
                array_map('trim', explode(',', (string) $name))
            ),
            fn (string $v) => $v !== ''
        ));
        sort($targetParts);
        $targetGroupKey = implode('|', $targetParts);

        if ($targetGroupKey !== '') {
            foreach ($colorStockMap as $displayName => $stock) {
                $mapParts = array_values(array_filter(
                    array_map(
                        fn (string $v) => $this->normalizeColorKey($v),
                        array_map('trim', explode(',', (string) $displayName))
                    ),
                    fn (string $v) => $v !== ''
                ));
                sort($mapParts);
                if (implode('|', $mapParts) === $targetGroupKey) {
                    return max(0, (int) $stock);
                }
            }
        }

        // Prioridad 2: claves de grupo separadas por coma — busca un color individual
        // dentro de una clave multi-color (ej: si $name es un solo color).
        foreach ($colorStockMap as $displayName => $stock) {
            $parts = array_values(array_filter(array_map(
                static fn ($value) => trim((string) $value),
                explode(',', (string) $displayName)
            ), static fn ($value) => $value !== ''));

            if ($parts === []) {
                continue;
            }

            $normalizedParts = array_map(fn (string $value) => $this->normalizeColorKey($value), $parts);
            if (in_array($target, $normalizedParts, true)) {
                return max(0, (int) $stock);
            }
        }

        return null;
    }

    private function applyColorStockToVariants(array $variants, array $colorStockMap): array
    {
        return array_map(function ($variant) use ($colorStockMap) {
            if (! is_array($variant)) {
                return $variant;
            }

            $name  = trim((string) ($variant['name'] ?? ''));
            $stock = $this->colorStockValueForName($colorStockMap, $name);
            if ($stock !== null) {
                $variant['stock'] = $stock;
            }

            return $variant;
        }, $variants);
    }

    private function validateRelevantColorStock(?string $primaryColor, Request $request, array $colorStockMap): ?string
    {
        $relevantColors = [];

        $mainColors = $this->normalizeSelectedColorNames($request->input('color', $primaryColor !== null ? [$primaryColor] : []));
        if ($mainColors === [] && trim((string) $primaryColor) !== '') {
            $mainColors = [trim((string) $primaryColor)];
        }

        foreach ($mainColors as $mainColor) {
            if ($mainColor === '') {
                continue;
            }

            $relevantColors[$this->normalizeColorKey($mainColor)] = $mainColor;
        }

        // Camera image color groups do not manage stock; only validate stock for main product colors.

        if ($relevantColors === []) {
            return $colorStockMap === []
                ? 'Debes indicar al menos el stock del color principal de la gafa.'
                : null;
        }

        $missing = [];
        foreach ($relevantColors as $displayName) {
            if ($this->colorStockValueForName($colorStockMap, $displayName) === null) {
                $missing[] = $displayName;
            }
        }

        if ($missing !== []) {
            return 'Falta indicar el stock para estos colores: ' . implode(', ', $missing) . '.';
        }

        return null;
    }

    private function resolveExistenciasValue(array $colorStockMap, mixed $fallback): ?int
    {
        if ($colorStockMap !== []) {
            $groupedStocks = [];

            foreach ($colorStockMap as $displayName => $value) {
                $names = $this->normalizeSelectedColorNames((string) $displayName);
                if ($names === []) {
                    continue;
                }

                $groupKey = $this->normalizeColorGroupKey($names);
                if ($groupKey === '') {
                    continue;
                }

                $groupedStocks[$groupKey] = max(0, (int) $value);
            }

            if ($groupedStocks !== []) {
                return array_sum($groupedStocks);
            }

            return array_sum(array_map(static fn ($value) => max(0, (int) $value), $colorStockMap));
        }

        if ($fallback === null || $fallback === '') {
            return null;
        }

        return max(0, (int) $fallback);
    }

    private function normalizeSelectedColorNames(mixed $value): array
    {
        $normalized = [];

        $collect = function (mixed $candidate) use (&$normalized, &$collect): void {
            if (is_array($candidate)) {
                foreach ($candidate as $nestedCandidate) {
                    $collect($nestedCandidate);
                }

                return;
            }

            if (is_string($candidate) && str_contains($candidate, ',')) {
                foreach (explode(',', $candidate) as $piece) {
                    $collect($piece);
                }

                return;
            }

            $name = trim((string) $candidate);
            if ($name === '') {
                return;
            }

            $key = $this->normalizeColorKey($name);
            if ($key === '') {
                return;
            }

            if (! array_key_exists($key, $normalized)) {
                $normalized[$key] = $name;
            }
        };

        $collect($value);

        return array_values($normalized);
    }

    // ──────────────────────────────────────────────
    // Parseo de valores
    // ──────────────────────────────────────────────

    private function parseMeasure(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = trim(str_replace(',', '.', $value));
        if ($value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function parseMoney(?string $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        // Solo dígitos, separadores y signo menos
        $value = preg_replace('/[^0-9,\.\-]/', '', $value) ?? '';
        $value = trim($value);

        if ($value === '' || $value === '-' || $value === '.' || $value === ',') {
            return null;
        }

        // Tiene punto Y coma: miles con punto, decimales con coma (es-CO)
        if (str_contains($value, '.') && str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, '.')) {
            // Solo punto: si parece agrupación de miles (50.000), quitar el punto
            if (preg_match('/^\-?\d{1,3}(?:\.\d{3})+$/', $value)) {
                $value = str_replace('.', '', $value);
            }
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function parseGalleryUrls(?string $raw, ?string $primary = null): array
    {
        if ($raw === null) {
            return [];
        }

        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        // Separar solo por saltos de línea (las URLs pueden contener comas)
        $parts = preg_split('/\r\n|\r|\n/', $raw) ?: [];

        $urls = [];
        foreach ($parts as $part) {
            $u = trim((string) $part);
            if ($u === '') {
                continue;
            }
            $urls[] = $u;
        }

        $urls = array_values(array_unique($urls));
        if ($primary !== null && $primary !== '') {
            $urls = array_values(array_filter($urls, static fn ($u) => $u !== $primary));
        }

        return $urls;
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'producto';
        }

        $slug = $base;
        $i    = 2;

        while (true) {
            $query = Producto::query()->where('slug', $slug);

            if ($ignoreId !== null) {
                $query->where('id', '!=', $ignoreId);
            }

            if (! $query->exists()) {
                return $slug;
            }

            $slug = $base . '-' . $i;
            $i++;
        }
    }
}
