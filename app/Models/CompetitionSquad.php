<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompetitionSquad extends Model
{
    protected $fillable = [
        'competition_id',
        'starts_at',
        'max_participants',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'max_participants' => 'integer',
        ];
    }

    /**
     * Squad capacity for online booking (1–12).
     */
    public function capacity(): int
    {
        return max(1, min(12, (int) $this->max_participants));
    }

    /**
     * @return BelongsTo<Competition, $this>
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * @return HasMany<CompetitionRegistration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(CompetitionRegistration::class);
    }

    /**
     * Display label "Squad 1", "Squad 2", … by order within the competition.
     */
    public function label(): string
    {
        $ids = static::query()
            ->where('competition_id', $this->competition_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id');
        $idx = $ids->search($this->id);

        $n = $idx !== false ? $idx + 1 : 1;

        return __('Squad :n', ['n' => $n]);
    }
}
