<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloqueContenido extends Model
{
    use HasFactory;

    protected $table = 'bloques_contenido';

    protected $fillable = [
        'clave',
        'titulo',
        'cuerpo',
        'datos',
        'esta_activo',
        'orden',
    ];

    protected $casts = [
        'datos' => 'array',
        'esta_activo' => 'boolean',
    ];

    public function archivos()
    {
        return $this->hasMany(BloqueContenidoArchivo::class, 'bloque_contenido_id');
    }
}
