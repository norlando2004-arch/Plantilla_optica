<?php

namespace App\Mail;

use App\Models\Pago;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminOrderNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Pago $pago;
    /** @var array<int, array<string, mixed>> */
    public array $itemsSummary;
    public bool $hasLowStock;

    /**
     * @param array<int, array<string, mixed>> $itemsSummary
     */
    public function __construct(Pago $pago, array $itemsSummary, bool $hasLowStock)
    {
        $this->pago = $pago;
        $this->itemsSummary = $itemsSummary;
        $this->hasLowStock = $hasLowStock;
    }

    public function build(): self
    {
        return $this
            ->subject('Nuevo pago aprobado - Optica')
            ->view('emails.admin.order_notification');
    }
}
