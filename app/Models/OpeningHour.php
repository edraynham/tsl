<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningHour extends Model
{
    /** ISO 8601 weekday: 1 = Monday … 7 = Sunday */
    public const WEEKDAY_LABELS = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    protected $fillable = [
        'shooting_ground_id',
        'weekday',
        'opens_at',
        'closes_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
            'sort_order' => 'integer',
            'opens_at' => 'datetime:H:i',
            'closes_at' => 'datetime:H:i',
        ];
    }

    public function shootingGround(): BelongsTo
    {
        return $this->belongsTo(ShootingGround::class);
    }

    public function weekdayLabel(): string
    {
        return self::WEEKDAY_LABELS[$this->weekday] ?? '';
    }
}
