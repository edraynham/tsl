<?php

namespace Tests\Feature;

use App\Mail\CompetitionNewRegistrationMail;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\CompetitionSquad;
use App\Models\ShootingGround;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class CompetitionRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function seedGroundOwnerCompetition(bool $squadded = false): array
    {
        $ground = ShootingGround::query()->create([
            'name' => 'TSL Test Ground',
            'slug' => 'tsl-test-ground-'.Str::lower(Str::random(6)),
        ]);

        $owner = User::factory()->create([
            'email' => 'owner-'.Str::lower(Str::random(6)).'@example.com',
            'email_verified_at' => now(),
            'registration_roles_completed_at' => now(),
        ]);
        $owner->ownedShootingGrounds()->sync([$ground->id]);

        $competition = Competition::query()->create([
            'shooting_ground_id' => $ground->id,
            'title' => 'Test Competition',
            'slug' => 'test-comp-'.Str::lower(Str::random(8)),
            'summary' => null,
            'starts_at' => now()->addWeek(),
            'discipline_id' => null,
            'discipline' => null,
            'external_url' => null,
            'cpsa_registered' => false,
            'registration_format' => $squadded ? Competition::REGISTRATION_SQUADDED : Competition::REGISTRATION_OPEN,
            'open_max_participants' => $squadded ? null : 2,
        ]);

        $squads = collect();
        if ($squadded) {
            $squads->push(CompetitionSquad::query()->create([
                'competition_id' => $competition->id,
                'starts_at' => now()->addWeek()->setTime(9, 0),
                'max_participants' => 1,
                'sort_order' => 0,
            ]));
            $squads->push(CompetitionSquad::query()->create([
                'competition_id' => $competition->id,
                'starts_at' => now()->addWeek()->setTime(10, 0),
                'max_participants' => 6,
                'sort_order' => 1,
            ]));
        }

        return compact('ground', 'owner', 'competition', 'squads');
    }

    public function test_open_registration_succeeds_and_sends_mail_to_verified_owner(): void
    {
        Mail::fake();
        ['ground' => $ground, 'owner' => $owner, 'competition' => $competition] = $this->seedGroundOwnerCompetition(false);

        $response = $this->post(route('competitions.book.store', $competition), [
            'cpsa_number' => '12345',
            'entrant_name' => 'Alex Shooter',
            'email' => 'alex@example.com',
            'telephone' => '07123456789',
        ]);

        $response->assertRedirect(route('competitions.show', $competition));
        $this->assertDatabaseHas('competition_registrations', [
            'competition_id' => $competition->id,
            'competition_squad_id' => null,
            'cpsa_number' => '12345',
            'party_size' => 1,
        ]);

        Mail::assertSent(CompetitionNewRegistrationMail::class, function (CompetitionNewRegistrationMail $mail) use ($owner, $competition) {
            return $mail->hasTo($owner->email)
                && $mail->competition->is($competition)
                && $mail->registrations->count() === 1;
        });
    }

    public function test_open_registration_rejects_duplicate_cpsa_number(): void
    {
        Mail::fake();
        ['competition' => $competition] = $this->seedGroundOwnerCompetition(false);

        CompetitionRegistration::query()->create([
            'competition_id' => $competition->id,
            'competition_squad_id' => null,
            'cpsa_number' => '999',
            'entrant_name' => 'Existing',
            'email' => 'ex@example.com',
            'telephone' => '07000000000',
            'party_size' => 1,
        ]);

        $response = $this->from(route('competitions.book', $competition))->post(route('competitions.book.store', $competition), [
            'cpsa_number' => '999',
            'entrant_name' => 'Other',
            'email' => 'other@example.com',
            'telephone' => '07111111111',
        ]);

        $response->assertSessionHasErrors('cpsa_number');
        $this->assertSame(1, CompetitionRegistration::query()->where('competition_id', $competition->id)->count());
    }

    public function test_open_registration_rejects_when_at_capacity(): void
    {
        Mail::fake();
        ['competition' => $competition] = $this->seedGroundOwnerCompetition(false);

        CompetitionRegistration::query()->create([
            'competition_id' => $competition->id,
            'competition_squad_id' => null,
            'cpsa_number' => '1',
            'entrant_name' => 'A',
            'email' => 'a@example.com',
            'telephone' => '01',
            'party_size' => 1,
        ]);
        CompetitionRegistration::query()->create([
            'competition_id' => $competition->id,
            'competition_squad_id' => null,
            'cpsa_number' => '2',
            'entrant_name' => 'B',
            'email' => 'b@example.com',
            'telephone' => '02',
            'party_size' => 1,
        ]);

        $response = $this->from(route('competitions.book', $competition))->post(route('competitions.book.store', $competition), [
            'cpsa_number' => '3',
            'entrant_name' => 'C',
            'email' => 'c@example.com',
            'telephone' => '03',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(2, CompetitionRegistration::query()->where('competition_id', $competition->id)->count());
    }

    public function test_squadded_registration_respects_squad_capacity(): void
    {
        Mail::fake();
        ['competition' => $competition, 'squads' => $squads] = $this->seedGroundOwnerCompetition(true);
        $squadA = $squads->first();

        CompetitionRegistration::query()->create([
            'competition_id' => $competition->id,
            'competition_squad_id' => $squadA->id,
            'cpsa_number' => '100',
            'entrant_name' => 'Taken',
            'email' => 't@example.com',
            'telephone' => '0',
            'party_size' => 1,
        ]);

        $response = $this->from(route('competitions.book', $competition))->post(route('competitions.book.store', $competition), [
            'competition_squad_id' => $squadA->id,
            'party_size' => 1,
            'entrants' => [
                [
                    'cpsa_number' => '200',
                    'entrant_name' => 'Late',
                    'email' => 'late@example.com',
                    'telephone' => '1',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('party_size');
    }

    public function test_squadded_booking_party_size_reserves_multiple_places(): void
    {
        Mail::fake();
        ['competition' => $competition, 'squads' => $squads] = $this->seedGroundOwnerCompetition(true);
        $squadB = $squads->last();

        $this->post(route('competitions.book.store', $competition), [
            'competition_squad_id' => $squadB->id,
            'party_size' => 3,
            'entrants' => [
                [
                    'cpsa_number' => '300',
                    'entrant_name' => 'Party Lead',
                    'email' => 'party@example.com',
                    'telephone' => '07700900000',
                ],
                ['cpsa_number' => '301', 'entrant_name' => 'Guest Two'],
                ['cpsa_number' => '302', 'entrant_name' => 'Guest Three'],
            ],
        ])->assertRedirect(route('competitions.show', $competition));

        $this->assertSame(3, CompetitionRegistration::query()->where('competition_id', $competition->id)->where('competition_squad_id', $squadB->id)->count());
        foreach (['300', '301', '302'] as $cpsa) {
            $this->assertDatabaseHas('competition_registrations', [
                'competition_id' => $competition->id,
                'competition_squad_id' => $squadB->id,
                'party_size' => 1,
                'cpsa_number' => $cpsa,
            ]);
        }

        Mail::assertSent(CompetitionNewRegistrationMail::class, function (CompetitionNewRegistrationMail $mail) {
            return $mail->registrations->count() === 3;
        });

        $this->post(route('competitions.book.store', $competition), [
            'competition_squad_id' => $squadB->id,
            'party_size' => 4,
            'entrants' => [
                ['cpsa_number' => '400', 'entrant_name' => 'A', 'email' => 'big@example.com', 'telephone' => '07700900001'],
                ['cpsa_number' => '401', 'entrant_name' => 'B'],
                ['cpsa_number' => '402', 'entrant_name' => 'C'],
                ['cpsa_number' => '403', 'entrant_name' => 'D'],
            ],
        ])->assertSessionHasErrors('party_size');
    }

    public function test_squadded_booking_rejects_duplicate_cpsa_within_party(): void
    {
        ['competition' => $competition, 'squads' => $squads] = $this->seedGroundOwnerCompetition(true);
        $squadB = $squads->last();

        $response = $this->from(route('competitions.book', $competition))->post(route('competitions.book.store', $competition), [
            'competition_squad_id' => $squadB->id,
            'party_size' => 2,
            'entrants' => [
                ['cpsa_number' => '500', 'entrant_name' => 'A', 'email' => 'a@example.com', 'telephone' => '01'],
                ['cpsa_number' => '500', 'entrant_name' => 'B'],
            ],
        ]);

        $response->assertSessionHasErrors('entrants');
        $this->assertSame(0, CompetitionRegistration::query()->where('competition_id', $competition->id)->where('cpsa_number', '500')->count());
    }

    public function test_owner_can_view_registrations_list(): void
    {
        ['ground' => $ground, 'owner' => $owner, 'competition' => $competition] = $this->seedGroundOwnerCompetition(false);
        CompetitionRegistration::query()->create([
            'competition_id' => $competition->id,
            'competition_squad_id' => null,
            'cpsa_number' => '55',
            'entrant_name' => 'Pat',
            'email' => 'pat@example.com',
            'telephone' => '99',
            'party_size' => 1,
        ]);

        $response = $this->actingAs($owner)->get(
            route('account.competitions.registrations.index', $competition)
        );

        $response->assertOk();
        $response->assertSee('Pat');
        $response->assertSee('55');
    }

    public function test_non_owner_cannot_view_registrations_list(): void
    {
        ['ground' => $ground, 'competition' => $competition] = $this->seedGroundOwnerCompetition(false);
        $other = User::factory()->create();

        $response = $this->actingAs($other)->get(
            route('account.competitions.registrations.index', $competition)
        );

        $response->assertForbidden();
    }
}
