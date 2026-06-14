<?php

namespace App\Http\Controllers;

use App\Models\Resena;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ResenaController extends Controller
{
    private const DELETE_ALLOWED_ROLES = [2, 3, 4];

    public function index(Request $request): View
    {
        $selectedStars = $request->integer('stars');
        if (! in_array($selectedStars, [1, 2, 3, 4, 5], true)) {
            $selectedStars = null;
        }

        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, [5, 10], true)) {
            $perPage = 10;
        }

        $reviewsAvg = 0.0;
        $reviewsCount = 0;
        $verifiedBuyerUserIds = [];

        $allReviews = Resena::query()
            ->with(['usuario:id,nombre'])
            ->when($selectedStars !== null, fn ($query) => $query->where('estrellas', $selectedStars))
            ->latest('id')
            ->simplePaginate($perPage)
            ->withQueryString();

        try {
            if (Schema::hasTable('resenas')) {
                $reviewsCount = (int) Resena::query()->count();
                $reviewsAvg = (float) (Resena::query()->avg('estrellas') ?? 0);
            }

            $reviewUserIds = $allReviews->getCollection()->pluck('usuario_id')->filter()->unique()->values();
            if ($reviewUserIds->isNotEmpty() && Schema::hasTable('pagos') && Schema::hasTable('carritos')) {
                $verifiedBuyerUserIds = DB::table('pagos')
                    ->join('carritos', 'pagos.carrito_id', '=', 'carritos.id')
                    ->where('pagos.estado', 'aprobado')
                    ->whereIn('carritos.usuario_id', $reviewUserIds->all())
                    ->distinct()
                    ->pluck('carritos.usuario_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }
        } catch (\Throwable $e) {
            $reviewsAvg = 0.0;
            $reviewsCount = 0;
            $verifiedBuyerUserIds = [];
        }

        return view('resenas.index', [
            'allReviews' => $allReviews,
            'reviewsAvg' => $reviewsAvg,
            'reviewsCount' => $reviewsCount,
            'verifiedBuyerUserIds' => $verifiedBuyerUserIds,
            'selectedStars' => $selectedStars,
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request)
    {
        $rateLimit = $this->reviewRateLimitConfig($request);
        $rateLimitKey = $this->reviewRateLimitKey($request);
        if (RateLimiter::tooManyAttempts($rateLimitKey, $rateLimit['max_attempts'])) {
            $retryAfter = max(1, (int) RateLimiter::availableIn($rateLimitKey));
            $retryMinutes = (int) ceil($retryAfter / 60);
            $retryLabel = $retryMinutes > 1 ? ($retryMinutes . ' minutos') : '1 minuto';
            $message = 'Has enviado muchas reseñas en poco tiempo. Intenta de nuevo en ' . $retryLabel . '.';

            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => false,
                    'message' => $message,
                    'retryAfterSeconds' => $retryAfter,
                ], 429);
            }

            return back()
                ->withInput($request->except('foto_archivo'))
                ->withErrors([
                    'comentario' => $message,
                ]);
        }

        RateLimiter::hit($rateLimitKey, $rateLimit['decay_seconds']);

        $postMaxBytes = $this->iniSizeToBytes((string) ini_get('post_max_size'));
        $contentLength = (int) ($request->server('CONTENT_LENGTH') ?? 0);
        if ($postMaxBytes > 0 && $contentLength > $postMaxBytes) {
            return back()
                ->withInput($request->except('foto_archivo'))
                ->withErrors([
                    'foto_archivo' => 'La imagen es demasiado pesada para el servidor. Reduce el tamaño e intenta de nuevo (máximo 25 MB).',
                ]);
        }

        $normalizedComment = (string) $request->input('comentario', '');
        $normalizedComment = preg_replace("/\r\n?|\n/", "\n", $normalizedComment) ?? $normalizedComment;
        $request->merge([
            'comentario' => $normalizedComment,
        ]);

        $validated = $request->validate([
            'autor_nombre' => ['required', 'string', 'max:120'],
            'estrellas' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['required', 'string', 'max:1000'],
            'foto_archivo' => ['nullable', 'file', 'image', 'max:25600', 'mimetypes:image/jpeg,image/png,image/webp,image/gif', 'mimes:jpg,jpeg,png,webp,gif'],
        ], [
            'foto_archivo.max' => 'La imagen supera el tamaño permitido. Máximo 25 MB.',
            'foto_archivo.image' => 'El archivo subido no es una imagen válida.',
            'foto_archivo.mimetypes' => 'La foto debe estar en formato JPG, PNG, WEBP o GIF.',
            'foto_archivo.mimes' => 'La extensión del archivo no está permitida. Usa JPG, PNG, WEBP o GIF.',
            'foto_archivo.uploaded' => 'No se pudo subir la foto. Es posible que sea demasiado pesada para el servidor.',
        ]);

        $sanitizedAuthor = $this->sanitizeReviewText((string) ($validated['autor_nombre'] ?? ''), false);
        $sanitizedComment = $this->sanitizeReviewText((string) ($validated['comentario'] ?? ''), true);

        if ($sanitizedAuthor === '') {
            throw ValidationException::withMessages([
                'autor_nombre' => 'Ingresa un nombre válido.',
            ]);
        }

        if ($sanitizedComment === '') {
            throw ValidationException::withMessages([
                'comentario' => 'Ingresa un comentario válido.',
            ]);
        }

        if ($this->containsUrl($sanitizedAuthor) || $this->containsUrl($sanitizedComment)) {
            throw ValidationException::withMessages([
                'comentario' => 'No se permiten URLs ni enlaces en las reseñas.',
            ]);
        }

        if ($this->containsSqlPayload($sanitizedAuthor) || $this->containsSqlPayload($sanitizedComment)) {
            throw ValidationException::withMessages([
                'comentario' => 'El contenido incluye patrones no permitidos.',
            ]);
        }

        $usuario = $request->user();
        $uploadedPhotoFile = $request->file('foto_archivo');

        if ($uploadedPhotoFile instanceof UploadedFile) {
            $photoValidationError = $this->validateUploadedPhotoContent($uploadedPhotoFile);
            if ($photoValidationError !== null) {
                throw ValidationException::withMessages([
                    'foto_archivo' => $photoValidationError,
                ]);
            }
        }

        $uploadedPhotoUrl = $this->storeUploadedPhoto($uploadedPhotoFile);
        $reviewData = [
            'autor_nombre' => $sanitizedAuthor,
            'estrellas' => (int) $validated['estrellas'],
            'comentario' => $sanitizedComment,
            'foto_url' => $uploadedPhotoUrl,
            'foto_data' => null,
            'foto_nombre' => null,
            'foto_mime' => null,
            'foto_size' => null,
        ];

        // Siempre crear una nueva reseña sin reemplazar
        if ($usuario) {
            $reviewData['usuario_id'] = (int) $usuario->id;
        }
        $resena = Resena::create($reviewData);

        $wantsJson = $request->expectsJson() || $request->wantsJson() || $request->ajax();
        if (! $wantsJson) {
            return back()->with('status', '¡Gracias! Tu reseña se guardó.');
        }

        $reviewsCount = 0;
        $reviewsAvg = 0;
        $latestReviews = collect();
        $verifiedBuyerUserIds = [];

        try {
            if (Schema::hasTable('resenas')) {
                $reviewsCount = (int) Resena::query()->count();
                $reviewsAvg = (float) (Resena::query()->avg('estrellas') ?? 0);
                $latestReviews = Resena::query()
                    ->with(['usuario:id,nombre'])
                    ->latest('id')
                    ->limit(12)
                    ->get();

                $reviewUserIds = $latestReviews->pluck('usuario_id')->filter()->unique()->values();
                if ($reviewUserIds->isNotEmpty() && Schema::hasTable('pagos') && Schema::hasTable('carritos')) {
                    $verifiedBuyerUserIds = DB::table('pagos')
                        ->join('carritos', 'pagos.carrito_id', '=', 'carritos.id')
                        ->where('pagos.estado', 'aprobado')
                        ->whereIn('carritos.usuario_id', $reviewUserIds->all())
                        ->distinct()
                        ->pluck('carritos.usuario_id')
                        ->map(fn ($id) => (int) $id)
                        ->all();
                }
            }
        } catch (\Throwable $e) {
            $reviewsCount = 0;
            $reviewsAvg = 0;
            $latestReviews = collect();
            $verifiedBuyerUserIds = [];
        }

        $cardsHtml = view('partials.reviews_cards', [
            'latestReviews' => $latestReviews,
            'verifiedBuyerUserIds' => $verifiedBuyerUserIds,
        ])->render();

        return response()->json([
            'ok' => true,
            'message' => '¡Gracias! Tu reseña se guardó.',
            'reviewsCount' => $reviewsCount,
            'reviewsAvg' => $reviewsAvg,
            'cardsHtml' => $cardsHtml,
            'savedReviewId' => (int) $resena->id,
        ]);
    }

    public function destroy(Request $request, Resena $resena)
    {
        $user = $request->user();
        $roleId = (int) ($user?->rol_id ?? 0);

        abort_unless($user && in_array($roleId, self::DELETE_ALLOWED_ROLES, true), 403);

        $photoUrl = trim((string) ($resena->foto_url ?? ''));
        if ($photoUrl !== '' && str_starts_with($photoUrl, '/storage/')) {
            $storagePath = ltrim(substr($photoUrl, strlen('/storage/')), '/');
            if ($storagePath !== '') {
                Storage::disk('public')->delete($storagePath);
            }
        }

        $resena->delete();

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Reseña eliminada correctamente.',
            ]);
        }

        return back()->with('status', 'Reseña eliminada correctamente.');
    }

    private function storeUploadedPhoto(?UploadedFile $file): ?string
    {
        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            return null;
        }

        $storedPath = $file->store('resenas', 'public');
        if (! is_string($storedPath) || trim($storedPath) === '') {
            return null;
        }

        return '/storage/' . ltrim(str_replace('\\', '/', $storedPath), '/');
    }

    private function iniSizeToBytes(string $value): int
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return 0;
        }

        if (! preg_match('/^([0-9]+)\s*([kmg])?$/i', $trimmed, $matches)) {
            return (int) $trimmed;
        }

        $bytes = (int) $matches[1];
        $unit = strtolower((string) ($matches[2] ?? ''));

        if ($unit === 'g') {
            return $bytes * 1024 * 1024 * 1024;
        }
        if ($unit === 'm') {
            return $bytes * 1024 * 1024;
        }
        if ($unit === 'k') {
            return $bytes * 1024;
        }

        return $bytes;
    }

    private function sanitizeReviewText(string $value, bool $allowNewLines): string
    {
        $text = strip_tags($value);
        if ($allowNewLines) {
            $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $text) ?? $text;
            $text = preg_replace('/\h+/u', ' ', $text) ?? $text;
            $text = preg_replace('/(?:\r\n?|\n){3,}/u', "\n\n", $text) ?? $text;
            $text = trim($text);
        } else {
            $text = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $text) ?? $text;
            $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
            $text = trim($text);
        }

        return $text;
    }

    private function containsUrl(string $value): bool
    {
        return preg_match('/(?:https?:\/\/|www\.|(?:[a-z0-9-]+\.)+[a-z]{2,})(?:\/[\w\-\.~:\/?#\[\]@!$&\'\(\)*+,;=%]*)?/iu', $value) === 1;
    }

    private function containsSqlPayload(string $value): bool
    {
        return preg_match('/(?:\bor\s+1\s*=\s*1\b|\bunion\s+select\b|\bselect\b.+\bfrom\b|\binsert\s+into\b|\bupdate\b.+\bset\b|\bdelete\s+from\b|\bdrop\s+table\b|\balter\s+table\b|\btruncate\s+table\b|--|\/\*|\*\/|;\s*(?:select|insert|update|delete|drop|alter|truncate)\b)/iu', $value) === 1;
    }

    private function validateUploadedPhotoContent(UploadedFile $file): ?string
    {
        if (! $file->isValid()) {
            return 'No se pudo procesar la imagen enviada.';
        }

        $realPath = $file->getRealPath();
        if (! is_string($realPath) || trim($realPath) === '' || ! is_file($realPath)) {
            return 'No se pudo leer la imagen subida.';
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $allowedImageTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo ? finfo_file($finfo, $realPath) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        if (! is_string($detectedMime) || ! in_array($detectedMime, $allowedMimeTypes, true)) {
            return 'El archivo no corresponde a una imagen permitida.';
        }

        $imageInfo = @getimagesize($realPath);
        if (! is_array($imageInfo) || ! isset($imageInfo[2]) || ! in_array((int) $imageInfo[2], $allowedImageTypes, true)) {
            return 'La imagen es inválida o está dañada.';
        }

        return null;
    }

    private function reviewRateLimitKey(Request $request): string
    {
        $userId = (int) ($request->user()?->id ?? 0);
        if ($userId > 0) {
            return 'review-submit:user:' . $userId;
        }

        $ip = (string) ($request->ip() ?? 'unknown');

        return 'review-submit:guest:' . $ip;
    }

    private function reviewRateLimitConfig(Request $request): array
    {
        if ($request->user()) {
            return [
                'max_attempts' => 6,
                'decay_seconds' => 600,
            ];
        }

        return [
            'max_attempts' => 3,
            'decay_seconds' => 600,
        ];
    }
}
