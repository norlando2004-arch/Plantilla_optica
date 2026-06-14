<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GafaPrescription extends Model
{
    protected $table = 'gafa_prescriptions';

    protected $fillable = [
        'user_id',
        'session_id',
        'storage_disk',
        'storage_path',
        'original_name',
        'mime',
        'size',
        'sha256',
        'analysis',
    ];

    protected $casts = [
        'analysis' => 'array',
    ];
}
