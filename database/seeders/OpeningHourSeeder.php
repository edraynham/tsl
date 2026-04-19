<?php

namespace Database\Seeders;

use App\Models\OpeningHour;
use App\Models\ShootingGround;
use Illuminate\Database\Seeder;

class OpeningHourSeeder extends Seeder
{
    /**
     * Fabricated weekly schedules (ISO weekday 1 = Monday … 7 = Sunday).
     *
     * @var list<list{array{0: int, 1: string, 2: string, 3?: int}}>
     */
    private array $templates;

    public function __construct()
    {
        $this->templates = [
            // Tue–Sun 09:00–17:00; Monday closed
            [
                [2, '09:00', '17:00', 0],
                [3, '09:00', '17:00', 0],
                [4, '09:00', '17:00', 0],
                [5, '09:00', '17:00', 0],
                [6, '09:00', '17:00', 0],
                [7, '09:00', '16:00', 0],
            ],
            // Mon–Fri 10:00–16:00; weekend longer
            [
                [1, '10:00', '16:00', 0],
                [2, '10:00', '16:00', 0],
                [3, '10:00', '16:00', 0],
                [4, '10:00', '16:00', 0],
                [5, '10:00', '16:00', 0],
                [6, '09:00', '17:00', 0],
                [7, '09:00', '17:00', 0],
            ],
            // Wed–Sun only
            [
                [3, '09:30', '17:00', 0],
                [4, '09:30', '17:00', 0],
                [5, '09:30', '17:00', 0],
                [6, '09:00', '17:30', 0],
                [7, '10:00', '15:00', 0],
            ],
            // All week; Thursday split (morning + evening)
            [
                [1, '10:00', '16:00', 0],
                [2, '10:00', '16:00', 0],
                [3, '10:00', '16:00', 0],
                [4, '09:00', '13:00', 0],
                [4, '14:00', '20:00', 1],
                [5, '09:00', '17:00', 0],
                [6, '09:00', '17:00', 0],
                [7, '10:00', '15:00', 0],
            ],
            // Typical club: closed Mon–Tue; rest of week
            [
                [3, '10:00', '17:00', 0],
                [4, '10:00', '17:00', 0],
                [5, '10:00', '17:00', 0],
                [6, '09:00', '17:00', 0],
                [7, '09:00', '16:00', 0],
            ],
        ];
    }

    public function run(): void
    {
        OpeningHour::query()->delete();

        ShootingGround::query()->update(['opening_hours' => null]);

        $grounds = ShootingGround::query()->orderBy('name')->get();
        if ($grounds->isEmpty()) {
            $this->command?->warn('No shooting grounds — run ShootingGroundSeeder first.');

            return;
        }

        $n = count($this->templates);

        foreach ($grounds as $ground) {
            $idx = (crc32($ground->slug) % $n + $n) % $n;
            foreach ($this->templates[$idx] as $slot) {
                $sort = $slot[3] ?? 0;
                OpeningHour::create([
                    'shooting_ground_id' => $ground->id,
                    'weekday' => $slot[0],
                    'opens_at' => $slot[1],
                    'closes_at' => $slot[2],
                    'sort_order' => $sort,
                ]);
            }
        }

        $this->command?->info('Seeded opening hours for '.$grounds->count().' grounds.');
    }
}
