<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\PrescriptionPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class GafaPrescriptionController extends Controller
{
    public function analyze(Request $request, Producto $producto, PrescriptionPdfService $pdfService): RedirectResponse
    {
        // OCR/parseo puede tardar más de 30s (especialmente en PDFs escaneados).
        // Elevamos el límite solo para esta acción.
        try {
            @set_time_limit(180);
            @ini_set('max_execution_time', '180');
        } catch (\Throwable) {
            // noop
        }

        abort_unless(
            in_array($producto->tipo, ['gafas', 'gafas_ninas', 'gafas_ninos', 'gafas_polarizadas'], true)
                && in_array($producto->genero_objetivo, ['female', 'male', 'unisex', 'ninos', 'ninas', 'gafas_polarizadas', 'descanso'], true)
                && (bool) $producto->esta_activo,
            404
        );

        $validated = $request->validate([
            'archivo' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:5120',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!$value instanceof UploadedFile) {
                        $fail('Sube un PDF o una imagen con tu fórmula.');
                        return;
                    }

                    if (!$value->isValid()) {
                        $fail('No se pudo subir el archivo (upload inválido). Intenta de nuevo o cambia el navegador.');
                        return;
                    }

                    $pathname = $value->getPathname();
                    if (!is_string($pathname) || trim($pathname) === '' || !is_file($pathname)) {
                        $fail('No se pudo subir el archivo (temporal no disponible). Intenta de nuevo o revisa la carpeta temporal de PHP.');
                    }
                },
            ],
        ], [
            'archivo.required' => 'Sube un PDF o una imagen con tu fórmula.',
            'archivo.mimes' => 'El archivo debe ser PDF o una imagen (JPG/PNG/WebP).',
            'archivo.max' => 'El archivo debe pesar máximo 5MB.',
        ]);

        /** @var UploadedFile $archivo */
        $archivo = $validated['archivo'];

        if (app()->isLocal()) {
            try {
                Log::info('GafaPrescription upload received', [
                    'original_name' => $archivo->getClientOriginalName(),
                    'client_mime' => method_exists($archivo, 'getClientMimeType') ? $archivo->getClientMimeType() : null,
                    'mime' => $archivo->getMimeType(),
                    'size' => $archivo->getSize(),
                    'error' => $archivo->getError(),
                    'is_valid' => $archivo->isValid(),
                    'pathname' => $archivo->getPathname(),
                    'realpath' => $archivo->getRealPath(),
                ]);
            } catch (\Throwable) {
                // noop
            }
        }

        try {
            $result = $pdfService->extractPrescriptionFromUpload($archivo);
        } catch (\Throwable $e) {
            report($e);

            if (app()->isLocal()) {
                Log::error('GafaPrescription analyze failed', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'trace' => substr($e->getTraceAsString(), 0, 4000),
                    'original_name' => $archivo->getClientOriginalName(),
                    'mime' => $archivo->getMimeType(),
                    'size' => $archivo->getSize(),
                    'error' => $archivo->getError(),
                    'pathname' => $archivo->getPathname(),
                ]);
            }

            $message = 'No pude leer ese archivo. Intenta con otro (o con una foto/escaneo más nítido).';
            if (app()->isLocal()) {
                $detail = (string) $e->getMessage();
                if (stripos($detail, 'Path must not be empty') !== false) {
                    $detail = 'El archivo no se subió correctamente (ruta temporal vacía). Selecciónalo de nuevo e intenta.';
                }

                $message .= ' Detalle (solo local): ' . $detail;
            }

            return back()
                ->withInput()
                ->withErrors([
                    'archivo' => $message,
                ]);
        }

        return back()->with([
            'prescription' => $result,
        ]);
    }
}
