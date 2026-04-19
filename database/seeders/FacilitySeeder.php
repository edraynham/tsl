<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FacilitySeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private const NAMES = [
        'Cafe',
        'Gun hire',
        'Clubhouse',
        'Pro shop',
        'Catering',
        'Parking',
        'Toilets',
        'Coaching',
        'Corporate hospitality',
        'Covered stands',
        'Wheelchair access',
        'Hearing loop',
        'Dog friendly',
        'Accommodation nearby',
    ];

    public function run(): void
    {
        foreach (self::NAMES as $name) {
            $slug = Str::slug($name);
            Facility::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name],
            );
        }
    }
}
