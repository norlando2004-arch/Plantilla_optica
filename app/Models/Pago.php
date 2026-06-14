<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'carrito_id',
        'estado',
        'envio_estado',
        'envio_marcado_por',
        'envio_marcado_en',
        'pasarela',
        'moneda',
        'monto',
        'referencia',
        'pasarela_transaccion_id',
        'pasarela_estado',
        'meta',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'meta' => 'array',
        'envio_marcado_en' => 'datetime',
    ];

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }
}
