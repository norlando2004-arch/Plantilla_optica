<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilCliente extends Model
{
    use HasFactory;

    protected $table = 'perfiles_clientes';

    protected $fillable = [
        'usuario_id',
        'tipo_documento',
        'numero_documento',
        'telefono',
        'fecha_nacimiento',
        'genero',
        'direccion',
        'ciudad',
        'notas',
        'preferencias',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'preferencias' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
