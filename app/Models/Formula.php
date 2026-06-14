<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formula extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'esta_activo',
        'orden',
        'meta',
    ];

    protected $casts = [
        'esta_activo' => 'boolean',
        'meta' => 'array',
    ];

    public function precios()
    {
        return $this->hasMany(PrecioFormula::class, 'formula_id');
    }
}
