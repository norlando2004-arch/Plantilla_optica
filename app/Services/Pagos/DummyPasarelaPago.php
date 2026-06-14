<?php

namespace App\Services\Pagos;

use App\Models\Pago;

class DummyPasarelaPago implements PasarelaPago
{
    public function iniciar(Pago $pago): string
    {
        return route('pagos.dummy', $pago);
    }
}
