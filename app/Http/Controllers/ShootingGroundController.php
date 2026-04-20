<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Discipline;
use App\Models\Facility;
use App\Models\ShootingGround;
use App\Support\Geo;
use App\Support\Weather;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShootingGroundController extends Controller
{
    private const UK_POSTCODE_REGEX = '/^[A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2}$/i';

    /** @var list<int> */
    private const ALLOWED_RADIUS_MILES = [10, 25, 50, 75];

    /**
     * JSON autocomplete for directory search (e.g. homepage hero).
     */
    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $q = mb_substr($q, 0, 80);
        $like = '%'.$q.'%';

        $grounds = ShootingGround::query()
            ->where(function ($sub) use ($like) {
                $sub->where('name', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('county', 'like', $like)
                    ->orWhere('postcode', 'like', $like);
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['name', 'slug', 'city', 'county', 'photo_url']);

        $suggestions = $grounds->map(function (ShootingGround $g) {
            $subtitle = collect([$g->city, $g->county])->filter()->implode(' · ');

            return [
                'name' => $g->name,
                'subtitle' => $subtitle,
                'photo_url' => $g->coverPhotoUrl(),
                'url' => route('grounds.show', $g),
            ];
        })->values()->all();

        return response()->json(['suggestions' => $suggestions]);
    }

    public function index(Request $request): View
    {
        $disciplineFilter = $this->normalizedDisciplineIds($request);
        $facilityFilter = $this->normalizedFacilityIds($request);
        $q = trim((string) $request->input('q', ''));

        $requestUserLat = $request->input('user_lat');
        $requestUserLng = $request->input('user_lng');

        $postcodeCoords = null;
        $postcodeLookupFailed = false;

        if ($q !== '' && $this->looksLikeUkPostcode($q)) {
            $postcodeCoords = Geo::geocodeUkSearch($q);
            if ($postcodeCoords === null) {
                $postcodeLookupFailed = true;
            }
        }

        if ($postcodeCoords !== null) {
            $userLat = $postcodeCoords['lat'];
            $userLng = $postcodeCoords['lng'];
        } else {
            $userLat = $requestUserLat;
            $userLng = $requestUserLng;
        }

        $hasUserGeo = Geo::validUserCoords($userLat, $userLng);
        $isPostcodeProximitySearch = $postcodeCoords !== null;

        $filtered = $this->filteredQuery(
            $request,
            $disciplineFilter,
            $facilityFilter,
            $isPostcodeProximitySearch
        );

        $radiusMiles = $this->normalizedRadiusMiles($request);
        if ($radiusMiles !== null && $hasUserGeo) {
            $this->applyWithinMilesRadius($filtered, (float) $userLat, (float) $userLng, $radiusMiles);
        }

        if ($request->filled('sort')) {
            $sort = (string) $request->input('sort');
        } elseif ($isPostcodeProximitySearch) {
            $sort = 'distance';
        } elseif ($hasUserGeo) {
            $sort = 'distance';
        } else {
            $sort = 'az';
        }

        $effectiveSort = ($sort === 'distance' && ! $hasUserGeo) ? 'az' : $sort;

        $sortedQuery = match ($effectiveSort) {
            'za' => (clone $filtered)->orderByDesc('name'),
            'county' => (clone $filtered)->orderBy('county')->orderBy('name'),
            'distance' => $this->applyDistanceSort((clone $filtered), (float) $userLat, (float) $userLng),
            default => (clone $filtered)->orderBy('name'),
        };

        $viewMode = $request->input('view', 'list') === 'map' ? 'map' : 'list';

        $grounds = $viewMode === 'list'
            ? $sortedQuery->paginate(24)->withQueryString()
            : null;

        $mapMarkers = (clone $filtered)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('name')
            ->get(['name', 'slug', 'latitude', 'longitude', 'county', 'city'])
            ->map(fn (ShootingGround $g) => [
                'lat' => (float) $g->latitude,
                'lng' => (float) $g->longitude,
                'name' => $g->name,
                'slug' => $g->slug,
                'county' => $g->county,
                'city' => $g->city,
                'url' => route('grounds.show', $g),
            ])
            ->values();

        $filteredTotal = (clone $filtered)->count();
        $mapPinsCount = $mapMarkers->count();
        $missingCoordsCount = max(0, $filteredTotal - $mapPinsCount);

        return view('grounds.index', [
            'grounds' => $grounds,
            'q' => $q,
            'sort' => $sort,
            'facilityFilter' => $facilityFilter,
            'allFacilities' => Facility::query()->orderBy('name')->get(),
            'disciplineFilter' => $disciplineFilter,
            'allDisciplines' => Discipline::query()->orderBy('name')->get(),
            'viewMode' => $viewMode,
            'mapMarkers' => $mapMarkers,
            'mapPinsCount' => $mapPinsCount,
            'missingCoordsCount' => $missingCoordsCount,
            'filteredTotal' => $filteredTotal,
            'hasUserGeo' => $hasUserGeo,
            'userLat' => $hasUserGeo ? (float) $userLat : null,
            'userLng' => $hasUserGeo ? (float) $userLng : null,
            'distanceMiles' => $radiusMiles,
            'isPostcodeProximitySearch' => $isPostcodeProximitySearch,
            'postcodeLookupFailed' => $postcodeLookupFailed,
        ]);
    }

    public function show(ShootingGround $shooting_ground): View
    {
        $shooting_ground->load(['openingHours', 'disciplines', 'facilities']);
        $shooting_ground->loadCount('owners');

        $competitions = $shooting_ground->competitions()
            ->with('canonicalDiscipline')
            ->orderBy('starts_at')
            ->get();

        $upcomingCompetitions = $competitions
            ->filter(fn (Competition $c) => ! $c->isPast())
            ->values();

        $pastCompetitions = $competitions
            ->filter(fn (Competition $c) => $c->isPast())
            ->sortByDesc(fn (Competition $c) => $c->starts_at)
            ->take(24)
            ->values();

        $weather = null;
        if ($shooting_ground->latitude !== null && $shooting_ground->longitude !== null) {
            $weather = Weather::currentForGround(
                (float) $shooting_ground->latitude,
                (float) $shooting_ground->longitude,
                $shooting_ground->id,
            );
        }

        return view('grounds.show', [
            'ground' => $shooting_ground,
            'weather' => $weather,
            'upcomingCompetitions' => $upcomingCompetitions,
            'pastCompetitions' => $pastCompetitions,
        ]);
    }

    /**
     * Order by planar distance in lat/lng space (monotonic with true distance at UK scale; works in SQLite/MySQL).
     */
    private function applyDistanceSort(Builder $query, float $userLat, float $userLng): Builder
    {
        return $query
            ->orderByRaw('CASE WHEN latitude IS NULL OR longitude IS NULL THEN 1 ELSE 0 END ASC')
            ->orderByRaw(
                '((latitude - ?) * (latitude - ?) + (longitude - ?) * (longitude - ?)) ASC',
                [$userLat, $userLat, $userLng, $userLng]
            )
            ->orderBy('name');
    }

    /**
     * Great-circle distance filter (miles). Requires non-null ground coordinates.
     */
    private function applyWithinMilesRadius(Builder $query, float $userLat, float $userLng, float $radiusMiles): void
    {
        $earthMiles = 3958.7613;
        $d2r = M_PI / 180.0;

        $driver = $query->getConnection()->getDriverName();
        // SQLite scalar clamp uses min/max; MySQL/MariaDB/Postgres use least/greatest.
        $clamp = match ($driver) {
            'sqlite' => 'min(1.0, max(-1.0,
                    cos(? * ?) * cos(latitude * ?) * cos((longitude - ?) * ?)
                    + sin(? * ?) * sin(latitude * ?)
                ))',
            default => 'least(1.0, greatest(-1.0,
                    cos(? * ?) * cos(latitude * ?) * cos((longitude - ?) * ?)
                    + sin(? * ?) * sin(latitude * ?)
                ))',
        };

        $radiusMilesInt = (int) $radiusMiles;
        $query->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw(
                "({$earthMiles} * acos({$clamp})) <= {$radiusMilesInt}",
                [
                    $userLat, $d2r,
                    $d2r,
                    $userLng, $d2r,
                    $userLat, $d2r,
                    $d2r,
                ]
            );
    }

    private function normalizedRadiusMiles(Request $request): ?float
    {
        $raw = $request->input('distance');
        if ($raw === null || $raw === '') {
            return null;
        }

        $miles = (int) $raw;

        return in_array($miles, self::ALLOWED_RADIUS_MILES, true) ? (float) $miles : null;
    }

    /**
     * @return list<int>
     */
    private function normalizedDisciplineIds(Request $request): array
    {
        $raw = $request->input('discipline', []);
        if (! is_array($raw)) {
            return [];
        }

        $ids = [];
        foreach ($raw as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return list<int>
     */
    private function normalizedFacilityIds(Request $request): array
    {
        $raw = $request->input('facility', []);
        if (! is_array($raw)) {
            return [];
        }

        $ids = [];
        $slugs = [];
        foreach ($raw as $id) {
            if (is_numeric($id)) {
                $id = (int) $id;
                if ($id > 0) {
                    $ids[] = $id;
                }

                continue;
            }

            $slug = trim((string) $id);
            if ($slug !== '') {
                $slugs[] = $slug;
            }
        }

        if ($slugs !== []) {
            $slugIds = Facility::query()
                ->whereIn('slug', $slugs)
                ->pluck('id')
                ->all();
            $ids = [...$ids, ...$slugIds];
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  list<int>  $disciplineIds
     * @param  list<int>  $facilityIds
     */
    private function filteredQuery(
        Request $request,
        array $disciplineIds = [],
        array $facilityIds = [],
        bool $skipTextSearch = false
    ): Builder {
        $query = ShootingGround::query()->with(['disciplines', 'facilities']);

        $q = trim((string) $request->input('q', ''));
        if ($q !== '' && ! $skipTextSearch) {
            $like = '%'.$q.'%';
            $query->where(function ($sub) use ($like) {
                $sub->where('name', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('county', 'like', $like)
                    ->orWhere('postcode', 'like', $like);
            });
        }

        if ($facilityIds !== []) {
            $query->whereHas('facilities', function ($q) use ($facilityIds): void {
                $q->whereIn('facilities.id', $facilityIds);
            });
        }

        if ($disciplineIds !== []) {
            $query->whereHas('disciplines', function ($q) use ($disciplineIds): void {
                $q->whereIn('disciplines.id', $disciplineIds);
            });
        }

        return $query;
    }

    private function looksLikeUkPostcode(string $value): bool
    {
        return preg_match(self::UK_POSTCODE_REGEX, trim($value)) === 1;
    }
}
