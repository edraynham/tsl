<?php

namespace Tests\Feature;

use App\Models\ShootingGround;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GroundsDirectoryDistanceFilterTest extends TestCase
{
    use RefreshDatabase;

    private function fakeNominatimNearLondon(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/search*' => Http::response([
                ['lat' => '51.5000', 'lon' => '-0.1200'],
            ], 200),
        ]);
    }

    public function test_distance_filter_limits_results_to_grounds_within_radius(): void
    {
        ShootingGround::query()->create([
            'name' => 'TSL_NEAR_TEST_GROUND',
            'slug' => 'near-ground',
            'city' => 'A',
            'county' => 'B',
            'latitude' => 51.5010,
            'longitude' => -0.1200,
        ]);

        ShootingGround::query()->create([
            'name' => 'TSL_FAR_TEST_GROUND',
            'slug' => 'far-ground',
            'city' => 'C',
            'county' => 'D',
            'latitude' => 54.0000,
            'longitude' => -0.1200,
        ]);

        $response = $this->get(route('grounds.index', [
            'user_lat' => 51.5000,
            'user_lng' => -0.1200,
            'distance' => '25',
            'sort' => 'distance',
        ]));

        $response->assertOk();
        $response->assertSee('TSL_NEAR_TEST_GROUND');
        $response->assertDontSee('TSL_FAR_TEST_GROUND');
    }

    public function test_postcode_query_geocodes_and_distance_filter_uses_that_origin(): void
    {
        $this->fakeNominatimNearLondon();

        ShootingGround::query()->create([
            'name' => 'TSL_NEAR_TEST_GROUND',
            'slug' => 'near-ground',
            'city' => 'A',
            'county' => 'B',
            'latitude' => 51.5010,
            'longitude' => -0.1200,
        ]);

        ShootingGround::query()->create([
            'name' => 'TSL_FAR_TEST_GROUND',
            'slug' => 'far-ground',
            'city' => 'C',
            'county' => 'D',
            'latitude' => 54.0000,
            'longitude' => -0.1200,
        ]);

        $response = $this->get(route('grounds.index', [
            'q' => 'SW1A 1AA',
            'distance' => '25',
            'sort' => 'distance',
        ]));

        $response->assertOk();
        $response->assertSee('TSL_NEAR_TEST_GROUND');
        $response->assertDontSee('TSL_FAR_TEST_GROUND');
    }

    public function test_postcode_origin_overrides_request_user_coordinates(): void
    {
        $this->fakeNominatimNearLondon();

        ShootingGround::query()->create([
            'name' => 'TSL_NEAR_TEST_GROUND',
            'slug' => 'near-ground',
            'city' => 'A',
            'county' => 'B',
            'latitude' => 51.5010,
            'longitude' => -0.1200,
        ]);

        ShootingGround::query()->create([
            'name' => 'TSL_FAR_TEST_GROUND',
            'slug' => 'far-ground',
            'city' => 'C',
            'county' => 'D',
            'latitude' => 54.0000,
            'longitude' => -0.1200,
        ]);

        $response = $this->get(route('grounds.index', [
            'q' => 'SW1A 1AA',
            'user_lat' => 54.0000,
            'user_lng' => -0.1200,
            'distance' => '25',
            'sort' => 'distance',
        ]));

        $response->assertOk();
        $response->assertSee('TSL_NEAR_TEST_GROUND');
        $response->assertDontSee('TSL_FAR_TEST_GROUND');
    }

    public function test_failed_postcode_geocode_shows_message(): void
    {
        Http::fake([
            'https://nominatim.openstreetmap.org/search*' => Http::response([], 200),
        ]);

        $response = $this->get(route('grounds.index', [
            'q' => 'SW1A 1AA',
        ]));

        $response->assertOk();
        $response->assertSee('find that postcode. Check spelling', false);
    }
}
