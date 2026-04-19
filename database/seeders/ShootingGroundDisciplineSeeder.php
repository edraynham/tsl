<?php

namespace Database\Seeders;

use App\Models\Discipline;
use App\Models\ShootingGround;
use Illuminate\Database\Seeder;

class ShootingGroundDisciplineSeeder extends Seeder
{
    public function run(): void
    {
        $ids = Discipline::query()->orderBy('code')->pluck('id');
        if ($ids->isEmpty()) {
            $this->command?->warn('No disciplines — run DisciplineSeeder before ShootingGroundDisciplineSeeder.');

            return;
        }

        foreach (ShootingGround::query()->orderBy('name')->cursor() as $ground) {
            $n = 3 + (abs(crc32((string) $ground->slug)) % 6);
            $picked = $ids->shuffle()->take(min($n, $ids->count()))->values()->all();
            $ground->disciplines()->sync($picked);
        }
    }
}
