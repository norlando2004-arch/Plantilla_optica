<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Promocion extends Model
{
    use HasFactory;

    protected $table = 'promociones';

    protected $fillable = [
        'tipo',
        'titulo',
        'codigo',
        'insignia',
        'descripcion',
        'texto_cta',
        'url_cta',
        'ruta_imagen',
        'uploaded_image_data',
        'uploaded_image_mime',
        'uploaded_image_original_name',
        'uploaded_image_size',
        'empieza_en',
        'termina_en',
        'esta_activa',
        'orden',
        'meta',
    ];

    protected $casts = [
        'empieza_en' => 'datetime',
        'termina_en' => 'datetime',
        'esta_activa' => 'boolean',
        'meta' => 'array',
    ];

    public function productos()
    {
        return $this->belongsToMany(
            Producto::class,
            'promocion_producto',
            'promocion_id',
            'producto_id'
        );
    }

    public function imageDisk(): string
    {
        return (string) data_get($this->meta, 'image_disk', config('filesystems.hero_banners_disk', 'public'));
    }

    public function hasExternalHeroImage(): bool
    {
        return self::isExternalImagePath($this->ruta_imagen);
    }

    public function hasUploadedHeroImage(): bool
    {
        return filled($this->uploaded_image_data) && filled($this->uploaded_image_mime);
    }

    public function hasStoredHeroImage(): bool
    {
        return filled($this->ruta_imagen) && ! $this->hasExternalHeroImage();
    }

    public function heroImageUrl(?string $fallback = null): ?string
    {
        return $this->bannerImageUrl('hero-banners.image', 'hero_banner', $fallback);
    }

    public function bannerImageUrl(string $routeName, string $parameterName, ?string $fallback = null): ?string
    {
        if ($this->hasUploadedHeroImage()) {
            return route($routeName, [
                $parameterName => $this,
                'v' => $this->updated_at?->timestamp ?? $this->getKey(),
            ]);
        }

        if (blank($this->ruta_imagen)) {
            return $fallback;
        }

        if ($this->hasExternalHeroImage()) {
            return $this->ruta_imagen;
        }

        // Evita depender de APP_URL (host/puerto) cuando el archivo esta en disco publico local.
        if ($this->imageDisk() === 'public') {
            return '/storage/' . ltrim((string) $this->ruta_imagen, '/');
        }

        return Storage::disk($this->imageDisk())->url($this->ruta_imagen);
    }

    private static function isExternalImagePath(?string $path): bool
    {
        return filled($path) && Str::startsWith($path, ['http://', 'https://', '//', 'data:']);
    }
}
