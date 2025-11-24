<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['user_id', 'pricing_id', 'payment_method', 'amount_cents', 'meta', 'status'];

    protected $casts = ['meta' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pricing()
    {
        return $this->belongsTo(Pricing::class);
    }
}
