<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'uuid', 'full_name', 'business_name', 'phone', 'email', 'password', 'avatar_url', 'expiration_date', 'package_id',
    ];

    protected $hidden = ['password'];

    // protected $casts = ['is_admin' => 'boolean', 'credits' => 'integer'];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
