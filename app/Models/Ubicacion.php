<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones';

    protected $fillable = [
        'nombre',
        'direccion',
        'ciudad',
        'region',
        'pais',
        'telefono',
        'correo',
        'url_google_maps',
        'latitud',
        'longitud',
        'horario',
        'esta_activo',
        'orden',
        'meta',
    ];

    protected $casts = [
        'horario' => 'array',
        'meta' => 'array',
        'esta_activo' => 'boolean',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
    ];
}
