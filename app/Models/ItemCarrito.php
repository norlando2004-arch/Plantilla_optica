<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCarrito extends Model
{
    use HasFactory;

    protected $table = 'items_carrito';

    protected $fillable = [
        'carrito_id',
        'producto_id',
        'nombre_producto',
        'precio_unitario',
        'cantidad',
        'moneda',
        'meta',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'meta' => 'array',
    ];

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
