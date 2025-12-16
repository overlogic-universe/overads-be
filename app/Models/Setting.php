<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['user_id', 'apiKey', 'igId'];

    public function setApiKeyAttribute($v)
    {
        $this->attributes['apiKey'] =
            Crypt::encryptString(is_string($v) ? $v : json_encode($v));
    }

    public function getApiKeyAttribute($v)
    {
        try {
            $de = Crypt::decryptString($v);

            return json_decode($de, true) ?? $de;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function setIgIdAttribute($v)
    {
        $this->attributes['igId'] =
            Crypt::encryptString(is_string($v) ? $v : json_encode($v));
    }

    public function getIgIdAttribute($v)
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
