<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $table = 'carritos';

    protected $fillable = [
        'usuario_id',
        'sesion_id',
        'guest_token',
        'estado',
        'moneda',
        'subtotal',
        'total_descuento',
        'total',
        'meta',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total_descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'meta' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function items()
    {
        return $this->hasMany(ItemCarrito::class, 'carrito_id');
    }
}
