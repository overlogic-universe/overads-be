<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'original_price',
        'price',
        'benefits',
    ];

    protected $casts = [
        'benefits' => 'array',
    ];
}
