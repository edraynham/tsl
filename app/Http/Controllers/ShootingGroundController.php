<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use App\Models\ShootingGround;
use App\Support\Geo;
use App\Support\Weather;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShootingGroundController extends Controller
{
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
            ->get(['name', 'slug', 'city', 'county']);

        $suggestions = $grounds->map(function (ShootingGround $g) {
            $subtitle = collect([$g->city, $g->county])->filter()->implode(' · ');

            return [
                'name' => $g->name,
                'subtitle' => $subtitle,
                'url' => route('grounds.show', $g),
            ];
        })->values()->all();

        return response()->json(['suggestions' => $suggestions]);
    }

    public function index(Request $request): View
    {
        $disciplineFilter = $this->normalizedDisciplineIds($request);

        $filtered = $this->filteredQuery($request, $disciplineFilter);

        $userLat = $request->input('user_lat');
        $userLng = $request->input('user_lng');
        $hasUserGeo = Geo::validUserCoords($userLat, $userLng);

        if ($request->filled('sort')) {
            $sort = (string) $request->input('sort');
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
            'q' => trim((string) $request->input('q', '')),
            'sort' => $sort,
            'facilities' => is_array($request->input('facility')) ? $request->input('facility') : [],
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
        ]);
    }

    public function show(ShootingGround $shooting_ground): View
    {
        $shooting_ground->load(['openingHours', 'disciplines', 'facilities']);

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
     * @param  list<int>  $disciplineIds
     */
    private function filteredQuery(Request $request, array $disciplineIds = []): Builder
    {
        $query = ShootingGround::query()->with(['disciplines', 'facilities']);

        $q = trim((string) $request->input('q', ''));
        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function ($sub) use ($like) {
                $sub->where('name', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('county', 'like', $like)
                    ->orWhere('postcode', 'like', $like);
            });
        }

        $facilities = $request->input('facility', []);
        if (! is_array($facilities)) {
            $facilities = [];
        }
        if (in_array('practice', $facilities, true)) {
            $query->where('has_practice', true);
        }
        if (in_array('lessons', $facilities, true)) {
            $query->where('has_lessons', true);
        }
        if (in_array('competitions', $facilities, true)) {
            $query->where('has_competitions', true);
        }

        if ($disciplineIds !== []) {
            $query->whereHas('disciplines', function ($q) use ($disciplineIds): void {
                $q->whereIn('disciplines.id', $disciplineIds);
            });
        }

        return $query;
    }
}
