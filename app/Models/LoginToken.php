<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LoginToken extends Model
{
    protected $fillable = [
        'email',
        'token',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Create a single-use login token; returns the plain token for the URL (not stored).
     */
    public static function createForEmail(string $email): string
    {
        self::query()->where('email', $email)->delete();

        $plain = Str::random(64);

        self::query()->create([
            'email' => $email,
            'token' => hash('sha256', $plain),
            'expires_at' => now()->addMinutes(60),
        ]);

        return $plain;
    }

    public static function findValidPlainToken(string $plain): ?self
    {
        $row = self::query()
            ->where('token', hash('sha256', $plain))
            ->where('expires_at', '>', now())
            ->first();

        return $row;
    }
}
