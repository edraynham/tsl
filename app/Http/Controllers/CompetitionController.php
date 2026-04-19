<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Discipline;
use App\Support\Geo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function index(Request $request): View
    {
        $near = trim((string) $request->input('near', ''));
        $cpsa = $request->input('cpsa');
        $cpsaFilter = in_array($cpsa, ['1', '0'], true) ? $cpsa : null;
        $disciplineFilter = $this->normalizedDisciplineIds($request);
        $userLat = $request->input('user_lat');
        $userLng = $request->input('user_lng');

        $refLat = null;
        $refLng = null;

        if (Geo::validUserCoords($userLat, $userLng)) {
            $refLat = (float) $userLat;
            $refLng = (float) $userLng;
        } elseif ($near !== '') {
            $coords = Geo::geocodeUkSearch($near);
            if ($coords !== null) {
                $refLat = $coords['lat'];
                $refLng = $coords['lng'];
            }
        }

        $query = Competition::query()->with(['shootingGround', 'canonicalDiscipline']);

        $textOnlyFilter = $near !== '' && $refLat === null;

        if ($textOnlyFilter) {
            $like = '%'.$near.'%';
            $query->whereHas('shootingGround', function ($q) use ($like): void {
                $q->where('postcode', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('county', 'like', $like)
                    ->orWhere('name', 'like', $like);
            });
        }

        if ($cpsaFilter === '1') {
            $query->where('cpsa_registered', true);
        } elseif ($cpsaFilter === '0') {
            $query->where('cpsa_registered', false);
        }

        if ($disciplineFilter !== []) {
            $names = Discipline::query()
                ->whereIn('id', $disciplineFilter)
                ->pluck('name');
            $query->where(function ($q) use ($disciplineFilter, $names): void {
                $q->whereIn('discipline_id', $disciplineFilter)
                    ->orWhereIn('discipline', $names);
            });
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, Competition> $competitions */
        $competitions = $query->orderBy('starts_at')->get();

        $sortByDistance = $refLat !== null && $refLng !== null;

        if ($sortByDistance) {
            $competitions = $competitions->map(function (Competition $c) use ($refLat, $refLng) {
                $g = $c->shootingGround;
                if ($g->latitude !== null && $g->longitude !== null) {
                    $c->setAttribute(
                        'distance_miles',
                        round(Geo::haversineMiles(
                            $refLat,
                            $refLng,
                            (float) $g->latitude,
                            (float) $g->longitude,
                        ), 1),
                    );
                } else {
                    $c->setAttribute('distance_miles', null);
                }

                return $c;
            })->sortBy(function (Competition $c) {
                $d = $c->getAttribute('distance_miles');

                return $d === null ? PHP_FLOAT_MAX : $d;
            })->values();
        }

        $featured = $competitions->first(function (Competition $c) {
            return $c->starts_at->gte(now()->startOfDay());
        }) ?? $competitions->first();

        $rest = $featured
            ? $competitions->reject(fn (Competition $c) => $c->is($featured))
            : $competitions;

        $grouped = $rest->groupBy(
            fn (Competition $c) => $c->starts_at->format('Y-m'),
        );

        return view('competitions.index', [
            'competitions' => $competitions,
            'featured' => $featured,
            'grouped' => $grouped,
            'near' => $near,
            'cpsaFilter' => $cpsaFilter,
            'disciplineFilter' => $disciplineFilter,
            'allDisciplines' => Discipline::query()->orderBy('name')->get(),
            'sortByDistance' => $sortByDistance,
            'textOnlyFilter' => $textOnlyFilter,
            'hasUserCoords' => Geo::validUserCoords($userLat, $userLng),
        ]);
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

    public function show(Competition $competition): View
    {
        $competition->load(['shootingGround', 'canonicalDiscipline']);

        return view('competitions.show', [
            'competition' => $competition,
            'ground' => $competition->shootingGround,
        ]);
    }
}
