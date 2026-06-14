<?php

namespace App\Mail;

use App\Models\Pago;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PedidoEnviadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public Pago $pago;

    public function __construct(Pago $pago)
    {
        $this->pago = $pago;
    }

    public function build(): self
    {
        return $this
            ->subject('Tu pedido ya fue enviado a tu direccion - Optica')
            ->view('emails.user.pedido_enviado');
    }
}
