<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdGeneration extends Model
{
    protected $fillable = [
        'ads_id', 'prompt', 'status', 'result_media', 'user_id', "caption"
    ];

    public function ads()
    {
        return $this->belongsTo(Ad::class);
    }
     public function schedules()
    {
        return $this->hasMany(AdSchedule::class, 'generation_ads_id');
    }
}
