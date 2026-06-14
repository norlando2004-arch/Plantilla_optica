<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MensajeContacto extends Model
{
    use HasFactory;

    protected $table = 'mensajes_contacto';

    protected $fillable = [
        'usuario_id',
        'nombre',
        'correo',
        'telefono',
        'asunto',
        'mensaje',
        'estado',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
