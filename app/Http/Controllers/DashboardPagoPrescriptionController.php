<?php

namespace App\Http\Controllers;

use App\Models\GafaPrescription;
use App\Models\Pago;
use Illuminate\Support\Facades\Storage;

class DashboardPagoPrescriptionController extends Controller
{
    public function show(Pago $pago)
    {
        $pago->loadMissing('carrito');

        $meta = is_array($pago->meta) ? $pago->meta : [];
        $carritoMeta = is_array($pago->carrito?->meta) ? $pago->carrito->meta : [];
        $prescriptionId = (int) ($meta['prescription_id'] ?? $carritoMeta['prescription_id'] ?? 0);
        abort_unless($prescriptionId > 0, 404);

        $prescription = GafaPrescription::query()->whereKey($prescriptionId)->first();
        abort_unless($prescription !== null, 404);

        $disk = (string) ($prescription->storage_disk ?: 'local');
        $path = (string) $prescription->storage_path;
        abort_unless($path !== '', 404);

        $storage = Storage::disk($disk);
        abort_unless($storage->exists($path), 404);

        $originalName = trim((string) $prescription->original_name);
        if ($originalName === '') {
            $originalName = 'formula.pdf';
        }

        try {
            return $storage->response(
                $path,
                $originalName,
                [
                    'Content-Type' => 'application/pdf',
                ]
            );
        } catch (\Throwable $e) {
            report($e);
            abort(404);
        }
    }
}
