<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $lens_type
 * @property string $nara_level
 * @property int $price
 */
class GafaLensPrice extends Model
{
    protected $table = 'gafa_lens_prices';

    protected $fillable = [
        'lens_type',
        'nara_level',
        'price',
    ];
}
