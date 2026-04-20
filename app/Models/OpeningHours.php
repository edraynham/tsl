<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per shooting ground: weekly times in {@see ShootingGround::DAY_PREFIXES} columns.
 */
class OpeningHours extends Model
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

    protected $table = 'opening_hours';

    protected $fillable = [
        'shooting_ground_id',
        'monday_opens_at',
        'monday_closes_at',
        'tuesday_opens_at',
        'tuesday_closes_at',
        'wednesday_opens_at',
        'wednesday_closes_at',
        'thursday_opens_at',
        'thursday_closes_at',
        'friday_opens_at',
        'friday_closes_at',
        'saturday_opens_at',
        'saturday_closes_at',
        'sunday_opens_at',
        'sunday_closes_at',
    ];

    protected function casts(): array
    {
        return [
            'monday_opens_at' => 'datetime:H:i',
            'monday_closes_at' => 'datetime:H:i',
            'tuesday_opens_at' => 'datetime:H:i',
            'tuesday_closes_at' => 'datetime:H:i',
            'wednesday_opens_at' => 'datetime:H:i',
            'wednesday_closes_at' => 'datetime:H:i',
            'thursday_opens_at' => 'datetime:H:i',
            'thursday_closes_at' => 'datetime:H:i',
            'friday_opens_at' => 'datetime:H:i',
            'friday_closes_at' => 'datetime:H:i',
            'saturday_opens_at' => 'datetime:H:i',
            'saturday_closes_at' => 'datetime:H:i',
            'sunday_opens_at' => 'datetime:H:i',
            'sunday_closes_at' => 'datetime:H:i',
        ];
    }

    /**
     * @return BelongsTo<ShootingGround, $this>
     */
    public function shootingGround(): BelongsTo
    {
        return $this->belongsTo(ShootingGround::class);
    }

    public function hasAtLeastOneOpenDay(): bool
    {
        foreach (ShootingGround::DAY_PREFIXES as $day) {
            $o = $this->{$day.'_opens_at'};
            $c = $this->{$day.'_closes_at'};
            if ($o && $c) {
                return true;
            }
        }

        return false;
    }
}
