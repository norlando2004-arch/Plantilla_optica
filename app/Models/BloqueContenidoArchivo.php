<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BloqueContenidoArchivo extends Model
{
    use HasFactory;

    protected $table = 'bloques_contenido_archivos';

    protected $fillable = [
        'bloque_contenido_id',
        'field_key',
        'orden',
        'mime_type',
        'original_name',
        'size_bytes',
        'contenido_base64',
        'ruta_archivo',
    ];

    protected $casts = [
        'orden' => 'integer',
        'size_bytes' => 'integer',
    ];

    public function bloqueContenido()
    {
        return $this->belongsTo(BloqueContenido::class, 'bloque_contenido_id');
    }

    public function publicUrl(): string
    {
        if (filled($this->ruta_archivo)) {
            return '/storage/' . ltrim((string) $this->ruta_archivo, '/');
        }

        return route('content-block-assets.show', [
            'asset' => $this,
            'v' => $this->updated_at?->timestamp ?? $this->getKey(),
        ]);
    }
}