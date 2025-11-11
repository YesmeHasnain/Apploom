<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'description',
        'provider',
        'provider_id',
        'provider_token',
        // 'provider_refresh_token', // if you add later
        // 'provider_expires_in',    // if you add later
        'email_verified_at',
    ];

    /**
     * Hidden for arrays.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'provider_token',
        // 'provider_refresh_token',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // IMPORTANT: DO NOT add 'password' => 'hashed' here on your Laravel version
    ];



    // for image update
 public function getAvatarUrlAttribute(): string
{
    $raw = $this->avatar;
    if (! $raw) return '/img/profile/profile-9.webp';

    if (\Illuminate\Support\Str::startsWith($raw, ['http://','https://','data:'])) {
        return $raw;
    }
    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($raw)) {
        return asset('storage/'.$raw);
    }
    if (file_exists(public_path($raw))) {
        return asset($raw);
    }
    return '/img/profile/profile-9.webp';
}

}

