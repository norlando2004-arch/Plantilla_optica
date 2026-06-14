<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UsuarioFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'rol_id',
        'nombre',
        'correo',
        'contrasena',
        'rol',
        'esta_activo',
    ];

    protected $hidden = [
        'contrasena',
        'token_recordar',
    ];

    protected function casts(): array
    {
        return [
            'correo_verificado_en' => 'datetime',
            'contrasena' => 'hashed',
            'esta_activo' => 'boolean',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public function getRememberTokenName(): string
    {
        return 'token_recordar';
    }

    public function getEmailForPasswordReset(): string
    {
        return (string) $this->correo;
    }

    public function perfilCliente()
    {
        return $this->hasOne(PerfilCliente::class, 'usuario_id');
    }

    public function carritos()
    {
        return $this->hasMany(Carrito::class, 'usuario_id');
    }

    public function suscripcionesBoletin()
    {
        return $this->hasMany(SuscripcionBoletin::class, 'usuario_id');
    }

    public function mensajesContacto()
    {
        return $this->hasMany(MensajeContacto::class, 'usuario_id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class, 'usuario_id');
    }

        public function favoritos()
        {
            return $this->hasMany(Favorito::class, 'usuario_id');
        }

        public function productosFavoritos()
        {
            return $this->belongsToMany(Producto::class, 'favoritos', 'usuario_id', 'producto_id')
                ->withTimestamps();
        }
}
