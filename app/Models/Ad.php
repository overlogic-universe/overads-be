<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $fillable = ['user_id', 'name', 'type', 'description', 'theme', 'platforms', 'reference_media'];

    protected $casts = ['platforms' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'ad_id');
    }
}
