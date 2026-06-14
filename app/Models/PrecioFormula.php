<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecioFormula extends Model
{
    use HasFactory;

    protected $table = 'precios_formulas';

    protected $fillable = [
        'formula_id',
        'etiqueta',
        'precio',
        'moneda',
        'esta_activo',
        'meta',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'esta_activo' => 'boolean',
        'meta' => 'array',
    ];

    public function formula()
    {
        return $this->belongsTo(Formula::class, 'formula_id');
    }
}
