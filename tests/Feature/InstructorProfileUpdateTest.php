<?php

namespace Tests\Feature;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_instructor_profile_gets_404_on_edit(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'registration_roles_completed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('account.instructor.edit'));

        $response->assertNotFound();
    }

    public function test_instructor_owner_can_update_profile(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'registration_roles_completed_at' => now(),
        ]);

        $instructor = Instructor::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Coach',
            'slug' => 'test-coach',
            'headline' => 'Old headline',
            'bio' => null,
            'city' => 'York',
            'county' => 'Yorkshire',
            'photo_url' => null,
            'website' => null,
        ]);

        $response = $this->actingAs($user)->put(route('account.instructor.update'), [
            'name' => 'Test Coach Updated',
            'slug' => 'test-coach',
            'headline' => 'New headline',
            'bio' => 'Bio text',
            'city' => 'Leeds',
            'county' => 'Yorkshire',
            'photo_url' => null,
            'website' => null,
        ]);

        $response->assertRedirect(route('account.instructor'));
        $instructor->refresh();
        $this->assertSame('Test Coach Updated', $instructor->name);
        $this->assertSame('New headline', $instructor->headline);
        $this->assertSame('Leeds', $instructor->city);
    }
}
