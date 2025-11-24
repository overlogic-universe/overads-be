<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = ['user_id', 'ad_id', 'scheduled_at', 'platforms', 'status', 'n8n_execution_id', 'timezone', 'response'];

    protected $casts = ['platforms' => 'array', 'response' => 'array', 'scheduled_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ad()
    {
        return $this->belongsTo(Ad::class);
    }
}
