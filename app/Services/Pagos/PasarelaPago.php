<?php

namespace App\Services\Pagos;

use App\Models\Pago;

interface PasarelaPago
{
    /**
     * Retorna una URL a la que se debe redirigir al usuario para pagar.
     */
    public function iniciar(Pago $pago): string;
}
