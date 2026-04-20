<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    public const REGISTRATION_CLOSED = 'closed';

    public const REGISTRATION_OPEN = 'open';

    public const REGISTRATION_SQUADDED = 'squadded';

    protected $fillable = [
        'shooting_ground_id',
        'title',
        'slug',
        'summary',
        'starts_at',
        'discipline_id',
        'discipline',
        'external_url',
        'cpsa_registered',
        'registration_format',
        'open_max_participants',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'cpsa_registered' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ShootingGround, $this>
     */
    public function shootingGround(): BelongsTo
    {
        return $this->belongsTo(ShootingGround::class);
    }

    /**
     * Canonical discipline from the directory taxonomy (optional).
     *
     * @return BelongsTo<Discipline, $this>
     */
    public function canonicalDiscipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class, 'discipline_id');
    }

    /**
     * @return HasMany<CompetitionSquad, $this>
     */
    public function squads(): HasMany
    {
        return $this->hasMany(CompetitionSquad::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<CompetitionRegistration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(CompetitionRegistration::class);
    }

    public function disciplineDisplay(): ?string
    {
        return $this->canonicalDiscipline?->name ?? $this->discipline;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isPast(): bool
    {
        return $this->starts_at->isPast();
    }

    public function registrationIsOpen(): bool
    {
        return in_array($this->registration_format, [self::REGISTRATION_OPEN, self::REGISTRATION_SQUADDED], true);
    }

    /**
     * Whether the public book URL should work (GET). Squadded events need at least one squad row.
     */
    public function bookingPageAvailable(): bool
    {
        if ($this->isPast() || ! $this->registrationIsOpen()) {
            return false;
        }

        if ($this->registration_format === self::REGISTRATION_SQUADDED) {
            return $this->squads()->exists();
        }

        return $this->registration_format === self::REGISTRATION_OPEN;
    }

    public function openRegistrationsCount(): int
    {
        return (int) $this->registrations()->whereNull('competition_squad_id')->count();
    }

    public function openHasFreeSlots(): bool
    {
        if ($this->registration_format !== self::REGISTRATION_OPEN) {
            return false;
        }
        if ($this->open_max_participants === null) {
            return true;
        }

        return $this->openRegistrationsCount() < $this->open_max_participants;
    }

    public function squaddedHasAnyFreeSlot(): bool
    {
        if ($this->registration_format !== self::REGISTRATION_SQUADDED) {
            return false;
        }

        return $this->squads()->withSum('registrations', 'party_size')->get()->contains(
            fn (CompetitionSquad $s) => (int) ($s->registrations_sum_party_size ?? 0) < $s->capacity()
        );
    }
}
