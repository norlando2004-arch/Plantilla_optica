<?php

namespace App\Http\Controllers;

use App\Models\BloqueContenidoArchivo;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ContentBlockAssetController extends Controller
{
    public function show(BloqueContenidoArchivo $asset): Response
    {
        // New format: file stored on disk – serve directly from storage
        if (filled($asset->ruta_archivo)) {
            $contents = Storage::disk('public')->get($asset->ruta_archivo);
            abort_if($contents === null, 404);

            $etag = '"'.sha1((string) $asset->id.'|'.(string) $asset->updated_at?->timestamp.'|'.(string) $asset->size_bytes).'"';

            return response($contents, 200, [
                'Content-Type' => $asset->mime_type,
                'Content-Length' => (string) strlen($contents),
                'Content-Disposition' => 'inline; filename="'.($asset->original_name ?: 'contenido').'"',
                'Cache-Control' => 'public, max-age=604800',
                'ETag' => $etag,
            ]);
        }

        // Legacy format: base64 in DB
        $binary = base64_decode((string) $asset->contenido_base64, true);
        abort_if($binary === false, 404);

        $etag = '"'.sha1((string) $asset->id.'|'.(string) $asset->updated_at?->timestamp.'|'.(string) $asset->size_bytes).'"';

        return response($binary, 200, [
            'Content-Type' => $asset->mime_type,
            'Content-Length' => (string) strlen($binary),
            'Content-Disposition' => 'inline; filename="'.($asset->original_name ?: 'contenido').'"',
            'Cache-Control' => 'public, max-age=604800',
            'ETag' => $etag,
        ]);
    }
}