<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\Discipline;
use App\Models\ShootingGround;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        Competition::query()->delete();

        $grounds = ShootingGround::query()->orderBy('name')->get();
        if ($grounds->isEmpty()) {
            $this->command?->warn('No shooting grounds found — run ShootingGroundSeeder before CompetitionSeeder.');

            return;
        }

        $disciplineNames = Discipline::query()->orderBy('code')->pluck('name');
        if ($disciplineNames->isEmpty()) {
            $disciplineNames = collect([
                'English Sporting', 'English Skeet', 'FITASC Sporting', 'Compak Sporting', 'Olympic Trap', 'Down The Line',
            ]);
        }

        $titleParts = [
            ['Spring Open', '100-bird sporting open to all classes.'],
            ['County Championship', 'County colours and selection points. Dress code applies.'],
            ['Charity Shoot', 'Raffle, barbecue, and team pairs.'],
            ['Midsummer Cup', 'Full course with championship referees on stand.'],
            ['Ladies & Juniors Day', 'Categories for Ladies, Juniors, and Veterans.'],
            ['Winter League Final', 'Presentation dinner and trophies.'],
            ['Novice Friendly', 'Coaches on pegs; club guns by arrangement.'],
            ['Autumn Qualifier', 'Regional qualifier — CPSA rules.'],
            ['Corporate Challenge', 'Teams of four; hospitality package available.'],
            ['Charity DTL', 'Down-the-line marathon for a local cause.'],
            ['Skeet Marathon', 'Olympic skeet squads — book early.'],
            ['FITASC Classic', 'International layout; practice stands from 08:00.'],
            ['Compak Blast', 'Fast compak — eye and ear protection mandatory.'],
            ['Club Trophy', 'Members and guests welcome.'],
            ['Bank Holiday Open', 'Extended hours; food van on site.'],
        ];

        $count = 0;
        $dayCursor = 10;

        foreach ($titleParts as $i => [$titleSuffix, $summary]) {
            $ground = $grounds[$i % $grounds->count()];
            $discipline = $disciplineNames[$i % $disciplineNames->count()];

            $daysOffset = $dayCursor + ($i * 11) % 17;
            $dayCursor = ($dayCursor + 7) % 14 + 8;

            $starts = now()->addDays($daysOffset)->setTime(9 + ($i % 2), 30 * ($i % 2));

            $fullTitle = $titleSuffix.' — '.$ground->name;
            $slug = Str::slug($ground->slug.'-'.$titleSuffix.'-'.$i);

            $disc = $this->disciplinePayload($discipline);

            Competition::create([
                'shooting_ground_id' => $ground->id,
                'title' => $fullTitle,
                'slug' => $slug,
                'summary' => $summary.' Check with the ground for entries and squadding.',
                'starts_at' => $starts,
                'discipline_id' => $disc['discipline_id'],
                'discipline' => $disc['discipline'],
                'external_url' => $ground->website,
                'cpsa_registered' => $i % 3 !== 1,
            ]);
            $count++;
        }

        // Extra random scatter so more grounds get multiple events
        $extras = [
            ['Midweek Sporting', 'Midweek card — quieter stands.', 'English Sporting', 5],
            ['Sunday Skirmish', 'Sporting compak mix; bacon rolls from 08:30.', 'Compak Sporting', 12],
            ['Pro-Am Pairs', 'Drawn pairs; handicap optional.', 'English Sporting', 22],
            ['Twilight Trap', 'Floodlit stands from dusk.', 'Down The Line', 38],
            ['Young Shots Intro', 'Safety briefing and coached round.', 'English Sporting', 44],
        ];

        foreach ($extras as $j => [$title, $summary, $disc, $days]) {
            $ground = $grounds[($j * 3 + 7) % $grounds->count()];
            $starts = now()->addDays($days + $j * 2)->setTime(10, 0);
            $discPayload = $this->disciplinePayload($disc);
            Competition::create([
                'shooting_ground_id' => $ground->id,
                'title' => $title.' @ '.$ground->name,
                'slug' => Str::slug($ground->slug.'-extra-'.$j.'-'.$title),
                'summary' => $summary,
                'starts_at' => $starts,
                'discipline_id' => $discPayload['discipline_id'],
                'discipline' => $discPayload['discipline'],
                'external_url' => $ground->website,
                'cpsa_registered' => $j % 2 === 0,
            ]);
            $count++;
        }

        $this->command?->info("Seeded {$count} competitions.");
    }

    /**
     * @return array{discipline_id: int|null, discipline: string|null}
     */
    private function disciplinePayload(string $name): array
    {
        $trimmed = trim($name);
        if ($trimmed === '') {
            return ['discipline_id' => null, 'discipline' => null];
        }

        $id = Discipline::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($trimmed)])
            ->value('id');

        if ($id !== null) {
            $canonical = Discipline::query()->whereKey($id)->value('name');

            return ['discipline_id' => (int) $id, 'discipline' => $canonical];
        }

        return ['discipline_id' => null, 'discipline' => $trimmed];
    }
}
