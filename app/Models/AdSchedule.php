<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSchedule extends Model
{
    protected $fillable = [
        'ads_id','platform','scheduled_at','status'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime'
    ];

    public function ads()
    {
        return $this->belongsTo(Ad::class);
    }
}
