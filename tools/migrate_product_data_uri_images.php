<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

ini_set('memory_limit', '512M');

const IMAGE_DISK = 'public';
const IMAGE_DIRECTORY = 'productos/migrated';

function publicStorageUrl(string $path): string
{
    return '/storage/' . ltrim(str_replace('\\', '/', $path), '/');
}

function normalizeStorageUrl(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }

    if (str_starts_with($url, '/storage/')) {
        return $url;
    }

    foreach (['http://localhost/storage/', 'https://localhost/storage/', 'http://127.0.0.1:8000/storage/', 'https://127.0.0.1:8000/storage/'] as $prefix) {
        if (str_starts_with($url, $prefix)) {
            return '/storage/' . ltrim(substr($url, strlen($prefix)), '/');
        }
    }

    return $url;
}

function storeDataUriImage(string $dataUri, int $productId, string $slot): ?array
{
    if (!str_starts_with($dataUri, 'data:')) {
        return null;
    }

    $separator = strpos($dataUri, ';base64,');
    if ($separator === false) {
        return null;
    }

    $mime = strtolower(trim(substr($dataUri, 5, $separator - 5)));
    $base64Payload = substr($dataUri, $separator + 8);
    if ($mime === '' || $base64Payload === false || $base64Payload === '') {
        return null;
    }

    $binary = base64_decode($base64Payload, true);
    if ($binary === false) {
        return null;
    }

    $extension = match ($mime) {
        'image/jpeg', 'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'image/avif' => 'avif',
        default => 'bin',
    };

    $path = sprintf('%s/producto-%d/%s-%s.%s', IMAGE_DIRECTORY, $productId, $slot, Str::random(16), $extension);
    Storage::disk(IMAGE_DISK)->put($path, $binary);

    return [
        'path' => $path,
        'url' => publicStorageUrl($path),
        'mime' => $mime,
        'size' => strlen($binary),
    ];
}

function migrateVariant(array $variant, int $productId, int $variantIndex, array &$stats): array
{
    $images = [];
    $imagePaths = [];

    foreach ((array) ($variant['images'] ?? []) as $imageIndex => $image) {
        $image = is_string($image) ? trim($image) : '';
        if ($image === '') {
            continue;
        }

        if (str_starts_with($image, 'data:image/')) {
            $stored = storeDataUriImage($image, $productId, "variant-{$variantIndex}-{$imageIndex}");
            if ($stored !== null) {
                $images[] = $stored['url'];
                $imagePaths[] = $stored['path'];
                $stats['converted']++;
                continue;
            }
        }

        $images[] = normalizeStorageUrl($image);
    }

    $primaryImage = is_string($variant['image'] ?? null) ? normalizeStorageUrl((string) $variant['image']) : '';
    $primaryPath = trim((string) ($variant['image_path'] ?? ''));

    if ($primaryImage !== '' && str_starts_with($primaryImage, 'data:image/')) {
        $stored = storeDataUriImage($primaryImage, $productId, "variant-{$variantIndex}-main");
        if ($stored !== null) {
            $primaryImage = $stored['url'];
            $primaryPath = $stored['path'];
            $stats['converted']++;
        }
    }

    if ($primaryImage !== '' && !in_array($primaryImage, $images, true)) {
        array_unshift($images, $primaryImage);
    }

    if ($primaryPath !== '' && !in_array($primaryPath, $imagePaths, true)) {
        array_unshift($imagePaths, $primaryPath);
    }

    $variant['image'] = $primaryImage;
    $variant['image_path'] = $primaryPath !== '' ? $primaryPath : null;
    $variant['images'] = array_values(array_unique(array_filter($images, static fn (string $value): bool => $value !== '')));
    $variant['image_paths'] = array_values(array_unique(array_filter($imagePaths, static fn (string $value): bool => $value !== '')));

    return $variant;
}

$stats = [
    'rows' => 0,
    'updated' => 0,
    'converted' => 0,
    'skipped' => 0,
];

DB::table('productos')
    ->select(['id', 'meta'])
    ->orderBy('id')
    ->chunkById(25, function ($rows) use (&$stats): void {
        foreach ($rows as $row) {
            $stats['rows']++;

            if (!is_string($row->meta) || trim($row->meta) === '') {
                $stats['skipped']++;
                continue;
            }

            $meta = json_decode($row->meta, true);
            if (!is_array($meta)) {
                $stats['skipped']++;
                continue;
            }

            $changed = false;

            $mainImage = is_string($meta['imagen_url'] ?? null) ? trim((string) $meta['imagen_url']) : '';
            $normalizedMainImage = normalizeStorageUrl($mainImage);
            if ($normalizedMainImage !== $mainImage) {
                $meta['imagen_url'] = $normalizedMainImage;
                $mainImage = $normalizedMainImage;
                $changed = true;
            }

            if ($mainImage !== '' && str_starts_with($mainImage, 'data:image/')) {
                $stored = storeDataUriImage($mainImage, (int) $row->id, 'main');
                if ($stored !== null) {
                    $meta['imagen_url'] = $stored['url'];
                    $meta['uploaded_imagen_path'] = $stored['path'];
                    $meta['uploaded_imagen_mime'] = $meta['uploaded_imagen_mime'] ?? $stored['mime'];
                    $meta['uploaded_imagen_size'] = $stored['size'];
                    $changed = true;
                    $stats['converted']++;
                }
            }

            if (is_array($meta['imagenes'] ?? null)) {
                foreach ($meta['imagenes'] as $index => $image) {
                    $image = is_string($image) ? trim($image) : '';
                    $normalizedImage = normalizeStorageUrl($image);
                    if ($normalizedImage !== $image) {
                        $meta['imagenes'][$index] = $normalizedImage;
                        $image = $normalizedImage;
                        $changed = true;
                    }

                    if ($image === '' || !str_starts_with($image, 'data:image/')) {
                        continue;
                    }

                    $stored = storeDataUriImage($image, (int) $row->id, "gallery-{$index}");
                    if ($stored === null) {
                        continue;
                    }

                    $meta['imagenes'][$index] = $stored['url'];
                    $changed = true;
                    $stats['converted']++;
                }
            }

            if (is_array($meta['color_variants'] ?? null)) {
                foreach ($meta['color_variants'] as $index => $variant) {
                    if (!is_array($variant)) {
                        continue;
                    }

                    $migratedVariant = migrateVariant($variant, (int) $row->id, (int) $index, $stats);
                    if ($migratedVariant !== $variant) {
                        $meta['color_variants'][$index] = $migratedVariant;
                        $changed = true;
                    }
                }
            }

            if (!$changed) {
                $stats['skipped']++;
                continue;
            }

            DB::table('productos')
                ->where('id', $row->id)
                ->update([
                    'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);

            $stats['updated']++;
            echo "Migrado producto {$row->id}" . PHP_EOL;
        }
    });

echo PHP_EOL;
echo 'Filas revisadas: ' . $stats['rows'] . PHP_EOL;
echo 'Productos actualizados: ' . $stats['updated'] . PHP_EOL;
echo 'Imagenes convertidas: ' . $stats['converted'] . PHP_EOL;
echo 'Filas omitidas: ' . $stats['skipped'] . PHP_EOL;
