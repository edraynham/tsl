<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    /**
     * Stable placeholder images (Lorem Picsum seeded by slug — avoids hotlink breakage from stock sites).
     */
    private static function photoUrlForSlug(string $slug): string
    {
        return 'https://picsum.photos/seed/tsl-instructor-'.$slug.'/1200/900';
    }

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
                'website' => 'https://example.com/instructors/sarah-mitchell',
            ],
            [
                'name' => 'James Chen',
                'slug' => 'james-chen',
                'headline' => 'Olympic skeet · performance coaching',
                'bio' => "James works with ambitious skeet shots who want structured training plans and video review.\n\nHe runs regular group clinics in the Midlands and one-to-one days by request.",
                'city' => 'Solihull',
                'county' => 'West Midlands',
                'website' => null,
            ],
            [
                'name' => 'Emma Wright',
                'slug' => 'emma-wright',
                'headline' => 'Beginners & families · safety first',
                'bio' => "Emma focuses on welcoming new shots to the sport with calm, clear instruction — ideal for first lessons and confidence building.\n\nGroup introductions and gift experiences are available.",
                'city' => 'York',
                'county' => 'North Yorkshire',
                'website' => 'https://example.com/instructors/emma-wright',
            ],
            [
                'name' => 'David Hughes',
                'slug' => 'david-hughes',
                'headline' => 'Sporting · game days',
                'bio' => "Former competitive sporting shot turned coach; David helps shooters translate practice-ground form into driven and simulated game days.\n\nBased in Perthshire with travel across Scotland.",
                'city' => 'Perth',
                'county' => 'Perthshire',
                'website' => null,
            ],
            [
                'name' => 'Lucy Foster',
                'slug' => 'lucy-foster',
                'headline' => 'Ladies’ coaching · CPSA',
                'bio' => "Lucy runs ladies-only coaching days and mixed group sessions with an emphasis on technique, fitness, and fun.\n\nShe is based in Devon and visits grounds in the South West.",
                'city' => 'Exeter',
                'county' => 'Devon',
                'website' => 'https://example.com/instructors/lucy-foster',
            ],
            [
                'name' => 'Tom Aldridge',
                'slug' => 'tom-aldridge',
                'headline' => 'DTL & English Skeet · club coaching',
                'bio' => "Tom has run squad training for county teams and helps club shots tighten their averages on DTL and skeet.\n\nHe coaches across East Anglia and welcomes improvers aiming for classification upgrades.",
                'city' => 'Norwich',
                'county' => 'Norfolk',
                'website' => null,
            ],
            [
                'name' => 'Rachel Owen',
                'slug' => 'rachel-owen',
                'headline' => 'Junior & academy pathways',
                'bio' => "Rachel works with young shots and parents to build safe habits, confidence, and competition readiness.\n\nShe partners with grounds in Lancashire and Cheshire for regular junior sessions.",
                'city' => 'Chester',
                'county' => 'Cheshire',
                'website' => 'https://example.com/instructors/rachel-owen',
            ],
            [
                'name' => 'Marcus Bell',
                'slug' => 'marcus-bell',
                'headline' => 'FITASC · all-round gameshooting prep',
                'bio' => "Marcus blends FITASC coaching with practical preparation for high birds and walked-up days.\n\nHe travels to grounds in the North East and Scottish Borders by arrangement.",
                'city' => 'Newcastle upon Tyne',
                'county' => 'Tyne and Wear',
                'website' => null,
            ],
            [
                'name' => 'Helen Price',
                'slug' => 'helen-price',
                'headline' => 'Olympic trap · structured fundamentals',
                'bio' => "Helen breaks down Olympic trap technique into repeatable stages — setup, sight picture, and follow-through under finals pressure.\n\nBased near Cardiff with access to regional training facilities.",
                'city' => 'Cardiff',
                'county' => 'South Glamorgan',
                'website' => 'https://example.com/instructors/helen-price',
            ],
            [
                'name' => 'Chris Okonkwo',
                'slug' => 'chris-okonkwo',
                'headline' => 'Sporting · video analysis',
                'bio' => "Chris uses slow-motion review and pattern boards to fix root causes rather than quick fixes.\n\nHalf-day and full-day sessions around the Home Counties; corporate groups welcome.",
                'city' => 'St Albans',
                'county' => 'Hertfordshire',
                'website' => null,
            ],
            [
                'name' => 'Anna Fraser',
                'slug' => 'anna-fraser',
                'headline' => 'Compak · Sporting clays',
                'bio' => "Anna coaches shooters stepping from club level into registered competitions, with emphasis on routine and mental rehearsal.\n\nRegular slots at central Scotland grounds; online planning calls available.",
                'city' => 'Stirling',
                'county' => 'Stirlingshire',
                'website' => 'https://example.com/instructors/anna-fraser',
            ],
            [
                'name' => 'Rob Kent',
                'slug' => 'rob-kent',
                'headline' => 'Double trap · experienced competitor',
                'bio' => "Rob brings decades of competition experience to one-to-one coaching and small squads.\n\nNow focused on coaching across Kent and Sussex with flexible weekday slots.",
                'city' => 'Tunbridge Wells',
                'county' => 'Kent',
                'website' => null,
            ],
            [
                'name' => 'Nina Patel',
                'slug' => 'nina-patel',
                'headline' => 'Beginners to intermediate · all disciplines',
                'bio' => "Nina helps newcomers choose the right discipline and equipment, then builds skill with patient, structured lessons.\n\nShe teaches at several Midlands grounds and offers gift vouchers for introductions.",
                'city' => 'Leicester',
                'county' => 'Leicestershire',
                'website' => 'https://example.com/instructors/nina-patel',
            ],
        ];

        foreach ($rows as $row) {
            $slug = $row['slug'];
            Instructor::query()->updateOrCreate(
                ['slug' => $slug],
                array_merge($row, [
                    'user_id' => null,
                    'photo_url' => self::photoUrlForSlug($slug),
                ])
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
                    'photo_url' => self::photoUrlForSlug('ed-raynham'),
                    'website' => null,
                ]
            );
        }
    }
}
