<?php

namespace Database\Seeders;

use App\Models\Discipline;
use Illuminate\Database\Seeder;

class DisciplineSeeder extends Seeder
{
    /**
     * CPSA-style discipline list (name + short code).
     *
     * @var list<array{name: string, code: string}>
     */
    private const ROWS = [
        ['name' => 'All Round', 'code' => 'AR'],
        ['name' => 'Automatic Ball Trap', 'code' => 'ABT'],
        ['name' => 'Compak Sporting', 'code' => 'CSP'],
        ['name' => 'Double Rise', 'code' => 'DR'],
        ['name' => 'Double Trap', 'code' => 'DT'],
        ['name' => 'Down The Line', 'code' => 'DTL'],
        ['name' => 'English Skeet', 'code' => 'ESK'],
        ['name' => 'English Sporting', 'code' => 'ESP'],
        ['name' => 'FITASC Sporting', 'code' => 'SP'],
        ['name' => 'Handicap By Distance', 'code' => 'HD'],
        ['name' => 'Helice', 'code' => 'HEL'],
        ['name' => 'Olympic Skeet', 'code' => 'OSK'],
        ['name' => 'Olympic Trap', 'code' => 'TR'],
        ['name' => 'Single Barrel', 'code' => 'SB'],
        ['name' => 'Skeet Doubles', 'code' => 'SKD'],
        ['name' => 'Sportrap', 'code' => 'STR'],
        ['name' => 'Super Sporting', 'code' => 'SSP'],
        ['name' => 'Universal Trench', 'code' => 'UTR'],
    ];

    public function run(): void
    {
        foreach (self::ROWS as $row) {
            Discipline::query()->updateOrCreate(
                ['code' => $row['code']],
                ['name' => $row['name']],
            );
        }
    }
}
