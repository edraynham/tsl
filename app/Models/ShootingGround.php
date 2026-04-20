<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShootingGround extends Model
{
    /** @var list<string> monday … sunday — owner opening-hours form and column order */
    public const DAY_PREFIXES = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    protected $fillable = [
        'name',
        'slug',
        'photo_url',
        'city',
        'county',
        'postcode',
        'full_address',
        'latitude',
        'longitude',
        'website',
        'facebook_url',
        'instagram_url',
        'description',
        'opening_hours',
        'has_practice',
        'has_lessons',
        'has_competitions',
        'practice_notes',
        'lesson_notes',
        'competition_notes',
        'events_urls',
    ];

    protected function casts(): array
    {
        return [
            'has_practice' => 'boolean',
            'has_lessons' => 'boolean',
            'has_competitions' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'events_urls' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Structured weekly times in the `opening_hours` table (one row per ground).
     * When absent or empty, use {@see $opening_hours} free-text from imports.
     *
     * @return HasOne<OpeningHours, $this>
     */
    public function openingHours(): HasOne
    {
        return $this->hasOne(OpeningHours::class);
    }

    /**
     * True when the related row exists and at least one day has both open and close times.
     */
    public function hasStructuredWeeklyHours(): bool
    {
        $row = $this->openingHours;

        return $row !== null && $row->hasAtLeastOneOpenDay();
    }

    /**
     * @return HasMany<Competition, $this>
     */
    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class)->orderBy('starts_at');
    }

    /**
     * @return BelongsToMany<Discipline, $this>
     */
    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class)->withTimestamps(false, false);
    }

    /**
     * @return BelongsToMany<Facility, $this>
     */
    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Stable Unsplash image URL for a slug (used when seeding and as fallback).
     */
    public static function photoUrlForSlug(string $slug): string
    {
        $pool = self::unsplashPhotoPool();
        $n = count($pool);
        $i = (crc32($slug) % $n + $n) % $n;

        return $pool[$i];
    }

    public function coverPhotoUrl(): string
    {
        if (! empty($this->photo_url)) {
            return $this->photo_url;
        }

        return self::photoUrlForSlug($this->slug ?? '');
    }

    /**
     * @return list<string>
     */
    private static function unsplashPhotoPool(): array
    {
        $q = 'auto=format&fit=crop&w=1200&q=80';

        return [
            "https://images.unsplash.com/photo-1500382017468-9049fed747ef?{$q}",
            "https://images.unsplash.com/photo-1600585154340-be6161a56a0c?{$q}",
            "https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?{$q}",
            "https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?{$q}",
            "https://images.unsplash.com/photo-1594938298603-c8148c4dae35?{$q}",
            "https://images.unsplash.com/photo-1469474968028-56623f02e42e?{$q}",
            "https://images.unsplash.com/photo-1441974231531-c6227db76b6e?{$q}",
            "https://images.unsplash.com/photo-1506905925346-21bda4d32df4?{$q}",
            "https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?{$q}",
            "https://images.unsplash.com/photo-1511497584788-876760111969?{$q}",
            "https://images.unsplash.com/photo-1472214103451-9374bd1c798e?{$q}",
            "https://images.unsplash.com/photo-1518173946687-a4c8892bbd9f?{$q}",
        ];
    }
}
