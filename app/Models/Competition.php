<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Competition extends Model
{
    protected $fillable = [
        'shooting_ground_id',
        'title',
        'slug',
        'summary',
        'starts_at',
        'ends_at',
        'discipline_id',
        'discipline',
        'external_url',
        'cpsa_registered',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
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

    public function isMultiDay(): bool
    {
        return $this->ends_at !== null
            && $this->ends_at->toDateString() !== $this->starts_at->toDateString();
    }
}
