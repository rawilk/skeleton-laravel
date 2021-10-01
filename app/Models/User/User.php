<?php

declare(strict_types=1);

namespace App\Models\User;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Rawilk\LaravelBase\Concerns\HasAvatar;
use Rawilk\LaravelBase\Concerns\TwoFactorAuthenticatable;
use Rawilk\LaravelCasters\Casts\Password;
use Rawilk\LaravelCasters\Support\Name;

class User extends Authenticatable // implements MustVerifyEmail
{
    use HasFactory;
    use Notifiable;
    use HasAvatar;
    use TwoFactorAuthenticatable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'name' => Name::class,
        'password' => Password::class,
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
