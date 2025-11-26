<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'package_id', 'external_id', 'amount', 'status', 'invoice_url', 'xendit_invoice_id', 'payload',
    ];
}
