<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use Illuminate\View\View;

class GroundCompetitionRegistrationsController extends Controller
{
    public function index(Competition $competition): View
    {
        $this->authorize('update', $competition);
        $shooting_ground = $competition->shootingGround;

        $registrations = $competition->registrations()
            ->with('squad')
            ->orderByDesc('created_at')
            ->get();

        return view('owner.grounds.competitions.registrations', [
            'ground' => $shooting_ground,
            'competition' => $competition,
            'registrations' => $registrations,
        ]);
    }
}
