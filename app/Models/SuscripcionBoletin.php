<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuscripcionBoletin extends Model
{
    use HasFactory;

    protected $table = 'suscripciones_boletin';

    protected $fillable = [
        'correo',
        'usuario_id',
        'estado',
        'origen',
        'suscrito_en',
        'cancelado_en',
        'meta',
    ];

    protected $casts = [
        'suscrito_en' => 'datetime',
        'cancelado_en' => 'datetime',
        'meta' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
