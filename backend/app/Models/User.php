<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'failed_login_attempts',
        'locked_until',
        'profile_picture',
        'profile_image',
        'bio',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'locked_until'      => 'datetime',
        ];
    }

    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }
        return asset('images/default-avatar.png');
    }
}