<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\ShootingGround;
use Illuminate\Database\Seeder;

class ShootingGroundFacilitySeeder extends Seeder
{
    public function run(): void
    {
        $ids = Facility::query()->orderBy('name')->pluck('id');
        if ($ids->isEmpty()) {
            $this->command?->warn('No facilities — run FacilitySeeder before ShootingGroundFacilitySeeder.');

            return;
        }

        foreach (ShootingGround::query()->orderBy('name')->cursor() as $ground) {
            $n = 2 + (abs(crc32((string) $ground->slug)) % 7);
            $picked = $ids->shuffle()->take(min($n, $ids->count()))->values()->all();
            $ground->facilities()->sync($picked);
        }
    }
}
