<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'slug',
        'tipo',
        'genero_objetivo',
        'marca',
        'material_montura',
        'color',
        'descripcion',
        'caracteristicas',
        'precio',
        'precio_oferta',
        'moneda',
        'existencias',
        'esta_activo',
        'destacado',
        'meta',
    ];

    protected $casts = [
        'caracteristicas' => 'array',
        'meta' => 'array',
        'esta_activo' => 'boolean',
        'destacado' => 'boolean',
        'precio' => 'decimal:2',
        'precio_oferta' => 'decimal:2',
    ];

    public function promociones()
    {
        return $this->belongsToMany(
            Promocion::class,
            'promocion_producto',
            'producto_id',
            'promocion_id'
        );
    }

    public function favoritos()
    {
        return $this->hasMany(Favorito::class, 'producto_id');
    }

    public function stockPorColor(): array
    {
        $meta = is_array($this->meta) ? $this->meta : [];
        $normalized = [];

        $rawMap = is_array($meta['color_stock'] ?? null) ? $meta['color_stock'] : [];
        foreach ($rawMap as $name => $stock) {
            $key = $this->normalizeColorGroupKey(is_string($name) ? $name : '');
            if ($key === '' || $stock === null || $stock === '') {
                continue;
            }

            $normalized[$key] = max(0, (int) $stock);
        }

        if ($normalized === []) {
            foreach ((array) ($meta['color_variants'] ?? []) as $variant) {
                if (!is_array($variant)) {
                    continue;
                }

                $name = (string) ($variant['name'] ?? '');
                $key = $this->normalizeColorGroupKey($name);
                $stock = $variant['stock'] ?? null;

                if ($key === '' || $stock === null || $stock === '') {
                    continue;
                }

                $normalized[$key] = max(0, (int) $stock);
            }
        }

        return $normalized;
    }

    public function stockDisponibleParaColor(?string $color): ?int
    {
        $map = $this->stockPorColor();

        if ($map === []) {
            return $this->existencias === null ? null : max(0, (int) $this->existencias);
        }

        $fallbackColor = is_string($this->color) ? $this->color : '';
        $resolvedKeys = $this->resolveColorKeysFromMap($map, $color ?: $fallbackColor);

        if ($resolvedKeys !== []) {
            $stocks = array_map(static fn (string $key): int => max(0, (int) ($map[$key] ?? 0)), $resolvedKeys);

            return min($stocks);
        }

        return 0;
    }

    public function tieneStockParaColor(?string $color, int $cantidad = 1): bool
    {
        $cantidad = max(1, $cantidad);
        $stock = $this->stockDisponibleParaColor($color);

        return $stock === null || $stock >= $cantidad;
    }

    public function lowStockColors(int $threshold = 5): array
    {
        $threshold = max(0, $threshold);
        $map = $this->stockPorColor();

        if ($map === []) {
            $stock = $this->existencias;
            if ($stock === null || (int) $stock > $threshold) {
                return [];
            }

            $label = trim((string) ($this->color ?? 'General'));

            return [[
                'color' => $label !== '' ? $label : 'General',
                'stock' => max(0, (int) $stock),
            ]];
        }

        $meta = is_array($this->meta) ? $this->meta : [];
        $displayMap = $this->mergeColorStockIntoDisplayMap($meta, $map);
        $alerts = [];
        $coveredColorKeys = [];
        $seenAlertKeys = [];

        // Prioriza alertas por grupos (ej: "Negro, Azul") para evitar mensajes sueltos por color.
        foreach ((array) ($meta['color_variants'] ?? []) as $variant) {
            if (!is_array($variant)) {
                continue;
            }

            $rawName = trim((string) ($variant['name'] ?? ''));
            if ($rawName === '') {
                continue;
            }

            $variantNames = $this->extractVariantColorNames($rawName);
            if ($variantNames === []) {
                $variantNames = [$rawName];
            }

            $componentStocks = [];
            foreach ($variantNames as $variantName) {
                $componentKey = $this->normalizeColorKey($variantName);
                if ($componentKey === '' || !array_key_exists($componentKey, $map)) {
                    continue;
                }

                $coveredColorKeys[$componentKey] = true;
                $componentStocks[] = max(0, (int) $map[$componentKey]);
            }

            $variantStockRaw = $variant['stock'] ?? null;
            $variantStock = ($variantStockRaw !== null && $variantStockRaw !== '')
                ? max(0, (int) $variantStockRaw)
                : null;

            if ($variantStock === null && $componentStocks !== []) {
                $allEqual = count(array_unique($componentStocks)) === 1;
                $variantStock = ($allEqual || count($variantNames) === 1)
                    ? $componentStocks[0]
                    : min($componentStocks);
            }

            if ($variantStock === null || $variantStock > $threshold) {
                continue;
            }

            $variantLabel = implode(', ', $variantNames);
            $alertKey = $this->normalizeColorGroupKey($variantLabel);
            if ($alertKey !== '' && isset($seenAlertKeys[$alertKey])) {
                continue;
            }

            $alerts[] = [
                'color' => $variantLabel,
                'stock' => $variantStock,
            ];

            if ($alertKey !== '') {
                $seenAlertKeys[$alertKey] = true;
            }
        }

        foreach ($displayMap as $label => $stock) {
            $stock = max(0, (int) $stock);
            if ($stock > $threshold) {
                continue;
            }

            $labelKey = $this->normalizeColorGroupKey((string) $label);
            if ($labelKey !== '' && isset($coveredColorKeys[$labelKey])) {
                continue;
            }

            if ($labelKey !== '' && isset($seenAlertKeys[$labelKey])) {
                continue;
            }

            $alerts[] = [
                'color' => (string) $label,
                'stock' => $stock,
            ];

            if ($labelKey !== '') {
                $seenAlertKeys[$labelKey] = true;
            }
        }

        return $alerts;
    }

    public function stockStatusForColor(?string $color, int $threshold = 5): array
    {
        $threshold = max(0, $threshold);
        $stock = $this->stockDisponibleParaColor($color);
        $resolvedColor = trim((string) ($color ?: ($this->color ?? '')));

        if ($stock === null) {
            return [
                'stock' => null,
                'message' => 'Disponible',
                'class' => 'text-emerald-700',
                'is_low' => false,
                'is_sold_out' => false,
            ];
        }

        $stock = max(0, (int) $stock);

        if ($stock <= 0) {
            return [
                'stock' => 0,
                'message' => $resolvedColor !== '' ? 'Agotado en color ' . $resolvedColor : 'Agotado',
                'class' => 'text-rose-700',
                'is_low' => true,
                'is_sold_out' => true,
            ];
        }

        if ($stock <= $threshold) {
            return [
                'stock' => $stock,
                'message' => $resolvedColor !== '' ? 'Te quedan pocas gafas del color ' . $resolvedColor : 'Te quedan pocas gafas',
                'class' => 'text-amber-700',
                'is_low' => true,
                'is_sold_out' => false,
            ];
        }

        return [
            'stock' => $stock,
            'message' => $stock . ' disponibles',
            'class' => 'text-emerald-700',
            'is_low' => false,
            'is_sold_out' => false,
        ];
    }

    public function decrementStockForColor(?string $color, int $cantidad = 1): ?int
    {
        $cantidad = max(1, $cantidad);
        $meta = is_array($this->meta) ? $this->meta : [];
        $map = $this->stockPorColor();

        if ($map !== []) {
            $fallbackColor = (string) ($meta['color'] ?? $this->color ?? '');
            $resolvedKeys = $this->resolveColorKeysFromMap($map, $color ?: $fallbackColor);

            if ($resolvedKeys !== []) {
                foreach ($resolvedKeys as $resolvedKey) {
                    $map[$resolvedKey] = max(0, (int) ($map[$resolvedKey] ?? 0) - $cantidad);
                }

                $meta['color_stock'] = $this->mergeColorStockIntoDisplayMap($meta, $map);

                if (is_array($meta['color_variants'] ?? null)) {
                    $meta['color_variants'] = array_map(function ($variant) use ($map) {
                        if (!is_array($variant)) {
                            return $variant;
                        }

                        $name = (string) ($variant['name'] ?? '');
                        $variantKeys = $this->resolveColorKeysFromMap($map, $name);
                        if ($variantKeys !== []) {
                            $variantStocks = array_map(static fn (string $key): int => max(0, (int) ($map[$key] ?? 0)), $variantKeys);
                            $variant['stock'] = min($variantStocks);
                        }

                        return $variant;
                    }, $meta['color_variants']);
                }

                $this->forceFill([
                    'meta' => $meta,
                    'existencias' => array_sum($map),
                ])->save();

                $remainingStocks = array_map(static fn (string $key): int => max(0, (int) ($map[$key] ?? 0)), $resolvedKeys);

                return min($remainingStocks);
            }
        }

        if ($this->existencias !== null) {
            $restante = max(0, (int) $this->existencias - $cantidad);
            $this->forceFill([
                'existencias' => $restante,
            ])->save();

            return $restante;
        }

        return null;
    }

    private function normalizeColorKey(?string $color): string
    {
        return Str::lower(trim(Str::ascii((string) $color)));
    }

    private function resolveColorKeysFromMap(array $map, ?string $color): array
    {
        $rawColor = trim((string) $color);
        if ($rawColor === '') {
            return [];
        }

        $directKey = $this->normalizeColorGroupKey($rawColor);
        if ($directKey !== '' && array_key_exists($directKey, $map)) {
            return [$directKey];
        }

        $parts = $this->extractVariantColorNames($rawColor);
        if (count($parts) <= 1) {
            return [];
        }

        $resolvedKeys = [];
        foreach ($parts as $part) {
            $partKey = $this->normalizeColorKey($part);
            if ($partKey === '' || !array_key_exists($partKey, $map)) {
                return [];
            }

            $resolvedKeys[] = $partKey;
        }

        return array_values(array_unique($resolvedKeys));
    }

    private function extractVariantColorNames(?string $rawName): array
    {
        $parts = preg_split('/\s*,\s*/', (string) $rawName) ?: [];
        $normalized = [];
        $seen = [];

        foreach ($parts as $part) {
            $name = trim((string) $part);
            $nameKey = $this->normalizeColorKey($name);
            if ($name === '' || $nameKey === '' || isset($seen[$nameKey])) {
                continue;
            }

            $seen[$nameKey] = true;
            $normalized[] = $name;
        }

        return $normalized;
    }

    private function normalizeColorGroupKey(?string $rawName): string
    {
        $parts = $this->extractVariantColorNames($rawName);
        if ($parts === []) {
            return '';
        }

        $keys = array_values(array_filter(array_map(
            fn (string $name): string => $this->normalizeColorKey($name),
            $parts
        ), fn (string $key): bool => $key !== ''));

        if ($keys === []) {
            return '';
        }

        sort($keys);

        return implode('|', array_values(array_unique($keys)));
    }

    private function mergeColorStockIntoDisplayMap(array $meta, array $normalizedMap): array
    {
        $displayNames = [];

        $primary = trim((string) ($meta['color'] ?? $this->color ?? ''));
        if ($primary !== '') {
            $displayNames[$this->normalizeColorGroupKey($primary)] = $primary;
        }

        foreach ((array) ($meta['color_variants'] ?? []) as $variant) {
            if (!is_array($variant)) {
                continue;
            }

            $name = trim((string) ($variant['name'] ?? ''));
            if ($name !== '') {
                $displayNames[$this->normalizeColorGroupKey($name)] = $name;
            }
        }

        $displayMap = [];
        foreach ($normalizedMap as $key => $stock) {
            $label = $displayNames[$key] ?? ucfirst($key);
            $displayMap[$label] = max(0, (int) $stock);
        }

        return $displayMap;
    }
}
