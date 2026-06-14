<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * One-time migration: convert Base64 data URIs stored in productos.meta
 * into physical files under storage/app/public/productos/.
 *
 * Only processes images whose URL starts with "data:".
 * External "http..." and already-converted "/storage/..." URLs are skipped.
 *
 * Run once with:  php artisan migrate
 * Safe to re-run: already-converted rows are skipped automatically.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('productos')
            ->whereNotNull('meta')
            ->get(['id', 'meta']);

        $disk = Storage::disk('public');
        $disk->makeDirectory('productos');

        foreach ($rows as $row) {
            $meta = is_string($row->meta) ? json_decode($row->meta, true) : $row->meta;

            if (!is_array($meta)) {
                continue;
            }

            $changed = false;

            // ── Main image ────────────────────────────────────────────────
            if (isset($meta['imagen_url']) && str_starts_with((string) $meta['imagen_url'], 'data:')) {
                $path = $this->saveBase64($disk, $meta['imagen_url']);
                if ($path !== null) {
                    $meta['imagen_url'] = $path;
                    $changed = true;
                }
            }

            // ── Color variant images ──────────────────────────────────────
            if (isset($meta['color_variants']) && is_array($meta['color_variants'])) {
                foreach ($meta['color_variants'] as $key => $variant) {
                    if (!is_array($variant)) {
                        continue;
                    }
                    foreach (['imagen_url', 'imagen_url_alt'] as $field) {
                        if (isset($variant[$field]) && str_starts_with((string) $variant[$field], 'data:')) {
                            $path = $this->saveBase64($disk, $variant[$field]);
                            if ($path !== null) {
                                $meta['color_variants'][$key][$field] = $path;
                                $changed = true;
                            }
                        }
                    }
                }
            }

            if ($changed) {
                DB::table('productos')
                    ->where('id', $row->id)
                    ->update(['meta' => json_encode($meta)]);
            }
        }
    }

    public function down(): void
    {
        // Intentionally irreversible — do not revert converted files.
    }

    // ── Helper ────────────────────────────────────────────────────────────

    private function saveBase64(\Illuminate\Contracts\Filesystem\Filesystem $disk, string $dataUri): ?string
    {
        // Format:  data:<mime>;base64,<data>
        if (!preg_match('/^data:(image\/[a-z+\-]+);base64,(.+)$/s', $dataUri, $m)) {
            return null;
        }

        $extension = match (strtolower($m[1])) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png'               => 'png',
            'image/gif'               => 'gif',
            'image/webp'              => 'webp',
            'image/svg+xml'           => 'svg',
            default                   => 'bin',
        };

        $binary = base64_decode($m[2], strict: true);
        if ($binary === false) {
            return null;
        }

        $filename = 'productos/' . Str::uuid() . '.' . $extension;
        $disk->put($filename, $binary);

        return '/storage/' . $filename;   // e.g. /storage/productos/uuid.jpg
    }
};
