<?php

namespace App\Mail;

use App\Models\Pago;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserPaymentInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Pago $pago;
    /** @var array<int, array<string, mixed>> */
    public array $itemsSummary;

    /**
     * @param array<int, array<string, mixed>> $itemsSummary
     */
    public function __construct(Pago $pago, array $itemsSummary)
    {
        $this->pago = $pago;
        $this->itemsSummary = $itemsSummary;
    }

    public function build(): self
    {
        return $this
            ->subject('Confirmación de pago - Optica')
            ->view('emails.user.payment_invoice');
    }
}
