<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\CompetitionSquad;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class GroundCompetitionRegistrationsController extends Controller
{
    public function index(Competition $competition): View
    {
        $this->authorize('update', $competition);
        $shooting_ground = $competition->shootingGround;

        $all = $competition->registrations()->with('squad')->get();

        $registrationGroups = $this->registrationGroupsForView($competition, $all);

        return view('owner.grounds.competitions.registrations', [
            'ground' => $shooting_ground,
            'competition' => $competition,
            'registrationGroups' => $registrationGroups,
        ]);
    }

    /**
     * @param  Collection<int, CompetitionRegistration>  $all
     * @return Collection<int, array{squad: ?CompetitionSquad, registrations: Collection<int, CompetitionRegistration>}>
     */
    private function registrationGroupsForView(Competition $competition, Collection $all): Collection
    {
        if ($all->isEmpty()) {
            return collect();
        }

        if ($competition->registration_format !== Competition::REGISTRATION_SQUADDED) {
            return collect([[
                'squad' => null,
                'registrations' => $all->sortByDesc('created_at')->values(),
            ]]);
        }

        $bySquadId = $all->groupBy('competition_squad_id');

        $groups = $competition->squads()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function ($squad) use ($bySquadId) {
                $registrations = $bySquadId->get($squad->id, collect())->sortByDesc('created_at')->values();

                return [
                    'squad' => $squad,
                    'registrations' => $registrations,
                ];
            })
            ->filter(fn (array $group) => $group['registrations']->isNotEmpty())
            ->values();

        $withoutSquad = $bySquadId->get(null, collect())->sortByDesc('created_at')->values();
        if ($withoutSquad->isNotEmpty()) {
            $groups->push([
                'squad' => null,
                'registrations' => $withoutSquad,
            ]);
        }

        return $groups;
    }
}
