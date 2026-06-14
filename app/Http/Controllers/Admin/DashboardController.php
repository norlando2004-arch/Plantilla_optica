<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloqueContenido;
use App\Services\CompanyNotificationEmailsContent;
use App\Services\GafasPromoContent;
use App\Services\LandingBenefitStripContent;
use App\Services\LandingBenefitsContent;
use App\Services\LandingAdminReadyContent;
use App\Services\LandingCategoryPhotosContent;
use App\Services\LandingCategoriesContent;
use App\Services\LandingContactContent;
use App\Services\LandingEssentialBenefitsContent;
use App\Services\LandingFaqContent;
use App\Services\LandingHighlightsContent;
use App\Services\LandingHowItWorksContent;
use App\Services\LandingIntroContent;
use App\Services\LandingLocationContent;
use App\Services\LandingNewsletterContent;
use App\Services\LandingFooterContent;
use App\Services\LandingPromoBannersContent;
use App\Services\LandingQuickGuideContent;
use App\Services\LandingServicesContent;
use App\Services\LandingWhatsappContent;
use App\Services\GafasFormulasContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function dashboard()
    {
        return view('dashboard', [
            'gafasPromo' => GafasPromoContent::load(),
        ]);
    }

    private function validateLandingHref(string $href): bool
    {
        $href = trim($href);
        if ($href === '' || mb_strlen($href) > 2048) return false;

        $lower = mb_strtolower($href);
        if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:')) return false;

        // Allow anchors and relative paths.
        if (str_starts_with($href, '#') || str_starts_with($href, '/')) return true;

        // Allow absolute http(s) URLs.
        if (filter_var($href, FILTER_VALIDATE_URL) === false) return false;
        $scheme = (string) (parse_url($href, PHP_URL_SCHEME) ?? '');
        return in_array(mb_strtolower($scheme), ['http', 'https'], true);
    }

    private function validateLandingImageSrc(?string $src): bool
    {
        $src = trim((string) $src);
        if ($src === '') return true; // allow empty -> placeholder
        if (mb_strlen($src) > 2048) return false;

        $lower = mb_strtolower($src);
        if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:')) return false;

        // Allow relative paths (e.g. /storage/...)
        if (str_starts_with($src, '/')) return true;

        if (filter_var($src, FILTER_VALIDATE_URL) === false) return false;
        $scheme = (string) (parse_url($src, PHP_URL_SCHEME) ?? '');
        return in_array(mb_strtolower($scheme), ['http', 'https'], true);
    }

    private function normalizeSocialHref(string $href): string
    {
        $href = trim($href);
        if ($href === '' || $href === '#') return '#';

        $lower = mb_strtolower($href);
        if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:')) return '#';

        if (str_starts_with($href, '#') || str_starts_with($href, '/')) return $href;

        if (preg_match('/^[a-z][a-z0-9+\-.]*:\/\//i', $href) === 1) {
            return $href;
        }

        return 'https://' . ltrim($href, '/');
    }

    private function normalizeAboutHref(string $href): string
    {
        $href = trim($href);
        if ($href === '') return '#';

        $lower = mb_strtolower($href);
        if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:')) return '#';

        if (str_starts_with($href, '#') || str_starts_with($href, '/')) return $href;

        if (preg_match('/^[a-z][a-z0-9+\-.]*:\/\//i', $href) === 1) {
            return $href;
        }

        return 'https://' . ltrim($href, '/');
    }

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
                'field_key' => $fieldKey,
                'orden' => $nextOrder,
                'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                'original_name' => $file->getClientOriginalName() ?: null,
                'size_bytes' => $file->getSize(),
                'contenido_base64' => null,
                'ruta_archivo' => $storedPath,
            ]);
        }
    }

    private function replaceContentBlockFiles(BloqueContenido $block, string $fieldKey, array $files): void
    {
        $this->clearContentBlockImages($block, $fieldKey);
        $this->appendContentBlockImages($block, $fieldKey, $files);
    }

    public function index()
    {
        return redirect()->route('configuracion.hero-banners.index');
    }

    public function updateGafasPromo(Request $request): RedirectResponse
    {
        $request->validate([
            'promo_image_file' => ['nullable', 'file', 'image', 'max:51200'],
            'remove_promo_image' => ['nullable', 'boolean'],
        ]);

        $removePromoImage = $request->boolean('remove_promo_image');

        $block = BloqueContenido::query()->firstOrCreate(
            ['clave' => GafasPromoContent::BLOCK_KEY],
            [
                'titulo' => 'Gafas: promo de cabecera',
                'cuerpo' => null,
                'datos' => GafasPromoContent::defaults(),
                'esta_activo' => true,
                'orden' => 2,
            ]
        );

        if ($request->hasFile('promo_image_file')) {
            $this->clearContentBlockImages($block, GafasPromoContent::FIELD_IMAGE);
            $this->appendContentBlockImages($block, GafasPromoContent::FIELD_IMAGE, [$request->file('promo_image_file')]);
        } elseif ($removePromoImage) {
            $this->clearContentBlockImages($block, GafasPromoContent::FIELD_IMAGE);
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'Imagen promo de /gafas actualizada.');
    }

    public function editFormulaImages()
    {
        return view('dashboard.formula_images', [
            'formulas' => GafasFormulasContent::load(),
        ]);
    }

    public function updateFormulaImages(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mono_image_file' => ['nullable', 'file', 'image', 'max:51200'],
            'bifocal_image_file' => ['nullable', 'file', 'image', 'max:51200'],
            'progresivo_basic_image_file' => ['nullable', 'file', 'image', 'max:51200'],
            'progresivo_premium_image_file' => ['nullable', 'file', 'image', 'max:51200'],
            'ocupacional_image_file' => ['nullable', 'file', 'image', 'max:51200'],
            'mono_description_text' => ['nullable', 'string', 'max:400'],
            'bifocal_description_text' => ['nullable', 'string', 'max:400'],
            'ocupacional_description_text' => ['nullable', 'string', 'max:400'],
            'progresivo_description_text' => ['nullable', 'string', 'max:400'],
            'bifocal_blanco_description_text' => ['nullable', 'string', 'max:400'],
            'bifocal_ar_azul_description_text' => ['nullable', 'string', 'max:400'],
            'bifocal_ar_verde_description_text' => ['nullable', 'string', 'max:400'],
            'bifocal_ar_azul_foto_blue_description_text' => ['nullable', 'string', 'max:400'],
            'bifocal_ar_verde_foto_blue_description_text' => ['nullable', 'string', 'max:400'],
            'bifocal_159_transitions_description_text' => ['nullable', 'string', 'max:400'],
            'bifocal_159_blanco_description_text' => ['nullable', 'string', 'max:400'],
            'bifocal_159_ar_verde_description_text' => ['nullable', 'string', 'max:400'],
            'bifocal_159_blue_block_description_text' => ['nullable', 'string', 'max:400'],
            'bifocal_159_foto_ar_blue_description_text' => ['nullable', 'string', 'max:400'],
            'bifocal_159_ar_verde_foto_blue_description_text' => ['nullable', 'string', 'max:400'],

            // icon images per module
            'mono_icon_image' => ['nullable', 'file', 'image', 'max:51200'],
            'bifocal_icon_image' => ['nullable', 'file', 'image', 'max:51200'],
            'ocupacional_icon_image' => ['nullable', 'file', 'image', 'max:51200'],
            'progresivo_icon_image' => ['nullable', 'file', 'image', 'max:51200'],

            // bifocal variant icon uploads
            'bifocal_blanco_icon' => ['nullable', 'file', 'image', 'max:51200'],
            'bifocal_ar_azul_icon' => ['nullable', 'file', 'image', 'max:51200'],
            'bifocal_ar_verde_icon' => ['nullable', 'file', 'image', 'max:51200'],
            'bifocal_ar_azul_foto_blue_icon' => ['nullable', 'file', 'image', 'max:51200'],
            'bifocal_ar_verde_foto_blue_icon' => ['nullable', 'file', 'image', 'max:51200'],
            'bifocal_159_transitions_icon' => ['nullable', 'file', 'image', 'max:51200'],
            'bifocal_159_blanco_icon' => ['nullable', 'file', 'image', 'max:51200'],
            'bifocal_159_ar_verde_icon' => ['nullable', 'file', 'image', 'max:51200'],
            'bifocal_159_blue_block_icon' => ['nullable', 'file', 'image', 'max:51200'],
            'bifocal_159_foto_ar_blue_icon' => ['nullable', 'file', 'image', 'max:51200'],
            'bifocal_159_ar_verde_foto_blue_icon' => ['nullable', 'file', 'image', 'max:51200'],

            // removals
            'remove_mono_image' => ['nullable', 'boolean'],
            'remove_bifocal_image' => ['nullable', 'boolean'],
            'remove_progresivo_basic_image' => ['nullable', 'boolean'],
            'remove_progresivo_premium_image' => ['nullable', 'boolean'],
            'remove_mono_icon_image' => ['nullable', 'boolean'],
            'remove_bifocal_icon_image' => ['nullable', 'boolean'],
            'remove_ocupacional_icon_image' => ['nullable', 'boolean'],
            'remove_progresivo_icon_image' => ['nullable', 'boolean'],
            'remove_bifocal_blanco_icon_image' => ['nullable', 'boolean'],
            'remove_bifocal_ar_azul_icon_image' => ['nullable', 'boolean'],
            'remove_bifocal_ar_verde_icon_image' => ['nullable', 'boolean'],
            'remove_bifocal_ar_azul_foto_blue_icon_image' => ['nullable', 'boolean'],
            'remove_bifocal_ar_verde_foto_blue_icon_image' => ['nullable', 'boolean'],
            'remove_bifocal_159_transitions_icon_image' => ['nullable', 'boolean'],
            'remove_bifocal_159_blanco_icon_image' => ['nullable', 'boolean'],
            'remove_bifocal_159_ar_verde_icon_image' => ['nullable', 'boolean'],
            'remove_bifocal_159_blue_block_icon_image' => ['nullable', 'boolean'],
            'remove_bifocal_159_foto_ar_blue_icon_image' => ['nullable', 'boolean'],
            'remove_bifocal_159_ar_verde_foto_blue_icon_image' => ['nullable', 'boolean'],
            'default_lente_image' => ['nullable', 'file', 'image', 'max:51200'],
            'remove_default_lente_image' => ['nullable', 'boolean'],
            // nara images
            'nara_basica_image' => ['nullable', 'file', 'image', 'max:51200'],
            'nara_media_image' => ['nullable', 'file', 'image', 'max:51200'],
            'nara_alta_image' => ['nullable', 'file', 'image', 'max:51200'],
            'nara_premium_image' => ['nullable', 'file', 'image', 'max:51200'],
            'remove_nara_basica_image' => ['nullable', 'boolean'],
            'remove_nara_media_image' => ['nullable', 'boolean'],
            'remove_nara_alta_image' => ['nullable', 'boolean'],
            'remove_nara_premium_image' => ['nullable', 'boolean'],
        ]);

        $removeMono = $request->boolean('remove_mono_image');
        $removeBifocal = $request->boolean('remove_bifocal_image');
        $removeProgresivoBasic = $request->boolean('remove_progresivo_basic_image');
        $removeProgresivoPremium = $request->boolean('remove_progresivo_premium_image');

        $block = BloqueContenido::query()->firstOrCreate(
            ['clave' => GafasFormulasContent::BLOCK_KEY],
            [
                'titulo' => 'Gafas: imágenes de fórmulas',
                'cuerpo' => null,
                'datos' => GafasFormulasContent::defaults(),
                'esta_activo' => true,
                'orden' => 10,
            ]
        );

        $block->datos = array_merge(
            is_array($block->datos) ? $block->datos : [],
            [
                GafasFormulasContent::DATA_MONO_DESCRIPTION => trim((string) ($validated['mono_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_MONO_DESCRIPTION],
                GafasFormulasContent::DATA_BIFOCAL_DESCRIPTION => trim((string) ($validated['bifocal_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_BIFOCAL_DESCRIPTION],
                GafasFormulasContent::DATA_OCUPACIONAL_DESCRIPTION => trim((string) ($validated['ocupacional_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_OCUPACIONAL_DESCRIPTION],
                GafasFormulasContent::DATA_PROGRESIVO_DESCRIPTION => trim((string) ($validated['progresivo_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_PROGRESIVO_DESCRIPTION],
                GafasFormulasContent::DATA_BIFOCAL_BLANCO_DESCRIPTION => trim((string) ($validated['bifocal_blanco_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_BIFOCAL_BLANCO_DESCRIPTION],
                GafasFormulasContent::DATA_BIFOCAL_AR_AZUL_DESCRIPTION => trim((string) ($validated['bifocal_ar_azul_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_BIFOCAL_AR_AZUL_DESCRIPTION],
                GafasFormulasContent::DATA_BIFOCAL_AR_VERDE_DESCRIPTION => trim((string) ($validated['bifocal_ar_verde_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_BIFOCAL_AR_VERDE_DESCRIPTION],
                GafasFormulasContent::DATA_BIFOCAL_AR_AZUL_FOTO_BLUE_DESCRIPTION => trim((string) ($validated['bifocal_ar_azul_foto_blue_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_BIFOCAL_AR_AZUL_FOTO_BLUE_DESCRIPTION],
                GafasFormulasContent::DATA_BIFOCAL_AR_VERDE_FOTO_BLUE_DESCRIPTION => trim((string) ($validated['bifocal_ar_verde_foto_blue_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_BIFOCAL_AR_VERDE_FOTO_BLUE_DESCRIPTION],
                GafasFormulasContent::DATA_BIFOCAL_159_TRANSITIONS_DESCRIPTION => trim((string) ($validated['bifocal_159_transitions_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_BIFOCAL_159_TRANSITIONS_DESCRIPTION],
                GafasFormulasContent::DATA_BIFOCAL_159_BLANCO_DESCRIPTION => trim((string) ($validated['bifocal_159_blanco_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_BIFOCAL_159_BLANCO_DESCRIPTION],
                GafasFormulasContent::DATA_BIFOCAL_159_AR_VERDE_DESCRIPTION => trim((string) ($validated['bifocal_159_ar_verde_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_BIFOCAL_159_AR_VERDE_DESCRIPTION],
                GafasFormulasContent::DATA_BIFOCAL_159_BLUE_BLOCK_DESCRIPTION => trim((string) ($validated['bifocal_159_blue_block_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_BIFOCAL_159_BLUE_BLOCK_DESCRIPTION],
                GafasFormulasContent::DATA_BIFOCAL_159_FOTO_AR_BLUE_DESCRIPTION => trim((string) ($validated['bifocal_159_foto_ar_blue_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_BIFOCAL_159_FOTO_AR_BLUE_DESCRIPTION],
                GafasFormulasContent::DATA_BIFOCAL_159_AR_VERDE_FOTO_BLUE_DESCRIPTION => trim((string) ($validated['bifocal_159_ar_verde_foto_blue_description_text'] ?? '')) ?: GafasFormulasContent::defaults()[GafasFormulasContent::DATA_BIFOCAL_159_AR_VERDE_FOTO_BLUE_DESCRIPTION],
            ]
        );
        $block->save();

        // legacy single images
        if ($request->hasFile('mono_image_file')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_MONO);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_MONO, [$request->file('mono_image_file')]);
        } elseif ($removeMono) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_MONO);
        }

        if ($request->hasFile('bifocal_image_file')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL, [$request->file('bifocal_image_file')]);
        } elseif ($removeBifocal) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL);
        }

        if ($request->hasFile('progresivo_basic_image_file')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_PROGRESIVO_BASIC);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_PROGRESIVO_BASIC, [$request->file('progresivo_basic_image_file')]);
        } elseif ($removeProgresivoBasic) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_PROGRESIVO_BASIC);
        }

        if ($request->hasFile('progresivo_premium_image_file')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_PROGRESIVO_PREMIUM);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_PROGRESIVO_PREMIUM, [$request->file('progresivo_premium_image_file')]);
        } elseif ($removeProgresivoPremium) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_PROGRESIVO_PREMIUM);
        }

        // ocupacional main image
        if ($request->hasFile('ocupacional_image_file')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_OCUPACIONAL);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_OCUPACIONAL, [$request->file('ocupacional_image_file')]);
        }

        // default lente image (global fallback)
        if ($request->hasFile('default_lente_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_DEFAULT_LENTE);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_DEFAULT_LENTE, [$request->file('default_lente_image')]);
        } elseif ($request->boolean('remove_default_lente_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_DEFAULT_LENTE);
        }

        // icons per module
        if ($request->hasFile('mono_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_MONO_ICON);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_MONO_ICON, [$request->file('mono_icon_image')]);
        } elseif ($request->boolean('remove_mono_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_MONO_ICON);
        }

        if ($request->hasFile('bifocal_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_ICON);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_ICON, [$request->file('bifocal_icon_image')]);
        } elseif ($request->boolean('remove_bifocal_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_ICON);
        }

        if ($request->hasFile('ocupacional_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_OCUPACIONAL_ICON);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_OCUPACIONAL_ICON, [$request->file('ocupacional_icon_image')]);
        } elseif ($request->boolean('remove_ocupacional_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_OCUPACIONAL_ICON);
        }

        if ($request->hasFile('progresivo_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_PROGRESIVO_ICON);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_PROGRESIVO_ICON, [$request->file('progresivo_icon_image')]);
        } elseif ($request->boolean('remove_progresivo_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_PROGRESIVO_ICON);
        }

        // bifocal variant icons
        if ($request->hasFile('bifocal_blanco_icon')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_BLANCO);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_BLANCO, [$request->file('bifocal_blanco_icon')]);
        } elseif ($request->boolean('remove_bifocal_blanco_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_BLANCO);
        }

        if ($request->hasFile('bifocal_ar_azul_icon')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_AR_AZUL);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_AR_AZUL, [$request->file('bifocal_ar_azul_icon')]);
        } elseif ($request->boolean('remove_bifocal_ar_azul_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_AR_AZUL);
        }

        if ($request->hasFile('bifocal_ar_verde_icon')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_AR_VERDE);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_AR_VERDE, [$request->file('bifocal_ar_verde_icon')]);
        } elseif ($request->boolean('remove_bifocal_ar_verde_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_AR_VERDE);
        }

        if ($request->hasFile('bifocal_ar_azul_foto_blue_icon')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_AR_AZUL_FOTO_BLUE);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_AR_AZUL_FOTO_BLUE, [$request->file('bifocal_ar_azul_foto_blue_icon')]);
        } elseif ($request->boolean('remove_bifocal_ar_azul_foto_blue_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_AR_AZUL_FOTO_BLUE);
        }

        if ($request->hasFile('bifocal_ar_verde_foto_blue_icon')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_AR_VERDE_FOTO_BLUE);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_AR_VERDE_FOTO_BLUE, [$request->file('bifocal_ar_verde_foto_blue_icon')]);
        } elseif ($request->boolean('remove_bifocal_ar_verde_foto_blue_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_AR_VERDE_FOTO_BLUE);
        }

        if ($request->hasFile('bifocal_159_transitions_icon')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_TRANSITIONS);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_TRANSITIONS, [$request->file('bifocal_159_transitions_icon')]);
        } elseif ($request->boolean('remove_bifocal_159_transitions_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_TRANSITIONS);
        }

        if ($request->hasFile('bifocal_159_blanco_icon')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_BLANCO);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_BLANCO, [$request->file('bifocal_159_blanco_icon')]);
        } elseif ($request->boolean('remove_bifocal_159_blanco_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_BLANCO);
        }

        if ($request->hasFile('bifocal_159_ar_verde_icon')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_AR_VERDE);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_AR_VERDE, [$request->file('bifocal_159_ar_verde_icon')]);
        } elseif ($request->boolean('remove_bifocal_159_ar_verde_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_AR_VERDE);
        }

        if ($request->hasFile('bifocal_159_blue_block_icon')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_BLUE_BLOCK);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_BLUE_BLOCK, [$request->file('bifocal_159_blue_block_icon')]);
        } elseif ($request->boolean('remove_bifocal_159_blue_block_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_BLUE_BLOCK);
        }

        if ($request->hasFile('bifocal_159_foto_ar_blue_icon')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_FOTO_AR_BLUE);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_FOTO_AR_BLUE, [$request->file('bifocal_159_foto_ar_blue_icon')]);
        } elseif ($request->boolean('remove_bifocal_159_foto_ar_blue_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_FOTO_AR_BLUE);
        }

        if ($request->hasFile('bifocal_159_ar_verde_foto_blue_icon')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_AR_VERDE_FOTO_BLUE);
            $this->appendContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_AR_VERDE_FOTO_BLUE, [$request->file('bifocal_159_ar_verde_foto_blue_icon')]);
        } elseif ($request->boolean('remove_bifocal_159_ar_verde_foto_blue_icon_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_BIFOCAL_159_AR_VERDE_FOTO_BLUE);
        }

        // Nara category images: store under public/imagenes_formulas
        $storeNaraFile = function ($file) {
            if (! $file || ! $file->isValid()) return null;

            $extension = match (strtolower((string) ($file->getMimeType() ?: ''))) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'image/svg+xml' => 'svg',
                default => $file->guessExtension() ?: 'bin',
            };

            $storedPath = 'imagenes_formulas/' . Str::uuid() . '.' . $extension;
            Storage::disk('public')->put($storedPath, (string) file_get_contents($file->getRealPath() ?: $file->getPathname()));
            return [$storedPath, $file];
        };

        if ($request->hasFile('nara_basica_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_NARA_BASICA);
            $res = $storeNaraFile($request->file('nara_basica_image'));
            if ($res) {
                [$storedPath, $file] = $res;
                $block->archivos()->create([
                    'field_key' => GafasFormulasContent::FIELD_NARA_BASICA,
                    'orden' => 1,
                    'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                    'original_name' => $file->getClientOriginalName() ?: null,
                    'size_bytes' => $file->getSize(),
                    'contenido_base64' => null,
                    'ruta_archivo' => $storedPath,
                ]);
            }
        } elseif ($request->boolean('remove_nara_basica_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_NARA_BASICA);
        }

        if ($request->hasFile('nara_media_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_NARA_MEDIA);
            $res = $storeNaraFile($request->file('nara_media_image'));
            if ($res) {
                [$storedPath, $file] = $res;
                $block->archivos()->create([
                    'field_key' => GafasFormulasContent::FIELD_NARA_MEDIA,
                    'orden' => 1,
                    'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                    'original_name' => $file->getClientOriginalName() ?: null,
                    'size_bytes' => $file->getSize(),
                    'contenido_base64' => null,
                    'ruta_archivo' => $storedPath,
                ]);
            }
        } elseif ($request->boolean('remove_nara_media_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_NARA_MEDIA);
        }

        if ($request->hasFile('nara_alta_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_NARA_ALTA);
            $res = $storeNaraFile($request->file('nara_alta_image'));
            if ($res) {
                [$storedPath, $file] = $res;
                $block->archivos()->create([
                    'field_key' => GafasFormulasContent::FIELD_NARA_ALTA,
                    'orden' => 1,
                    'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                    'original_name' => $file->getClientOriginalName() ?: null,
                    'size_bytes' => $file->getSize(),
                    'contenido_base64' => null,
                    'ruta_archivo' => $storedPath,
                ]);
            }
        } elseif ($request->boolean('remove_nara_alta_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_NARA_ALTA);
        }

        if ($request->hasFile('nara_premium_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_NARA_PREMIUM);
            $res = $storeNaraFile($request->file('nara_premium_image'));
            if ($res) {
                [$storedPath, $file] = $res;
                $block->archivos()->create([
                    'field_key' => GafasFormulasContent::FIELD_NARA_PREMIUM,
                    'orden' => 1,
                    'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                    'original_name' => $file->getClientOriginalName() ?: null,
                    'size_bytes' => $file->getSize(),
                    'contenido_base64' => null,
                    'ruta_archivo' => $storedPath,
                ]);
            }
        } elseif ($request->boolean('remove_nara_premium_image')) {
            $this->clearContentBlockImages($block, GafasFormulasContent::FIELD_NARA_PREMIUM);
        }

        return redirect()
            ->route('dashboard.formula-images')
                ->with('status', 'Imágenes y textos de fórmulas actualizados.');
    }

    public function updateCompanyNotificationEmails(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'emails_text' => ['nullable', 'string', 'max:4000'],
        ]);

        $raw = trim((string) ($validated['emails_text'] ?? ''));
        $pieces = preg_split('/[\r\n,;]+/', $raw) ?: [];

        $emails = [];
        foreach ($pieces as $piece) {
            $normalized = mb_strtolower(trim((string) $piece));
            if ($normalized === '') {
                continue;
            }

            if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
                return redirect()
                    ->route('configuracion.index')
                    ->withInput()
                    ->withErrors([
                        'emails_text' => "Correo no valido: {$normalized}",
                    ]);
            }

            $emails[] = $normalized;
            if (count($emails) >= 20) {
                break;
            }
        }

        CompanyNotificationEmailsContent::upsert([
            'emails' => array_values(array_unique($emails)),
        ]);

        return redirect()
            ->route('configuracion.index')
            ->with('status', 'Correos de empresa actualizados.');
    }

    public function editLandingBenefitStrip()
    {
        return view('admin.landing_benefit_strip', [
            'landingBenefitStrip' => LandingBenefitStripContent::load(),
        ]);
    }

    public function updateLandingBenefitStrip(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items_text' => ['nullable', 'string', 'max:2500'],
        ]);

        $raw = trim((string) ($validated['items_text'] ?? ''));
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $items = [];

        foreach ($lines as $line) {
            $clean = trim((string) $line);
            $clean = ltrim($clean, "-•\t ");
            $clean = trim($clean);
            if ($clean === '') {
                continue;
            }

            $items[] = $clean;
            if (count($items) >= 12) {
                break;
            }
        }

        LandingBenefitStripContent::upsert([
            'items' => $items,
        ]);

        return redirect()->route('configuracion.landing-benefit-strip.edit')->with('status', 'Franja de beneficios actualizada.');
    }

    public function editLandingCategoryPhotos()
    {
        return view('admin.landing_category_photos', [
            'landingCategoryPhotos' => LandingCategoryPhotosContent::load(),
        ]);
    }

    public function updateLandingCategoryPhotos(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ninos_image_file' => ['nullable', 'file', 'image', 'max:51200'],
            'mujeres_image_file' => ['nullable', 'file', 'image', 'max:51200'],
            'hombres_image_file' => ['nullable', 'file', 'image', 'max:51200'],
            'remove_ninos_image' => ['nullable', 'boolean'],
            'remove_mujeres_image' => ['nullable', 'boolean'],
            'remove_hombres_image' => ['nullable', 'boolean'],
        ]);

        $removeNinos = $request->boolean('remove_ninos_image');
        $removeMujeres = $request->boolean('remove_mujeres_image');
        $removeHombres = $request->boolean('remove_hombres_image');

        $block = BloqueContenido::query()->firstOrCreate(
            ['clave' => LandingCategoryPhotosContent::BLOCK_KEY],
            [
                'titulo' => 'Landing: fotos de categorias',
                'cuerpo' => null,
                'datos' => [],
                'esta_activo' => true,
                'orden' => 3,
            ]
        );

        if ($request->hasFile('ninos_image_file')) {
            $this->clearContentBlockImages($block, LandingCategoryPhotosContent::FIELD_NINOS);
            $this->appendContentBlockImages($block, LandingCategoryPhotosContent::FIELD_NINOS, [$request->file('ninos_image_file')]);
        } elseif ($removeNinos) {
            $this->clearContentBlockImages($block, LandingCategoryPhotosContent::FIELD_NINOS);
        }

        if ($request->hasFile('mujeres_image_file')) {
            $this->clearContentBlockImages($block, LandingCategoryPhotosContent::FIELD_MUJERES);
            $this->appendContentBlockImages($block, LandingCategoryPhotosContent::FIELD_MUJERES, [$request->file('mujeres_image_file')]);
        } elseif ($removeMujeres) {
            $this->clearContentBlockImages($block, LandingCategoryPhotosContent::FIELD_MUJERES);
        }

        if ($request->hasFile('hombres_image_file')) {
            $this->clearContentBlockImages($block, LandingCategoryPhotosContent::FIELD_HOMBRES);
            $this->appendContentBlockImages($block, LandingCategoryPhotosContent::FIELD_HOMBRES, [$request->file('hombres_image_file')]);
        } elseif ($removeHombres) {
            $this->clearContentBlockImages($block, LandingCategoryPhotosContent::FIELD_HOMBRES);
        }

        return redirect()->route('configuracion.landing-category-photos.edit')->with('status', 'Fotos de categorías actualizadas.');
    }

    public function editLandingBenefits()
    {
        return view('admin.landing_benefits', [
            'landingBenefits' => LandingBenefitsContent::load(),
        ]);
    }

    public function updateLandingBenefits(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_0_title' => ['required', 'string', 'max:60'],
            'item_0_desc' => ['required', 'string', 'max:120'],
            'item_1_title' => ['required', 'string', 'max:60'],
            'item_1_desc' => ['required', 'string', 'max:120'],
            'item_2_title' => ['required', 'string', 'max:60'],
            'item_2_desc' => ['required', 'string', 'max:120'],
            'item_3_title' => ['required', 'string', 'max:60'],
            'item_3_desc' => ['required', 'string', 'max:120'],
        ]);

        $payload = [
            'items' => [
                ['title' => $validated['item_0_title'], 'desc' => $validated['item_0_desc']],
                ['title' => $validated['item_1_title'], 'desc' => $validated['item_1_desc']],
                ['title' => $validated['item_2_title'], 'desc' => $validated['item_2_desc']],
                ['title' => $validated['item_3_title'], 'desc' => $validated['item_3_desc']],
            ],
        ];

        LandingBenefitsContent::upsert($payload);

        return redirect()->route('configuracion.landing-benefits.edit')->with('status', 'Beneficios del landing actualizados.');
    }

    public function editLandingCategories()
    {
        return view('admin.landing_categories', [
            'landingCategories' => LandingCategoriesContent::load(),
        ]);
    }

    public function updateLandingCategories(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:60'],
            'subtitle' => ['required', 'string', 'max:160'],
            'top_link_text' => ['required', 'string', 'max:40'],
            'top_link_href' => ['required', 'string', 'max:2048'],

            'item_0_title' => ['required', 'string', 'max:60'],
            'item_0_desc' => ['required', 'string', 'max:120'],
            'item_0_href' => ['required', 'string', 'max:2048'],
            'item_0_image_url' => ['nullable', 'string', 'max:2048'],
            'item_0_uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'item_0_clear_uploaded_image' => ['nullable', 'boolean'],
            'item_0_image_pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_0_image_pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_0_image_zoom' => ['nullable', 'numeric', 'min:0.5', 'max:1.5'],

            'item_1_title' => ['required', 'string', 'max:60'],
            'item_1_desc' => ['required', 'string', 'max:120'],
            'item_1_href' => ['required', 'string', 'max:2048'],
            'item_1_image_url' => ['nullable', 'string', 'max:2048'],
            'item_1_uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'item_1_clear_uploaded_image' => ['nullable', 'boolean'],
            'item_1_image_pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_1_image_pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_1_image_zoom' => ['nullable', 'numeric', 'min:0.5', 'max:1.5'],

            'item_2_title' => ['required', 'string', 'max:60'],
            'item_2_desc' => ['required', 'string', 'max:120'],
            'item_2_href' => ['required', 'string', 'max:2048'],
            'item_2_image_url' => ['nullable', 'string', 'max:2048'],
            'item_2_uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'item_2_clear_uploaded_image' => ['nullable', 'boolean'],
            'item_2_image_pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_2_image_pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_2_image_zoom' => ['nullable', 'numeric', 'min:0.5', 'max:1.5'],

            'item_3_title' => ['required', 'string', 'max:60'],
            'item_3_desc' => ['required', 'string', 'max:120'],
            'item_3_href' => ['required', 'string', 'max:2048'],
            'item_3_image_url' => ['nullable', 'string', 'max:2048'],
            'item_3_uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'item_3_clear_uploaded_image' => ['nullable', 'boolean'],
            'item_3_image_pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_3_image_pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_3_image_zoom' => ['nullable', 'numeric', 'min:0.5', 'max:1.5'],
        ]);

        $hrefFields = [
            'top_link_href',
            'item_0_href',
            'item_1_href',
            'item_2_href',
            'item_3_href',
        ];

        foreach ($hrefFields as $field) {
            if (!$this->validateLandingHref((string) $validated[$field])) {
                return back()
                    ->withErrors([$field => 'URL inválida. Usa http(s), una ruta /... o un ancla #... (no javascript:).'])
                    ->withInput();
            }
        }

        for ($i = 0; $i < 4; $i++) {
            $imgKey = "item_{$i}_image_url";
            if (!$this->validateLandingImageSrc($validated[$imgKey] ?? null)) {
                return back()
                    ->withErrors([$imgKey => 'Imagen inválida. Usa http(s), o una ruta /... (máx 2048).'])
                    ->withInput();
            }
        }

        $payload = [
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'top_link_text' => $validated['top_link_text'],
            'top_link_href' => $validated['top_link_href'],
            'items' => [
                [
                    'title' => $validated['item_0_title'],
                    'desc' => $validated['item_0_desc'],
                    'href' => $validated['item_0_href'],
                    'image_url' => (string) ($validated['item_0_image_url'] ?? ''),
                    'image_pos_x' => (float) ($validated['item_0_image_pos_x'] ?? 50),
                    'image_pos_y' => (float) ($validated['item_0_image_pos_y'] ?? 50),
                    'image_zoom' => (float) ($validated['item_0_image_zoom'] ?? 1),
                ],
                [
                    'title' => $validated['item_1_title'],
                    'desc' => $validated['item_1_desc'],
                    'href' => $validated['item_1_href'],
                    'image_url' => (string) ($validated['item_1_image_url'] ?? ''),
                    'image_pos_x' => (float) ($validated['item_1_image_pos_x'] ?? 50),
                    'image_pos_y' => (float) ($validated['item_1_image_pos_y'] ?? 50),
                    'image_zoom' => (float) ($validated['item_1_image_zoom'] ?? 1),
                ],
                [
                    'title' => $validated['item_2_title'],
                    'desc' => $validated['item_2_desc'],
                    'href' => $validated['item_2_href'],
                    'image_url' => (string) ($validated['item_2_image_url'] ?? ''),
                    'image_pos_x' => (float) ($validated['item_2_image_pos_x'] ?? 50),
                    'image_pos_y' => (float) ($validated['item_2_image_pos_y'] ?? 50),
                    'image_zoom' => (float) ($validated['item_2_image_zoom'] ?? 1),
                ],
                [
                    'title' => $validated['item_3_title'],
                    'desc' => $validated['item_3_desc'],
                    'href' => $validated['item_3_href'],
                    'image_url' => (string) ($validated['item_3_image_url'] ?? ''),
                    'image_pos_x' => (float) ($validated['item_3_image_pos_x'] ?? 50),
                    'image_pos_y' => (float) ($validated['item_3_image_pos_y'] ?? 50),
                    'image_zoom' => (float) ($validated['item_3_image_zoom'] ?? 1),
                ],
            ],
        ];

        $block = LandingCategoriesContent::upsert($payload);

        for ($i = 0; $i < 4; $i++) {
            $fieldKey = "item_{$i}_image_url";
            $fileKey = "item_{$i}_uploaded_image";
            $clearKey = "item_{$i}_clear_uploaded_image";

            $uploadedFile = $request->file($fileKey);
            $clearUploaded = $request->boolean($clearKey);

            if ($clearUploaded || $uploadedFile) {
                $this->clearContentBlockImages($block, $fieldKey);
            }

            if ($uploadedFile) {
                $this->appendContentBlockImages($block, $fieldKey, [$uploadedFile]);
            }
        }

        return redirect()->route('configuracion.landing-categories.edit')->with('status', 'Categorías del landing actualizadas.');
    }

    public function editLandingPromoBanners()
    {
        return view('admin.landing_promo_banners', [
            'landingPromoBanners' => LandingPromoBannersContent::load(),
        ]);
    }

    public function editWhatsapp()
    {
        return view('admin.whatsapp', [
            'landingWhatsapp' => LandingWhatsappContent::load(),
        ]);
    }

    public function updateWhatsapp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone_number' => ['nullable', 'string', 'max:40'],
            'bubble_message' => ['nullable', 'string', 'max:80'],
            'icon_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $phone = trim((string) ($validated['phone_number'] ?? ''));
        if ($phone !== '' && !preg_match('/^[0-9+\-\s().]{1,40}$/', $phone)) {
            return back()
                ->withErrors(['phone_number' => 'Formato inválido. Usa solo números, espacios, +, guiones y paréntesis.'])
                ->withInput();
        }

        $bubble = trim((string) ($validated['bubble_message'] ?? ''));

        $iconUrl = trim((string) ($validated['icon_url'] ?? ''));
        if (!$this->validateLandingImageSrc($iconUrl)) {
            return back()
                ->withErrors(['icon_url' => 'Imagen inválida. Usa http(s), o una ruta /... (máx 2048).'])
                ->withInput();
        }

        LandingWhatsappContent::upsert([
            'phone_number' => $phone,
            'bubble_message' => $bubble,
            'icon_url' => $iconUrl,
        ]);

        return redirect()->route('configuracion.whatsapp.edit')->with('status', 'WhatsApp actualizado.');
    }

    public function updateLandingPromoBanners(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'promo_label' => ['required', 'string', 'max:24'],
            'promo_title' => ['required', 'string', 'max:80'],
            'promo_desc' => ['required', 'string', 'max:180'],
            'promo_cta_text' => ['required', 'string', 'max:40'],
            'promo_href' => ['required', 'string', 'max:2048'],
            'promo_image_url' => ['nullable', 'string', 'max:2048'],
            'promo_uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'promo_clear_uploaded_image' => ['nullable', 'boolean'],
            'promo_image_alt' => ['required', 'string', 'max:120'],

            'rec_label' => ['required', 'string', 'max:24'],
            'rec_title' => ['required', 'string', 'max:80'],
            'rec_desc' => ['required', 'string', 'max:180'],
            'rec_cta_text' => ['required', 'string', 'max:40'],
            'rec_href' => ['required', 'string', 'max:2048'],
            'rec_image_url' => ['nullable', 'string', 'max:2048'],
            'rec_uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'rec_clear_uploaded_image' => ['nullable', 'boolean'],
            'rec_image_alt' => ['required', 'string', 'max:120'],
        ]);

        $hrefFields = ['promo_href', 'rec_href'];
        foreach ($hrefFields as $field) {
            if (!$this->validateLandingHref((string) $validated[$field])) {
                return back()
                    ->withErrors([$field => 'URL inválida. Usa http(s), una ruta /... o un ancla #... (no javascript:).'])
                    ->withInput();
            }
        }

        $imgFields = ['promo_image_url', 'rec_image_url'];
        foreach ($imgFields as $field) {
            if (!$this->validateLandingImageSrc($validated[$field] ?? null)) {
                return back()
                    ->withErrors([$field => 'Imagen inválida. Usa http(s), o una ruta /... (máx 2048).'])
                    ->withInput();
            }
        }

        $payload = [
            'promo' => [
                'label' => $validated['promo_label'],
                'title' => $validated['promo_title'],
                'desc' => $validated['promo_desc'],
                'cta_text' => $validated['promo_cta_text'],
                'href' => $validated['promo_href'],
                'image_url' => (string) ($validated['promo_image_url'] ?? ''),
                'image_alt' => $validated['promo_image_alt'],
            ],
            'recommended' => [
                'label' => $validated['rec_label'],
                'title' => $validated['rec_title'],
                'desc' => $validated['rec_desc'],
                'cta_text' => $validated['rec_cta_text'],
                'href' => $validated['rec_href'],
                'image_url' => (string) ($validated['rec_image_url'] ?? ''),
                'image_alt' => $validated['rec_image_alt'],
            ],
        ];

        $block = LandingPromoBannersContent::upsert($payload);

        $promoUploadedFile = $request->file('promo_uploaded_image');
        $promoClearUploaded = $request->boolean('promo_clear_uploaded_image');
        if ($promoClearUploaded || $promoUploadedFile) {
            $this->clearContentBlockImages($block, LandingPromoBannersContent::PROMO_IMAGE_FIELD_KEY);
        }
        if ($promoUploadedFile) {
            $this->appendContentBlockImages($block, LandingPromoBannersContent::PROMO_IMAGE_FIELD_KEY, [$promoUploadedFile]);
        }

        $recUploadedFile = $request->file('rec_uploaded_image');
        $recClearUploaded = $request->boolean('rec_clear_uploaded_image');
        if ($recClearUploaded || $recUploadedFile) {
            $this->clearContentBlockImages($block, LandingPromoBannersContent::RECOMMENDED_IMAGE_FIELD_KEY);
        }
        if ($recUploadedFile) {
            $this->appendContentBlockImages($block, LandingPromoBannersContent::RECOMMENDED_IMAGE_FIELD_KEY, [$recUploadedFile]);
        }

        return redirect()->route('configuracion.landing-promo-banners.edit')->with('status', 'Banners promo del landing actualizados.');
    }

    public function editLandingServices()
    {
        return view('admin.landing_services', [
            'landingServices' => LandingServicesContent::load(),
        ]);
    }

    public function updateLandingServices(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:60'],
            'subtitle' => ['required', 'string', 'max:160'],
            'note_label' => ['required', 'string', 'max:30'],
            'note_text' => ['required', 'string', 'max:220'],

            'item_0_title' => ['required', 'string', 'max:60'],
            'item_0_desc' => ['required', 'string', 'max:160'],
            'item_0_href' => ['required', 'string', 'max:2048'],
            'item_0_image_url' => ['nullable', 'string', 'max:2048'],
            'item_0_uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'item_0_clear_uploaded_image' => ['nullable', 'boolean'],
            'item_0_image_alt' => ['required', 'string', 'max:120'],
            'item_0_image_pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_0_image_pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_0_image_zoom' => ['nullable', 'numeric', 'min:0.5', 'max:1.5'],

            'item_1_title' => ['required', 'string', 'max:60'],
            'item_1_desc' => ['required', 'string', 'max:160'],
            'item_1_href' => ['required', 'string', 'max:2048'],
            'item_1_image_url' => ['nullable', 'string', 'max:2048'],
            'item_1_uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'item_1_clear_uploaded_image' => ['nullable', 'boolean'],
            'item_1_image_alt' => ['required', 'string', 'max:120'],
            'item_1_image_pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_1_image_pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_1_image_zoom' => ['nullable', 'numeric', 'min:0.5', 'max:1.5'],

            'item_2_title' => ['required', 'string', 'max:60'],
            'item_2_desc' => ['required', 'string', 'max:160'],
            'item_2_href' => ['required', 'string', 'max:2048'],
            'item_2_image_url' => ['nullable', 'string', 'max:2048'],
            'item_2_uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'item_2_clear_uploaded_image' => ['nullable', 'boolean'],
            'item_2_image_alt' => ['required', 'string', 'max:120'],
            'item_2_image_pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_2_image_pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_2_image_zoom' => ['nullable', 'numeric', 'min:0.5', 'max:1.5'],

            'item_3_title' => ['required', 'string', 'max:60'],
            'item_3_desc' => ['required', 'string', 'max:160'],
            'item_3_href' => ['required', 'string', 'max:2048'],
            'item_3_image_url' => ['nullable', 'string', 'max:2048'],
            'item_3_uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'item_3_clear_uploaded_image' => ['nullable', 'boolean'],
            'item_3_image_alt' => ['required', 'string', 'max:120'],
            'item_3_image_pos_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_3_image_pos_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'item_3_image_zoom' => ['nullable', 'numeric', 'min:0.5', 'max:1.5'],
        ]);

        for ($i = 0; $i < 4; $i++) {
            $hrefKey = "item_{$i}_href";
            if (!$this->validateLandingHref((string) $validated[$hrefKey])) {
                return back()
                    ->withErrors([$hrefKey => 'URL inválida. Usa http(s), una ruta /... o un ancla #... (no javascript:).'])
                    ->withInput();
            }

            $imgKey = "item_{$i}_image_url";
            if (!$this->validateLandingImageSrc($validated[$imgKey] ?? null)) {
                return back()
                    ->withErrors([$imgKey => 'Imagen inválida. Usa http(s), o una ruta /... (máx 2048).'])
                    ->withInput();
            }
        }

        $items = [];
        for ($i = 0; $i < 4; $i++) {
            $items[] = [
                'title' => $validated["item_{$i}_title"],
                'desc' => $validated["item_{$i}_desc"],
                'href' => $validated["item_{$i}_href"],
                'image_url' => (string) ($validated["item_{$i}_image_url"] ?? ''),
                'image_alt' => $validated["item_{$i}_image_alt"],
                'image_pos_x' => (float) ($validated["item_{$i}_image_pos_x"] ?? 50),
                'image_pos_y' => (float) ($validated["item_{$i}_image_pos_y"] ?? 50),
                'image_zoom' => (float) ($validated["item_{$i}_image_zoom"] ?? 1),
            ];
        }

        $payload = [
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'note_label' => $validated['note_label'],
            'note_text' => $validated['note_text'],
            'items' => $items,
        ];

        $block = LandingServicesContent::upsert($payload);

        for ($i = 0; $i < 4; $i++) {
            $fieldKey = "item_{$i}_image_url";
            $fileKey = "item_{$i}_uploaded_image";
            $clearKey = "item_{$i}_clear_uploaded_image";

            $uploadedFile = $request->file($fileKey);
            $clearUploaded = $request->boolean($clearKey);

            if ($clearUploaded || $uploadedFile) {
                $this->clearContentBlockImages($block, $fieldKey);
            }

            if ($uploadedFile) {
                $this->appendContentBlockImages($block, $fieldKey, [$uploadedFile]);
            }
        }

        return redirect()->route('configuracion.landing-services.edit')->with('status', 'Servicios del landing actualizados.');
    }

    public function editLandingQuickGuide()
    {
        return view('admin.landing_quick_guide', [
            'landingQuickGuide' => LandingQuickGuideContent::load(),
        ]);
    }

    public function updateLandingQuickGuide(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:24'],
            'title' => ['required', 'string', 'max:80'],
            'desc' => ['required', 'string', 'max:240'],
            'cta_primary_text' => ['required', 'string', 'max:40'],
            'cta_primary_href' => ['required', 'string', 'max:2048'],
            'cta_secondary_text' => ['required', 'string', 'max:40'],
            'cta_secondary_href' => ['required', 'string', 'max:2048'],
            'step_0_k' => ['required', 'string', 'max:12'],
            'step_0_v' => ['required', 'string', 'max:24'],
            'step_1_k' => ['required', 'string', 'max:12'],
            'step_1_v' => ['required', 'string', 'max:24'],
            'step_2_k' => ['required', 'string', 'max:12'],
            'step_2_v' => ['required', 'string', 'max:24'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image_alt' => ['required', 'string', 'max:120'],
        ]);

        $hrefFields = ['cta_primary_href', 'cta_secondary_href'];
        foreach ($hrefFields as $field) {
            if (!$this->validateLandingHref((string) $validated[$field])) {
                return back()
                    ->withErrors([$field => 'URL inválida. Usa http(s), una ruta /... o un ancla #... (no javascript:).'])
                    ->withInput();
            }
        }

        if (!$this->validateLandingImageSrc($validated['image_url'] ?? null)) {
            return back()
                ->withErrors(['image_url' => 'Imagen inválida. Usa http(s), o una ruta /... (máx 2048).'])
                ->withInput();
        }

        $payload = [
            'label' => $validated['label'],
            'title' => $validated['title'],
            'desc' => $validated['desc'],
            'cta_primary_text' => $validated['cta_primary_text'],
            'cta_primary_href' => $validated['cta_primary_href'],
            'cta_secondary_text' => $validated['cta_secondary_text'],
            'cta_secondary_href' => $validated['cta_secondary_href'],
            'steps' => [
                ['k' => $validated['step_0_k'], 'v' => $validated['step_0_v']],
                ['k' => $validated['step_1_k'], 'v' => $validated['step_1_v']],
                ['k' => $validated['step_2_k'], 'v' => $validated['step_2_v']],
            ],
            'image_url' => (string) ($validated['image_url'] ?? ''),
            'image_alt' => $validated['image_alt'],
        ];

        LandingQuickGuideContent::upsert($payload);

        return redirect()->route('configuracion.landing-quick-guide.edit')->with('status', 'Guía rápida del landing actualizada.');
    }

    public function editLandingHighlights()
    {
        return view('admin.landing_highlights', [
            'landingHighlights' => LandingHighlightsContent::load(),
        ]);
    }

    public function updateLandingHighlights(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:60'],
            'subtitle' => ['required', 'string', 'max:160'],
            'tip_label' => ['required', 'string', 'max:30'],
            'tip_text' => ['required', 'string', 'max:220'],
        ]);

        // Ya no se editan las 4 cards desde el panel: se conservan como estén guardadas.
        $current = LandingHighlightsContent::load();
        $items = is_array($current['items'] ?? null) ? $current['items'] : [];

        $payload = [
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'tip_label' => $validated['tip_label'],
            'tip_text' => $validated['tip_text'],
            'items' => $items,
        ];

        LandingHighlightsContent::upsert($payload);

        return redirect()->route('configuracion.landing-highlights.edit')->with('status', 'Destacados del landing actualizados.');
    }

    public function editLandingHowItWorks()
    {
        return view('admin.landing_how_it_works', [
            'landingHowItWorks' => LandingHowItWorksContent::load(),
        ]);
    }

    public function updateLandingHowItWorks(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:60'],
            'subtitle' => ['required', 'string', 'max:200'],

            'step_0_title' => ['required', 'string', 'max:60'],
            'step_0_desc' => ['required', 'string', 'max:140'],
            'step_1_title' => ['required', 'string', 'max:60'],
            'step_1_desc' => ['required', 'string', 'max:140'],
            'step_2_title' => ['required', 'string', 'max:60'],
            'step_2_desc' => ['required', 'string', 'max:140'],
            'step_3_title' => ['required', 'string', 'max:60'],
            'step_3_desc' => ['required', 'string', 'max:140'],

            'cta_primary_text' => ['required', 'string', 'max:40'],
            'cta_primary_href' => ['required', 'string', 'max:2048'],
            'cta_secondary_text' => ['required', 'string', 'max:40'],
            'cta_secondary_href' => ['required', 'string', 'max:2048'],

            'image_url' => ['nullable', 'string', 'max:2048'],
            'uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'clear_uploaded_image' => ['nullable', 'boolean'],
            'image_alt' => ['required', 'string', 'max:120'],

            'stat_0_label' => ['required', 'string', 'max:40'],
            'stat_0_value' => ['required', 'string', 'max:60'],
            'stat_1_label' => ['required', 'string', 'max:40'],
            'stat_1_value' => ['required', 'string', 'max:60'],
        ]);

        $hrefFields = ['cta_primary_href', 'cta_secondary_href'];
        foreach ($hrefFields as $field) {
            if (!$this->validateLandingHref((string) $validated[$field])) {
                return back()
                    ->withErrors([$field => 'URL inválida. Usa http(s), una ruta /... o un ancla #... (no javascript:).'])
                    ->withInput();
            }
        }

        if (!$this->validateLandingImageSrc($validated['image_url'] ?? null)) {
            return back()
                ->withErrors(['image_url' => 'Imagen inválida. Usa http(s), o una ruta /... (máx 2048).'])
                ->withInput();
        }

        $payload = [
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'steps' => [
                ['title' => $validated['step_0_title'], 'desc' => $validated['step_0_desc']],
                ['title' => $validated['step_1_title'], 'desc' => $validated['step_1_desc']],
                ['title' => $validated['step_2_title'], 'desc' => $validated['step_2_desc']],
                ['title' => $validated['step_3_title'], 'desc' => $validated['step_3_desc']],
            ],
            'cta_primary_text' => $validated['cta_primary_text'],
            'cta_primary_href' => $validated['cta_primary_href'],
            'cta_secondary_text' => $validated['cta_secondary_text'],
            'cta_secondary_href' => $validated['cta_secondary_href'],
            'image_url' => (string) ($validated['image_url'] ?? ''),
            'image_alt' => $validated['image_alt'],
            'stat_0_label' => $validated['stat_0_label'],
            'stat_0_value' => $validated['stat_0_value'],
            'stat_1_label' => $validated['stat_1_label'],
            'stat_1_value' => $validated['stat_1_value'],
        ];

        $block = LandingHowItWorksContent::upsert($payload);

        $uploadedFile = $request->file('uploaded_image');
        $clearUploaded = $request->boolean('clear_uploaded_image');

        if ($clearUploaded || $uploadedFile) {
            $this->clearContentBlockImages($block, LandingHowItWorksContent::IMAGE_FIELD_KEY);
        }

        if ($uploadedFile) {
            $this->appendContentBlockImages($block, LandingHowItWorksContent::IMAGE_FIELD_KEY, [$uploadedFile]);
        }

        return redirect()->route('configuracion.landing-how-it-works.edit')->with('status', 'Sección “Cómo funciona” actualizada.');
    }

    public function editLandingEssentialBenefits()
    {
        return view('admin.landing_essential_benefits', [
            'landingEssentialBenefits' => LandingEssentialBenefitsContent::load(),
        ]);
    }

    public function updateLandingEssentialBenefits(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:60'],
            'subtitle' => ['required', 'string', 'max:160'],

            'item_0_title' => ['required', 'string', 'max:60'],
            'item_0_desc' => ['required', 'string', 'max:140'],
            'item_1_title' => ['required', 'string', 'max:60'],
            'item_1_desc' => ['required', 'string', 'max:140'],
            'item_2_title' => ['required', 'string', 'max:60'],
            'item_2_desc' => ['required', 'string', 'max:140'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'items' => [
                ['title' => $validated['item_0_title'], 'desc' => $validated['item_0_desc']],
                ['title' => $validated['item_1_title'], 'desc' => $validated['item_1_desc']],
                ['title' => $validated['item_2_title'], 'desc' => $validated['item_2_desc']],
            ],
        ];

        LandingEssentialBenefitsContent::upsert($payload);

        return redirect()->route('configuracion.landing-essential-benefits.edit')->with('status', 'Sección “Beneficios” actualizada.');
    }

    public function editLandingAdminReady()
    {
        return view('admin.landing_admin_ready', [
            'landingAdminReady' => LandingAdminReadyContent::load(),
        ]);
    }

    public function updateLandingAdminReady(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable'],
            'title' => ['required', 'string', 'max:80'],
            'text' => ['required', 'string', 'max:260'],

            'module_0_label' => ['required', 'string', 'max:20'],
            'module_0_title' => ['required', 'string', 'max:40'],
            'module_0_desc' => ['required', 'string', 'max:120'],

            'module_1_label' => ['required', 'string', 'max:20'],
            'module_1_title' => ['required', 'string', 'max:40'],
            'module_1_desc' => ['required', 'string', 'max:120'],

            'image_url' => ['nullable', 'string', 'max:2048'],
            'image_alt' => ['required', 'string', 'max:120'],

            'block_0_label' => ['required', 'string', 'max:20'],
            'block_0_title' => ['required', 'string', 'max:30'],
            'block_1_label' => ['required', 'string', 'max:20'],
            'block_1_title' => ['required', 'string', 'max:30'],
            'block_2_label' => ['required', 'string', 'max:20'],
            'block_2_title' => ['required', 'string', 'max:30'],
        ]);

        if (!$this->validateLandingImageSrc($validated['image_url'] ?? null)) {
            return back()
                ->withErrors(['image_url' => 'Imagen inválida. Usa http(s), o una ruta /... (máx 2048).'])
                ->withInput();
        }

        $payload = [
            'enabled' => (bool) $request->boolean('enabled'),
            'title' => $validated['title'],
            'text' => $validated['text'],
            'modules' => [
                ['label' => $validated['module_0_label'], 'title' => $validated['module_0_title'], 'desc' => $validated['module_0_desc']],
                ['label' => $validated['module_1_label'], 'title' => $validated['module_1_title'], 'desc' => $validated['module_1_desc']],
            ],
            'image_url' => (string) ($validated['image_url'] ?? ''),
            'image_alt' => $validated['image_alt'],
            'blocks' => [
                ['label' => $validated['block_0_label'], 'title' => $validated['block_0_title']],
                ['label' => $validated['block_1_label'], 'title' => $validated['block_1_title']],
                ['label' => $validated['block_2_label'], 'title' => $validated['block_2_title']],
            ],
        ];

        LandingAdminReadyContent::upsert($payload);

        return redirect()->route('configuracion.landing-admin-ready.edit')->with('status', 'Sección “Listo para el panel admin” actualizada.');
    }

    public function editLandingFaq()
    {
        return view('admin.landing_faq', [
            'landingFaq' => LandingFaqContent::load(),
        ]);
    }

    public function updateLandingFaq(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'subtitle' => ['required', 'string', 'max:160'],

            'item_0_question' => ['required', 'string', 'max:140'],
            'item_0_answer' => ['required', 'string', 'max:800'],
            'item_1_question' => ['required', 'string', 'max:140'],
            'item_1_answer' => ['required', 'string', 'max:800'],
            'item_2_question' => ['required', 'string', 'max:140'],
            'item_2_answer' => ['required', 'string', 'max:800'],
            'item_3_question' => ['required', 'string', 'max:140'],
            'item_3_answer' => ['required', 'string', 'max:800'],
            'item_4_question' => ['required', 'string', 'max:140'],
            'item_4_answer' => ['required', 'string', 'max:800'],
        ]);

        $payload = [
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'items' => [
                ['question' => $validated['item_0_question'], 'answer' => $validated['item_0_answer']],
                ['question' => $validated['item_1_question'], 'answer' => $validated['item_1_answer']],
                ['question' => $validated['item_2_question'], 'answer' => $validated['item_2_answer']],
                ['question' => $validated['item_3_question'], 'answer' => $validated['item_3_answer']],
                ['question' => $validated['item_4_question'], 'answer' => $validated['item_4_answer']],
            ],
        ];

        LandingFaqContent::upsert($payload);

        return redirect()->route('configuracion.landing-faq.edit')->with('status', 'Preguntas frecuentes actualizadas.');
    }

    public function editLandingLocation()
    {
        $landingLocation = LandingLocationContent::load();
        $defaults = LandingLocationContent::defaults();
        $rawLocations = $landingLocation['locations'] ?? $defaults['locations'];
        $rawLocations = is_array($rawLocations) ? array_values($rawLocations) : $defaults['locations'];

        // Asegurar al menos 3 ubicaciones (las fijas)
        if (count($rawLocations) < 3) {
            $rawLocations = array_merge($rawLocations, array_slice($defaults['locations'], count($rawLocations), 3 - count($rawLocations)));
        }

        $baseLocations = [];
        // Cargar TODAS las ubicaciones, no solo 3
        foreach ($rawLocations as $i => $rawLoc) {
            $fallback = $defaults['locations'][$i] ?? $defaults['locations'][0];
            $loc = is_array($rawLoc) ? $rawLoc : $fallback;
            $name = $loc['address'] ?? $loc['venue_name'] ?? ($fallback['address'] ?? '');
            $lat = $loc['lat'] ?? $fallback['lat'];
            $lng = $loc['lng'] ?? $fallback['lng'];
            $zoom = $loc['zoom'] ?? $fallback['zoom'];
            $image = (string) ($loc['image_url'] ?? ($fallback['image_url'] ?? '/images/naratodo.png'));

            $baseLocations[] = [
                'name' => (string) $name,
                'description' => (string) ($loc['description'] ?? ''),
                'lat' => (string) $lat,
                'lng' => (string) $lng,
                'zoom' => (string) $zoom,
                'image_url' => $image,
            ];
        }

        return view('admin.landing_location', [
            'landingLocation' => $landingLocation,
            'baseLocations' => $baseLocations,
        ]);
    }

    public function updateLandingLocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locations' => ['required', 'array', 'min:3'],
            'locations.*.name' => ['required', 'string', 'max:80'],
            'locations.*.description' => ['nullable', 'string', 'max:180'],
            'locations.*.lat' => ['required', 'numeric', 'between:-90,90'],
            'locations.*.lng' => ['required', 'numeric', 'between:-180,180'],
            'locations.*.zoom' => ['required', 'integer', 'between:1,20'],
            'locations.*.image_file' => ['nullable', 'file', 'image', 'max:51200'],
            'locations.*.remove_image' => ['nullable', 'boolean'],
        ]);

        $current = LandingLocationContent::load();
        $currentLocations = is_array($current['locations'] ?? null) ? $current['locations'] : LandingLocationContent::defaults()['locations'];

        $block = BloqueContenido::query()->firstOrCreate(
            ['clave' => LandingLocationContent::BLOCK_KEY],
            [
                'titulo' => 'Landing: ubicacion',
                'cuerpo' => null,
                'datos' => [],
                'esta_activo' => true,
                'orden' => 11,
            ]
        );

        $locations = [];
        foreach ($validated['locations'] as $i => $loc) {
            $fieldKey = 'location_image_'.$i;
            if ($request->hasFile("locations.$i.image_file")) {
                $this->clearContentBlockImages($block, $fieldKey);
                $this->appendContentBlockImages($block, $fieldKey, [$request->file("locations.$i.image_file")]);
            } elseif ($request->boolean("locations.$i.remove_image")) {
                $this->clearContentBlockImages($block, $fieldKey);
            }

            $existing = is_array($currentLocations[$i] ?? null) ? $currentLocations[$i] : [];
            $name = trim((string) ($loc['name'] ?? ''));
            $fallbackImage = (string) ($existing['image_url'] ?? '/images/naratodo.png');

            $locations[] = [
                'venue_name' => $name,
                'address' => $name,
                'description' => trim((string) ($loc['description'] ?? '')),
                'hours' => '',
                'cta_primary_text' => '',
                'cta_primary_href' => '#',
                'cta_secondary_text' => '',
                'lat' => (float) $loc['lat'],
                'lng' => (float) $loc['lng'],
                'zoom' => (int) $loc['zoom'],
                'map_title' => 'Mapa '.$name,
                'map_caption_title' => '',
                'map_caption_subtitle' => '',
                'image_url' => $fallbackImage,
            ];
        }

        $payload = [
            'title' => (string) ($current['title'] ?? 'Ubicación'),
            'subtitle' => (string) ($current['subtitle'] ?? ''),
            'locations' => $locations,
        ];

        LandingLocationContent::upsert($payload);

        return redirect()->route('configuracion.landing-location.edit')->with('status', 'Sección “Ubicación” actualizada.');
    }

    public function editLandingNewsletter()
    {
        return view('admin.landing_newsletter', [
            'landingNewsletter' => LandingNewsletterContent::load(),
        ]);
    }

    public function updateLandingNewsletter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:80'],
            'subtitle' => ['required', 'string', 'max:200'],
            'email_label' => ['required', 'string', 'max:40'],
            'email_placeholder' => ['required', 'string', 'max:80'],
            'button_text' => ['required', 'string', 'max:30'],
            'note' => ['required', 'string', 'max:120'],
        ]);

        LandingNewsletterContent::upsert([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'email_label' => $validated['email_label'],
            'email_placeholder' => $validated['email_placeholder'],
            'button_text' => $validated['button_text'],
            'note' => $validated['note'],
        ]);

        return redirect()->route('configuracion.landing-newsletter.edit')->with('status', 'Sección “Newsletter” actualizada.');
    }

    public function editLandingContact()
    {
        return view('admin.landing_contact', [
            'landingContact' => LandingContactContent::load(),
        ]);
    }

    public function updateLandingContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:60'],
            'subtitle' => ['required', 'string', 'max:220'],

            'whatsapp_label' => ['required', 'string', 'max:20'],
            'whatsapp_value' => ['required', 'string', 'max:60'],
            'email_label' => ['required', 'string', 'max:20'],
            'email_value' => ['required', 'string', 'max:120'],
            'hours_label' => ['required', 'string', 'max:20'],
            'hours_value' => ['required', 'string', 'max:60'],

            'channel_0_label' => ['required', 'string', 'max:20'],
            'channel_0_title' => ['required', 'string', 'max:40'],
            'channel_0_desc' => ['required', 'string', 'max:140'],

            'channel_1_label' => ['required', 'string', 'max:20'],
            'channel_1_title' => ['required', 'string', 'max:40'],
            'channel_1_desc' => ['required', 'string', 'max:140'],

            'form_label' => ['required', 'string', 'max:60'],
            'form_placeholder' => ['required', 'string', 'max:80'],
            'form_button_text' => ['required', 'string', 'max:30'],
            'form_note' => ['required', 'string', 'max:140'],

            'image_url' => ['nullable', 'string', 'max:2048'],
            'uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'clear_uploaded_image' => ['nullable', 'boolean'],
            'image_alt' => ['required', 'string', 'max:120'],
        ]);

        if (!$this->validateLandingImageSrc($validated['image_url'] ?? null)) {
            return back()
                ->withErrors(['image_url' => 'Imagen inválida. Usa http(s), o una ruta /... (máx 2048).'])
                ->withInput();
        }

        $block = LandingContactContent::upsert([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'whatsapp_label' => $validated['whatsapp_label'],
            'whatsapp_value' => $validated['whatsapp_value'],
            'email_label' => $validated['email_label'],
            'email_value' => $validated['email_value'],
            'hours_label' => $validated['hours_label'],
            'hours_value' => $validated['hours_value'],
            'channels' => [
                ['label' => $validated['channel_0_label'], 'title' => $validated['channel_0_title'], 'desc' => $validated['channel_0_desc']],
                ['label' => $validated['channel_1_label'], 'title' => $validated['channel_1_title'], 'desc' => $validated['channel_1_desc']],
            ],
            'form_label' => $validated['form_label'],
            'form_placeholder' => $validated['form_placeholder'],
            'form_button_text' => $validated['form_button_text'],
            'form_note' => $validated['form_note'],
            'image_url' => (string) ($validated['image_url'] ?? ''),
            'image_alt' => $validated['image_alt'],
        ]);

        $uploadedFile = $request->file('uploaded_image');
        $clearUploaded = $request->boolean('clear_uploaded_image');

        if ($clearUploaded || $uploadedFile) {
            $this->clearContentBlockImages($block, LandingContactContent::IMAGE_FIELD_KEY);
        }

        if ($uploadedFile) {
            $this->appendContentBlockImages($block, LandingContactContent::IMAGE_FIELD_KEY, [$uploadedFile]);
        }

        return redirect()->route('configuracion.landing-contact.edit')->with('status', 'Sección “Contacto” actualizada.');
    }

    public function editLandingFooter()
    {
        return view('admin.landing_footer', [
            'landingFooter' => LandingFooterContent::load(),
        ]);
    }

    public function updateLandingFooter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:60'],
            'tagline' => ['required', 'string', 'max:160'],

            'image_url' => ['nullable', 'string', 'max:2048'],
            'uploaded_image' => ['nullable', 'file', 'image', 'max:51200'],
            'clear_uploaded_image' => ['nullable', 'boolean'],
            'image_alt' => ['required', 'string', 'max:120'],

            'notice_title' => ['required', 'string', 'max:30'],
            'notice_text' => ['required', 'string', 'max:220'],

            'services_title' => ['required', 'string', 'max:30'],
            'service_0_text' => ['required', 'string', 'max:60'],
            'service_0_href' => ['required', 'string', 'max:2048'],
            'service_1_text' => ['required', 'string', 'max:60'],
            'service_1_href' => ['required', 'string', 'max:2048'],
            'service_2_text' => ['required', 'string', 'max:60'],
            'service_2_href' => ['required', 'string', 'max:2048'],
            'service_3_text' => ['required', 'string', 'max:60'],
            'service_3_href' => ['required', 'string', 'max:2048'],

            'help_title' => ['required', 'string', 'max:30'],
            'help_0_text' => ['required', 'string', 'max:60'],
            'help_0_href' => ['required', 'string', 'max:2048'],
            'help_1_text' => ['required', 'string', 'max:60'],
            'help_1_href' => ['required', 'string', 'max:2048'],
            'help_2_text' => ['required', 'string', 'max:60'],
            'help_2_href' => ['required', 'string', 'max:2048'],
            'help_3_text' => ['required', 'string', 'max:60'],
            'help_3_href' => ['required', 'string', 'max:2048'],
            'help_4_text' => ['required', 'string', 'max:60'],
            'help_4_href' => ['required', 'string', 'max:2048'],

            'legal_title' => ['required', 'string', 'max:30'],
            'legal_0_text' => ['required', 'string', 'max:60'],
            'legal_0_href' => ['required', 'string', 'max:2048'],
            'legal_1_text' => ['required', 'string', 'max:60'],
            'legal_1_href' => ['required', 'string', 'max:2048'],
            'legal_2_text' => ['required', 'string', 'max:60'],
            'legal_2_href' => ['required', 'string', 'max:2048'],
            'legal_3_text' => ['required', 'string', 'max:60'],
            'legal_3_href' => ['required', 'string', 'max:2048'],

            'contact_label' => ['required', 'string', 'max:30'],
            'contact_phone' => ['required', 'string', 'max:60'],
            'contact_email' => ['required', 'string', 'max:120'],
        ]);

        if (!$this->validateLandingImageSrc($validated['image_url'] ?? null)) {
            return back()
                ->withErrors(['image_url' => 'Imagen inválida. Usa http(s), o una ruta /... (máx 2048).'])
                ->withInput();
        }

        $hrefFields = [
            'service_0_href', 'service_1_href', 'service_2_href', 'service_3_href',
            'help_0_href', 'help_1_href', 'help_2_href', 'help_3_href', 'help_4_href',
            'legal_0_href', 'legal_1_href', 'legal_2_href', 'legal_3_href',
        ];
        foreach ($hrefFields as $field) {
            if (!$this->validateLandingHref((string) ($validated[$field] ?? ''))) {
                return back()
                    ->withErrors([$field => 'Link inválido. Usa http(s), /ruta o #ancla (máx 2048).'])
                    ->withInput();
            }
        }

        $block = LandingFooterContent::upsert([
            'company_name' => $validated['company_name'],
            'tagline' => $validated['tagline'],
            'image_url' => (string) ($validated['image_url'] ?? ''),
            'image_alt' => $validated['image_alt'],
            'notice_title' => $validated['notice_title'],
            'notice_text' => $validated['notice_text'],
            'services_title' => $validated['services_title'],
            'services_links' => [
                ['text' => $validated['service_0_text'], 'href' => $validated['service_0_href']],
                ['text' => $validated['service_1_text'], 'href' => $validated['service_1_href']],
                ['text' => $validated['service_2_text'], 'href' => $validated['service_2_href']],
                ['text' => $validated['service_3_text'], 'href' => $validated['service_3_href']],
            ],
            'help_title' => $validated['help_title'],
            'help_links' => [
                ['text' => $validated['help_0_text'], 'href' => $validated['help_0_href']],
                ['text' => $validated['help_1_text'], 'href' => $validated['help_1_href']],
                ['text' => $validated['help_2_text'], 'href' => $validated['help_2_href']],
                ['text' => $validated['help_3_text'], 'href' => $validated['help_3_href']],
                ['text' => $validated['help_4_text'], 'href' => $validated['help_4_href']],
            ],
            'legal_title' => $validated['legal_title'],
            'legal_links' => [
                ['text' => $validated['legal_0_text'], 'href' => $validated['legal_0_href']],
                ['text' => $validated['legal_1_text'], 'href' => $validated['legal_1_href']],
                ['text' => $validated['legal_2_text'], 'href' => $validated['legal_2_href']],
                ['text' => $validated['legal_3_text'], 'href' => $validated['legal_3_href']],
            ],
            'contact_label' => $validated['contact_label'],
            'contact_phone' => $validated['contact_phone'],
            'contact_email' => $validated['contact_email'],
        ]);

        $uploadedFile = $request->file('uploaded_image');
        $clearUploaded = $request->boolean('clear_uploaded_image');

        if ($clearUploaded || $uploadedFile) {
            $this->clearContentBlockImages($block, LandingFooterContent::IMAGE_FIELD_KEY);
        }

        if ($uploadedFile) {
            $this->appendContentBlockImages($block, LandingFooterContent::IMAGE_FIELD_KEY, [$uploadedFile]);
        }

        return redirect()->route('configuracion.landing-footer.edit')->with('status', 'Sección “Footer” actualizada.');
    }

    public function editLandingFooterFaq()
    {
        return view('admin.landing_footer_faq', [
            'landingFooter' => LandingFooterContent::load(),
        ]);
    }

    public function updateLandingFooterFaq(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_email' => ['required', 'string', 'max:120', 'email'],
            'contact_phone' => ['required', 'string', 'max:60'],
            'faq' => ['required', 'array', 'min:1'],
            'faq.*.question' => ['required', 'string', 'max:200'],
            'faq.*.answer' => ['nullable', 'string', 'max:2000'],
            'about_0_text' => ['required', 'string', 'max:60'],
            'about_0_href' => ['required', 'string', 'max:2048'],
            'about_1_text' => ['required', 'string', 'max:60'],
            'about_1_href' => ['required', 'string', 'max:2048'],
            'about_2_text' => ['required', 'string', 'max:60'],
            'about_2_href' => ['required', 'string', 'max:2048'],
            'about_0_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:102400'],
            'about_1_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:102400'],
            'about_2_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:102400'],
            'clear_about_0_pdf' => ['nullable', 'boolean'],
            'clear_about_1_pdf' => ['nullable', 'boolean'],
            'clear_about_2_pdf' => ['nullable', 'boolean'],
            'social_0_href' => ['nullable', 'string', 'max:2048'],
            'social_1_href' => ['nullable', 'string', 'max:2048'],
            'social_2_href' => ['nullable', 'string', 'max:2048'],
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto válido.',
            'email' => 'El campo :attribute debe ser un correo válido.',
            'max' => 'El campo :attribute supera el tamaño permitido.',
            'array' => 'El campo :attribute debe ser una lista válida.',
            'min' => 'El campo :attribute no cumple el mínimo permitido.',
            'mimes' => 'El archivo de :attribute debe ser PDF.',
            'file' => 'El campo :attribute debe ser un archivo válido.',
            'boolean' => 'El campo :attribute debe ser verdadero o falso.',
        ], [
            'contact_email' => 'correo de contacto',
            'contact_phone' => 'teléfono de contacto',
            'faq' => 'preguntas frecuentes',
            'faq.*.question' => 'pregunta frecuente',
            'faq.*.answer' => 'respuesta frecuente',
            'about_0_text' => 'texto de la opción 1',
            'about_0_href' => 'enlace de la opción 1',
            'about_1_text' => 'texto de la opción 2',
            'about_1_href' => 'enlace de la opción 2',
            'about_2_text' => 'texto de la opción 3',
            'about_2_href' => 'enlace de la opción 3',
            'about_0_pdf' => 'PDF de la opción 1',
            'about_1_pdf' => 'PDF de la opción 2',
            'about_2_pdf' => 'PDF de la opción 3',
            'social_0_href' => 'enlace de Facebook',
            'social_1_href' => 'enlace de Instagram',
            'social_2_href' => 'enlace de TikTok',
        ]);

        $about0Href = $this->normalizeAboutHref((string) ($validated['about_0_href'] ?? ''));
        $about1Href = $this->normalizeAboutHref((string) ($validated['about_1_href'] ?? ''));
        $about2Href = $this->normalizeAboutHref((string) ($validated['about_2_href'] ?? ''));

        foreach (['about_0_href' => $about0Href, 'about_1_href' => $about1Href, 'about_2_href' => $about2Href] as $field => $href) {
            if (!$this->validateLandingHref($href)) {
                return back()->withErrors([$field => 'Link inválido.'])->withInput();
            }
        }

        $social0Href = $this->normalizeSocialHref((string) ($validated['social_0_href'] ?? ''));
        $social1Href = $this->normalizeSocialHref((string) ($validated['social_1_href'] ?? ''));
        $social2Href = $this->normalizeSocialHref((string) ($validated['social_2_href'] ?? ''));

        foreach (['social_0_href' => $social0Href, 'social_1_href' => $social1Href, 'social_2_href' => $social2Href] as $field => $socialHref) {
            if (!$this->validateLandingHref($socialHref)) {
                return back()->withErrors([$field => 'Link de red social inválido.'])->withInput();
            }
        }

        $defaults = LandingFooterContent::defaults();
        $current = LandingFooterContent::load();
        
        $block = LandingFooterContent::upsert([
            'company_name' => $current['company_name'],
            'tagline' => $current['tagline'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'],
            'faq' => $validated['faq'],
            'about_links' => [
                ['text' => $validated['about_0_text'], 'href' => $about0Href],
                ['text' => $validated['about_1_text'], 'href' => $about1Href],
                ['text' => $validated['about_2_text'], 'href' => $about2Href],
            ],
            'social_links' => [
                array_merge($defaults['social_links'][0], ['href' => $social0Href]),
                array_merge($defaults['social_links'][1], ['href' => $social1Href]),
                array_merge($defaults['social_links'][2], ['href' => $social2Href]),
            ],
        ]);

        for ($i = 0; $i < 3; $i++) {
            $fieldKey = LandingFooterContent::ABOUT_PDF_FIELD_PREFIX . $i;
            $fileInput = 'about_' . $i . '_pdf';
            $clearInput = 'clear_about_' . $i . '_pdf';

            if ($request->hasFile($fileInput)) {
                $this->replaceContentBlockFiles($block, $fieldKey, [$request->file($fileInput)]);
                continue;
            }

            if ($request->boolean($clearInput)) {
                $this->clearContentBlockImages($block, $fieldKey);
            }
        }

        return redirect()->route('configuracion.landing-footer-faq.edit')->with('status', 'FAQ y redes actualizadas.');
    }

    public function editLandingIntro()
    {
        return view('admin.landing_intro', [
            'landingIntro' => LandingIntroContent::load(),
        ]);
    }

    public function updateLandingIntro(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'badge' => ['required', 'string', 'max:120'],
            'title_prefix' => ['required', 'string', 'max:80'],
            'title_highlight' => ['required', 'string', 'max:80'],
            'title_suffix' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:600'],
            'cta_primary_text' => ['required', 'string', 'max:60'],
            'cta_secondary_text' => ['required', 'string', 'max:60'],
            'stats_0_label' => ['required', 'string', 'max:30'],
            'stats_0_value' => ['required', 'string', 'max:30'],
            'stats_1_label' => ['required', 'string', 'max:30'],
            'stats_1_value' => ['required', 'string', 'max:30'],
            'stats_2_label' => ['required', 'string', 'max:30'],
            'stats_2_value' => ['required', 'string', 'max:30'],
            'card_0_eyebrow' => ['required', 'string', 'max:40'],
            'card_0_title' => ['required', 'string', 'max:60'],
            'card_0_desc' => ['required', 'string', 'max:140'],
            'card_1_eyebrow' => ['required', 'string', 'max:40'],
            'card_1_title' => ['required', 'string', 'max:60'],
            'card_1_desc' => ['required', 'string', 'max:140'],
            'image_alt' => ['required', 'string', 'max:120'],
            'image_urls' => ['nullable', 'string', 'max:12000'],
            'uploaded_images' => ['nullable', 'array', 'max:6'],
            'uploaded_images.*' => ['file', 'image', 'max:51200'],
            'clear_uploaded_images' => ['nullable', 'boolean'],
            'replace_uploaded_images' => ['nullable', 'boolean'],
            'mini_0_eyebrow' => ['required', 'string', 'max:40'],
            'mini_0_title' => ['required', 'string', 'max:60'],
            'mini_1_eyebrow' => ['required', 'string', 'max:40'],
            'mini_1_title' => ['required', 'string', 'max:60'],
        ]);

        $urlsRaw = (string) ($validated['image_urls'] ?? '');
        $lines = preg_split('/\r\n|\r|\n/', $urlsRaw) ?: [];
        $urls = [];
        foreach ($lines as $line) {
            $url = trim((string) $line);
            if ($url === '') continue;
            if (! $this->validateLandingImageSrc($url)) {
                return back()
                    ->withErrors(['image_urls' => 'Cada línea debe ser una URL válida http(s) o ruta /... (máx 2048 caracteres).'])
                    ->withInput();
            }
            $urls[] = $url;
        }

        $payload = [
            'badge' => $validated['badge'],
            'title_prefix' => $validated['title_prefix'],
            'title_highlight' => $validated['title_highlight'],
            'title_suffix' => $validated['title_suffix'],
            'description' => $validated['description'],
            'cta_primary_text' => $validated['cta_primary_text'],
            'cta_primary_href' => '#categorias',
            'cta_secondary_text' => $validated['cta_secondary_text'],
            'cta_secondary_href' => '#beneficios',
            'stats' => [
                ['label' => $validated['stats_0_label'], 'value' => $validated['stats_0_value']],
                ['label' => $validated['stats_1_label'], 'value' => $validated['stats_1_value']],
                ['label' => $validated['stats_2_label'], 'value' => $validated['stats_2_value']],
            ],
            'cards' => [
                ['eyebrow' => $validated['card_0_eyebrow'], 'title' => $validated['card_0_title'], 'desc' => $validated['card_0_desc']],
                ['eyebrow' => $validated['card_1_eyebrow'], 'title' => $validated['card_1_title'], 'desc' => $validated['card_1_desc']],
            ],
            'image_alt' => $validated['image_alt'],
            'image_urls' => $urls,
            'mini_cards' => [
                ['eyebrow' => $validated['mini_0_eyebrow'], 'title' => $validated['mini_0_title']],
                ['eyebrow' => $validated['mini_1_eyebrow'], 'title' => $validated['mini_1_title']],
            ],
            'image_rotate_ms' => LandingIntroContent::defaults()['image_rotate_ms'],
        ];

        $block = LandingIntroContent::upsert($payload);

        $replaceUploadedImages = $request->boolean('replace_uploaded_images');
        $clearUploadedImages = $request->boolean('clear_uploaded_images');

        if ($replaceUploadedImages || $clearUploadedImages) {
            $this->clearContentBlockImages($block, LandingIntroContent::IMAGE_FIELD_KEY);
        }

        $uploadedImages = is_array($request->file('uploaded_images')) ? $request->file('uploaded_images') : [];
        if ($uploadedImages !== []) {
            $this->appendContentBlockImages($block, LandingIntroContent::IMAGE_FIELD_KEY, $uploadedImages);
        }

        return redirect()->route('configuracion.landing-intro.edit')->with('status', 'Contenido del bloque principal actualizado.');
    }
}
