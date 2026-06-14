<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    use HasFactory;

    protected $table = 'resenas';

    protected $fillable = [
        'usuario_id',
        'autor_nombre',
        'estrellas',
        'comentario',
        'foto_url',
        'foto_data',
        'foto_nombre',
        'foto_mime',
        'foto_size',
    ];

    protected function casts(): array
    {
        return [
            'estrellas' => 'integer',
            'foto_size' => 'integer',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
