<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promocion;
use App\Services\LandingSecondaryCarouselContent;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SecondaryBannerController extends Controller
{
    private const TIPO = 'secondary_banner';
    private const RECOMMENDED_WIDTH = 1920;
    private const RECOMMENDED_HEIGHT = 450;

    private const MIN_WIDTH = 1600;
    private const MIN_HEIGHT = 380;

    public function index()
    {
        $banners = Promocion::query()
            ->where('tipo', self::TIPO)
            ->orderBy('orden')
            ->latest('id')
            ->get();

        $secondaryCarousel = LandingSecondaryCarouselContent::load();

        return view('admin.secondary_banners.index', [
            'banners' => $banners,
            'secondaryCarousel' => $secondaryCarousel,
        ]);
    }

    public function updateCarouselSettings(Request $request)
    {
        $validated = $request->validate([
            'seconds_per_slide' => ['required', 'integer', 'min:2', 'max:30'],
        ]);

        LandingSecondaryCarouselContent::upsert($validated);

        return redirect()->route('configuracion.secondary-banners.index')
            ->with('status', 'Tiempo del carrusel secundario actualizado.');
    }

    public function create()
    {
        return view('admin.secondary_banners.create');
    }

    public function image(Promocion $secondary_banner): Response
    {
        abort_unless($secondary_banner->tipo === self::TIPO && $secondary_banner->hasUploadedHeroImage(), 404);

        $binary = base64_decode((string) $secondary_banner->uploaded_image_data, true);
        abort_if($binary === false, 404);

        $etag = '"'.sha1((string) $secondary_banner->id.'|'.(string) $secondary_banner->updated_at?->timestamp.'|'.(string) $secondary_banner->uploaded_image_size).'"';

        return response($binary, 200, [
            'Content-Type' => $secondary_banner->uploaded_image_mime,
            'Content-Length' => (string) strlen($binary),
            'Content-Disposition' => 'inline; filename="'.($secondary_banner->uploaded_image_original_name ?: 'secondary-banner').'"',
            'Cache-Control' => 'public, max-age=604800',
            'ETag' => $etag,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'imagenes' => ['required', 'array', 'min:1'],
            'imagenes.*' => ['required', 'image', 'max:51200'],
        ], [
            'imagenes.required' => 'Debes seleccionar al menos una imagen.',
            'imagenes.*.uploaded' => 'Una o más imágenes no pudieron subirse. Verifica que cada archivo pese menos de 50 MB e inténtalo de nuevo.',
            'imagenes.*.image' => 'Cada archivo debe ser una imagen válida.',
            'imagenes.*.max' => 'Cada imagen debe pesar máximo 50 MB.',
        ]);

        $files = $request->file('imagenes', []);
        $files = is_array($files) ? $files : [];

        $nextOrder = ((int) Promocion::query()->where('tipo', self::TIPO)->max('orden')) + 1;

        $meta = [
            'image_recommended_px' => self::RECOMMENDED_WIDTH.'x'.self::RECOMMENDED_HEIGHT,
            'image_min_px' => self::MIN_WIDTH.'x'.self::MIN_HEIGHT,
            'image_pos_x' => 50,
            'image_pos_y' => 50,
            'image_zoom' => 1,
        ];

        foreach ($files as $file) {
            if (!($file instanceof UploadedFile) || !$file->isValid()) {
                continue;
            }

            $uploadedImage = $this->extractUploadedImagePayloadFromFile($file);
            if (empty($uploadedImage)) {
                continue;
            }

            Promocion::query()->create([
                'tipo' => self::TIPO,
                'titulo' => 'Banner secundario '.$nextOrder,
                'insignia' => null,
                'descripcion' => null,
                'texto_cta' => null,
                'url_cta' => null,
                'ruta_imagen' => $uploadedImage['ruta_imagen'] ?? null,
                'uploaded_image_data' => $uploadedImage['uploaded_image_data'] ?? null,
                'uploaded_image_mime' => $uploadedImage['uploaded_image_mime'] ?? null,
                'uploaded_image_original_name' => $uploadedImage['uploaded_image_original_name'] ?? null,
                'uploaded_image_size' => $uploadedImage['uploaded_image_size'] ?? null,
                'esta_activa' => true,
                'orden' => $nextOrder,
                'meta' => $meta,
            ]);

            $nextOrder++;
        }

        return redirect()->route('configuracion.secondary-banners.index')
            ->with('status', 'Banners secundarios creados.');
    }

    public function edit(Promocion $secondary_banner)
    {
        abort_unless($secondary_banner->tipo === self::TIPO, 404);

        return view('admin.secondary_banners.edit', [
            'banner' => $secondary_banner,
        ]);
    }

    public function update(Request $request, Promocion $secondary_banner)
    {
        abort_unless($secondary_banner->tipo === self::TIPO, 404);

        $request->validate([
            'imagen' => ['nullable', 'image', 'max:51200'],
            'imagenes_extra' => ['nullable', 'array'],
            'imagenes_extra.*' => ['nullable', 'image', 'max:51200'],
        ], [
            'imagen.uploaded' => 'La imagen principal no pudo subirse. Verifica que pese menos de 50 MB e inténtalo de nuevo.',
            'imagen.image' => 'La imagen principal debe ser una imagen válida.',
            'imagen.max' => 'La imagen principal debe pesar máximo 50 MB.',
            'imagenes_extra.*.uploaded' => 'Una o más imágenes extra no pudieron subirse. Verifica que cada archivo pese menos de 50 MB.',
            'imagenes_extra.*.image' => 'Cada imagen extra debe ser una imagen válida.',
            'imagenes_extra.*.max' => 'Cada imagen extra debe pesar máximo 50 MB.',
        ]);

        $hasMainImage = $request->hasFile('imagen');
        $extraFilesRaw = $request->file('imagenes_extra', []);
        $extraFilesRaw = is_array($extraFilesRaw) ? $extraFilesRaw : [];
        $extraFiles = array_values(array_filter(
            $extraFilesRaw,
            fn ($file) => $file instanceof UploadedFile && $file->isValid()
        ));

        if (!$hasMainImage && count($extraFiles) === 0) {
            return back()
                ->withInput()
                ->withErrors(['imagen' => 'Sube una imagen para reemplazar este banner o agrega varias en "Agregar más banners".']);
        }

        $updatedCurrent = false;
        if ($hasMainImage) {
            $this->deleteStoredImage($secondary_banner);
            $uploadedImage = $this->extractUploadedImagePayload($request);

            $meta = array_filter([
                'image_recommended_px' => self::RECOMMENDED_WIDTH.'x'.self::RECOMMENDED_HEIGHT,
                'image_min_px' => self::MIN_WIDTH.'x'.self::MIN_HEIGHT,
                'image_pos_x' => (float) ($secondary_banner->meta['image_pos_x'] ?? 50),
                'image_pos_y' => (float) ($secondary_banner->meta['image_pos_y'] ?? 50),
                'image_zoom' => (float) ($secondary_banner->meta['image_zoom'] ?? 1),
            ], fn ($v) => $v !== null && $v !== '');

            $secondary_banner->update([
                'ruta_imagen' => $uploadedImage['ruta_imagen'] ?? null,
                'uploaded_image_data' => $uploadedImage['uploaded_image_data'],
                'uploaded_image_mime' => $uploadedImage['uploaded_image_mime'],
                'uploaded_image_original_name' => $uploadedImage['uploaded_image_original_name'],
                'uploaded_image_size' => $uploadedImage['uploaded_image_size'],
                'esta_activa' => true,
                'meta' => $meta,
            ]);

            $updatedCurrent = true;
        }

        $createdExtra = 0;
        if (count($extraFiles) > 0) {
            $nextOrder = ((int) Promocion::query()->where('tipo', self::TIPO)->max('orden')) + 1;

            $baseMeta = [
                'image_recommended_px' => self::RECOMMENDED_WIDTH.'x'.self::RECOMMENDED_HEIGHT,
                'image_min_px' => self::MIN_WIDTH.'x'.self::MIN_HEIGHT,
                'image_pos_x' => 50,
                'image_pos_y' => 50,
                'image_zoom' => 1,
            ];

            foreach ($extraFiles as $file) {
                $uploadedImage = $this->extractUploadedImagePayloadFromFile($file);
                if (empty($uploadedImage)) {
                    continue;
                }

                Promocion::query()->create([
                    'tipo' => self::TIPO,
                    'titulo' => 'Banner secundario '.$nextOrder,
                    'insignia' => null,
                    'descripcion' => null,
                    'texto_cta' => null,
                    'url_cta' => null,
                    'ruta_imagen' => $uploadedImage['ruta_imagen'] ?? null,
                    'uploaded_image_data' => $uploadedImage['uploaded_image_data'] ?? null,
                    'uploaded_image_mime' => $uploadedImage['uploaded_image_mime'] ?? null,
                    'uploaded_image_original_name' => $uploadedImage['uploaded_image_original_name'] ?? null,
                    'uploaded_image_size' => $uploadedImage['uploaded_image_size'] ?? null,
                    'esta_activa' => true,
                    'orden' => $nextOrder,
                    'meta' => $baseMeta,
                ]);

                $nextOrder++;
                $createdExtra++;
            }
        }

        $status = [];
        if ($updatedCurrent) {
            $status[] = 'Banner secundario actualizado';
        }
        if ($createdExtra > 0) {
            $status[] = $createdExtra.' banners secundarios agregados';
        }

        return redirect()->route('configuracion.secondary-banners.edit', $secondary_banner)
            ->with('status', implode(' · ', $status).'.');
    }

    public function destroy(Promocion $secondary_banner)
    {
        abort_unless($secondary_banner->tipo === self::TIPO, 404);

        $this->deleteStoredImage($secondary_banner);

        $secondary_banner->delete();

        return redirect()->route('configuracion.secondary-banners.index')
            ->with('status', 'Banner secundario eliminado.');
    }

    private function extractUploadedImagePayload(Request $request): array
    {
        if (!$request->hasFile('imagen')) {
            return [];
        }

        $file = $request->file('imagen');
        if (!($file instanceof UploadedFile)) {
            return [];
        }

        return $this->extractUploadedImagePayloadFromFile($file);
    }

    private function extractUploadedImagePayloadFromFile(UploadedFile $file): array
    {
        if (!$file || !$file->isValid()) {
            return [];
        }

        $path = $file->getRealPath() ?: $file->getPathname();
        if (!is_string($path) || trim($path) === '') {
            return [];
        }

        $binary = @file_get_contents($path);
        if ($binary === false) {
            return [];
        }

        $extension = match (strtolower((string) ($file->getMimeType() ?: ''))) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => $file->guessExtension() ?: 'bin',
        };

        $storedPath = 'banners/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($storedPath, $binary);

        return [
            'ruta_imagen' => $storedPath,
            'uploaded_image_data' => null,
            'uploaded_image_mime' => null,
            'uploaded_image_original_name' => null,
            'uploaded_image_size' => null,
        ];
    }

    private function deleteStoredImage(?Promocion $banner): void
    {
        if (! $banner || ! $banner->hasStoredHeroImage()) {
            return;
        }

        Storage::disk($banner->imageDisk())->delete($banner->ruta_imagen);
    }
}
