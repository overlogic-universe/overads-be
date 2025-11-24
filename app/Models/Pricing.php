<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{
    protected $fillable = ['slug', 'name', 'price_cents', 'credits_given', 'billing_period_days', 'description'];
}
