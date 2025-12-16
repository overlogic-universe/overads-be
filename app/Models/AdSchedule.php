<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSchedule extends Model
{
    protected $fillable = [
        'ads_id','platform','scheduled_at','status', "generation_ads_id",'user_id'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime'
    ];

    public function ads()
    {
        return $this->belongsTo(Ad::class);
    }
     public function ad()
    {
        return $this->belongsTo(Ad::class, 'ads_id');
    }

    public function generation()
    {
        return $this->belongsTo(AdGeneration::class, 'generation_ads_id');
    }
}
