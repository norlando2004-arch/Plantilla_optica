<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyRegistrationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $nombre;

    public function __construct(string $code, string $nombre)
    {
        $this->code = $code;
        $this->nombre = $nombre;
    }

    public function build(): self
    {
        return $this
            ->subject('Tu código de verificación - Óptica')
            ->view('emails.auth.verify_registration_code');
    }
}
