<?php

namespace App\Services\Pagos;

class PasarelaPagoFactory
{
    public static function make(string $driver): PasarelaPago
    {
        return match ($driver) {
            'dummy' => new DummyPasarelaPago(),
            'bold' => new BoldPasarelaPago(),
            default => new DummyPasarelaPago(),
        };
    }
}
