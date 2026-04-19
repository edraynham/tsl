<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'name' => 'Sarah Mitchell',
                'slug' => 'sarah-mitchell',
                'headline' => 'CPSA qualified · Sporting & FITASC',
                'bio' => "Sarah has coached club and county-level shooters for over fifteen years. She specialises in building repeatable gun mounts and readable targets under pressure.\n\nLessons are available at grounds in Gloucestershire and by arrangement elsewhere.",
                'city' => 'Cheltenham',
                'county' => 'Gloucestershire',
                'photo_url' => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?auto=format&fit=crop&w=1200&q=80',
                'website' => 'https://example.com/instructors/sarah-mitchell',
            ],
            [
                'name' => 'James Chen',
                'slug' => 'james-chen',
                'headline' => 'Olympic skeet · performance coaching',
                'bio' => "James works with ambitious skeet shots who want structured training plans and video review.\n\nHe runs regular group clinics in the Midlands and one-to-one days by request.",
                'city' => 'Solihull',
                'county' => 'West Midlands',
                'photo_url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?auto=format&fit=crop&w=1200&q=80',
                'website' => null,
            ],
            [
                'name' => 'Emma Wright',
                'slug' => 'emma-wright',
                'headline' => 'Beginners & families · safety first',
                'bio' => "Emma focuses on welcoming new shots to the sport with calm, clear instruction — ideal for first lessons and confidence building.\n\nGroup introductions and gift experiences are available.",
                'city' => 'York',
                'county' => 'North Yorkshire',
                'photo_url' => 'https://images.unsplash.com/photo-1517649763962-0c62306601db?auto=format&fit=crop&w=1200&q=80',
                'website' => 'https://example.com/instructors/emma-wright',
            ],
            [
                'name' => 'David Hughes',
                'slug' => 'david-hughes',
                'headline' => 'Sporting · game days',
                'bio' => "Former competitive sporting shot turned coach; David helps shooters translate practice-ground form into driven and simulated game days.\n\nBased in Perthshire with travel across Scotland.",
                'city' => 'Perth',
                'county' => 'Perthshire',
                'photo_url' => 'https://images.unsplash.com/photo-1505142468610-359e7d316be0?auto=format&fit=crop&w=1200&q=80',
                'website' => null,
            ],
            [
                'name' => 'Lucy Foster',
                'slug' => 'lucy-foster',
                'headline' => 'Ladies’ coaching · CPSA',
                'bio' => "Lucy runs ladies-only coaching days and mixed group sessions with an emphasis on technique, fitness, and fun.\n\nShe is based in Devon and visits grounds in the South West.",
                'city' => 'Exeter',
                'county' => 'Devon',
                'photo_url' => 'https://images.unsplash.com/photo-1517649763962-0c62306601db?auto=format&fit=crop&w=1200&q=80',
                'website' => 'https://example.com/instructors/lucy-foster',
            ],
        ];

        foreach ($rows as $row) {
            Instructor::query()->updateOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, ['user_id' => null])
            );
        }

        $ed = User::query()->where('email', 'ed@behindthedot.com')->first();
        if ($ed !== null) {
            $ed->forceFill(['is_instructor' => true])->save();
            Instructor::query()->updateOrCreate(
                ['user_id' => $ed->id],
                [
                    'name' => trim($ed->first_name.' '.$ed->last_name),
                    'slug' => 'ed-raynham',
                    'headline' => 'Sporting · tuition for all levels',
                    'bio' => "Ed offers structured coaching sessions focused on technique, consistency, and enjoying time on the peg.\n\nContact via the site to arrange a lesson.",
                    'city' => 'Stroud',
                    'county' => 'Gloucestershire',
                    'photo_url' => null,
                    'website' => null,
                ]
            );
        }
    }
}
