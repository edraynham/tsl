<?php

namespace Database\Seeders;

use App\Models\OpeningHours;
use App\Models\ShootingGround;
use Illuminate\Database\Seeder;

class OpeningHourSeeder extends Seeder
{
    /**
     * Fabricated weekly schedules. Keys: monday…sunday → [opens, closes] or nulls for closed.
     *
     * @var list<array<string, array{0: string|null, 1: string|null}>>
     */
    private array $templates;

    public function __construct()
    {
        $closed = [null, null];
        $this->templates = [
            // Tue–Sun 09:00–17:00; Monday closed
            [
                'monday' => $closed,
                'tuesday' => ['09:00', '17:00'],
                'wednesday' => ['09:00', '17:00'],
                'thursday' => ['09:00', '17:00'],
                'friday' => ['09:00', '17:00'],
                'saturday' => ['09:00', '17:00'],
                'sunday' => ['09:00', '16:00'],
            ],
            // Mon–Fri 10:00–16:00; weekend longer
            [
                'monday' => ['10:00', '16:00'],
                'tuesday' => ['10:00', '16:00'],
                'wednesday' => ['10:00', '16:00'],
                'thursday' => ['10:00', '16:00'],
                'friday' => ['10:00', '16:00'],
                'saturday' => ['09:00', '17:00'],
                'sunday' => ['09:00', '17:00'],
            ],
            // Wed–Sun only
            [
                'monday' => $closed,
                'tuesday' => $closed,
                'wednesday' => ['09:30', '17:00'],
                'thursday' => ['09:30', '17:00'],
                'friday' => ['09:30', '17:00'],
                'saturday' => ['09:00', '17:30'],
                'sunday' => ['10:00', '15:00'],
            ],
            // All week; Thursday longer evening slot
            [
                'monday' => ['10:00', '16:00'],
                'tuesday' => ['10:00', '16:00'],
                'wednesday' => ['10:00', '16:00'],
                'thursday' => ['09:00', '20:00'],
                'friday' => ['09:00', '17:00'],
                'saturday' => ['09:00', '17:00'],
                'sunday' => ['10:00', '15:00'],
            ],
            // Typical club: closed Mon–Tue
            [
                'monday' => $closed,
                'tuesday' => $closed,
                'wednesday' => ['10:00', '17:00'],
                'thursday' => ['10:00', '17:00'],
                'friday' => ['10:00', '17:00'],
                'saturday' => ['09:00', '17:00'],
                'sunday' => ['09:00', '16:00'],
            ],
        ];
    }

    public function run(): void
    {
        $grounds = ShootingGround::query()->orderBy('name')->get();
        if ($grounds->isEmpty()) {
            $this->command?->warn('No shooting grounds — run ShootingGroundSeeder first.');

            return;
        }

        $n = count($this->templates);

        foreach ($grounds as $ground) {
            $idx = (crc32($ground->slug) % $n + $n) % $n;
            $tpl = $this->templates[$idx];
            $attrs = ['shooting_ground_id' => $ground->id];
            foreach (ShootingGround::DAY_PREFIXES as $day) {
                [$open, $close] = $tpl[$day];
                $attrs[$day.'_opens_at'] = $open;
                $attrs[$day.'_closes_at'] = $close;
            }
            OpeningHours::query()->updateOrCreate(
                ['shooting_ground_id' => $ground->id],
                $attrs
            );
            $ground->update(['opening_hours' => null]);
        }

        $this->command?->info('Seeded weekly opening hours for '.$grounds->count().' grounds.');
    }
}
