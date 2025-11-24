<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['user_id', 'key', 'value'];

    public function setValueAttribute($v)
    {
        $this->attributes['value'] = Crypt::encryptString(is_string($v) ? $v : json_encode($v));
    }

    public function getValueAttribute($v)
    {
        try {
            $de = Crypt::decryptString($v);

            return json_decode($de, true) ?? $de;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
