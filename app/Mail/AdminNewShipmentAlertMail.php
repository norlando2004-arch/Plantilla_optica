<?php

namespace App\Mail;

use App\Models\GafaPrescription;
use App\Models\Pago;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class AdminNewShipmentAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public Pago $pago;

    public function __construct(Pago $pago)
    {
        $this->pago = $pago;
    }

    public function build(): self
    {
        $mailable = $this
            ->subject('Nuevo envío por preparar - Optica')
            ->view('emails.admin.new_shipment_alert');

        // Adjuntar la fórmula PDF desde storage si existe.
        try {
            $meta = is_array($this->pago->meta) ? $this->pago->meta : [];
            $carritoMeta = is_array($this->pago->carrito?->meta) ? $this->pago->carrito->meta : [];
            $prescriptionId = (int) ($meta['prescription_id'] ?? $carritoMeta['prescription_id'] ?? 0);

            if ($prescriptionId > 0) {
                $prescription = GafaPrescription::query()->whereKey($prescriptionId)->first();

                if ($prescription) {
                    $disk = (string) ($prescription->storage_disk ?: 'local');
                    $path = (string) $prescription->storage_path;
                    $originalName = trim((string) $prescription->original_name);
                    if ($originalName === '') {
                        $originalName = 'formula.pdf';
                    }

                    if ($path !== '' && Storage::disk($disk)->exists($path)) {
                        $mailable->attachFromStorageDisk($disk, $path, $originalName, [
                            'mime' => 'application/pdf',
                        ]);
                    }
                }
            }
        } catch (\Throwable) {
            // No bloquear el envío del correo si falla la carga del PDF.
        }

        return $mailable;
    }
}
