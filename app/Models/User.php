<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['first_name', 'last_name', 'email', 'is_shooter', 'is_organiser', 'is_instructor', 'registration_roles_completed_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $appends = [
        'name',
    ];

    /**
     * Shooting grounds this user may edit (ground owner).
     *
     * @return BelongsToMany<ShootingGround, $this>
     */
    public function ownedShootingGrounds(): BelongsToMany
    {
        return $this->belongsToMany(ShootingGround::class)->withTimestamps();
    }

    public function managesShootingGround(ShootingGround $ground): bool
    {
        return $this->ownedShootingGrounds()->whereKey($ground->getKey())->exists();
    }

    public function isGroundOwner(): bool
    {
        return $this->ownedShootingGrounds()->exists();
    }

    public function isShooter(): bool
    {
        return (bool) $this->is_shooter;
    }

    public function isOrganiser(): bool
    {
        return (bool) $this->is_organiser;
    }

    public function isInstructor(): bool
    {
        return (bool) $this->is_instructor;
    }

    /**
     * Optional public directory profile (when listed as an instructor).
     *
     * @return HasOne<Instructor, $this>
     */
    public function instructorProfile(): HasOne
    {
        return $this->hasOne(Instructor::class);
    }

    /**
     * Full display name (not stored — use first_name / last_name in forms).
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn () => trim($this->first_name.' '.$this->last_name));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_shooter' => 'boolean',
            'is_organiser' => 'boolean',
            'is_instructor' => 'boolean',
            'registration_roles_completed_at' => 'datetime',
        ];
    }
}
