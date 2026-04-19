<?php

namespace Database\Seeders;

use App\Models\ShootingGround;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (! User::query()->where('email', 'ed@behindthedot.com')->exists()) {
            User::factory()->create([
                'first_name' => 'Ed',
                'last_name' => 'Raynham',
                'email' => 'ed@behindthedot.com',
            ]);
        }

        $this->call(ShootingGroundSeeder::class);

        $owner = User::query()->firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'first_name' => 'Ground',
                'last_name' => 'Owner',
                'password' => null,
                'is_organiser' => true,
                'is_shooter' => true,
                'email_verified_at' => now(),
            ],
        );
        $owner->forceFill([
            'is_organiser' => true,
            'is_shooter' => true,
            'email_verified_at' => now(),
            'registration_roles_completed_at' => now(),
        ])->save();
        $owner->ownedShootingGrounds()->sync(
            ShootingGround::query()->orderBy('name')->limit(3)->pluck('id')->all()
        );
        $this->call(OpeningHourSeeder::class);
        $this->call(DisciplineSeeder::class);
        $this->call(ShootingGroundDisciplineSeeder::class);
        $this->call(FacilitySeeder::class);
        $this->call(ShootingGroundFacilitySeeder::class);
        $this->call(CompetitionSeeder::class);
        $this->call(InstructorSeeder::class);
    }
}
