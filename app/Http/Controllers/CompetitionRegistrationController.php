<?php

namespace App\Http\Controllers;

use App\Mail\CompetitionNewRegistrationMail;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\CompetitionSquad;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CompetitionRegistrationController extends Controller
{
    public function create(Competition $competition): View|RedirectResponse
    {
        if (! $competition->bookingPageAvailable()) {
            return redirect()
                ->route('competitions.show', $competition)
                ->with('status', __('Online registration is not available for this event.'));
        }

        $competition->load(['shootingGround', 'squads' => fn ($q) => $q->withCount('registrations')]);

        $squads = $competition->registration_format === Competition::REGISTRATION_SQUADDED
            ? $competition->squads
            : collect();

        $openTaken = $competition->registration_format === Competition::REGISTRATION_OPEN
            ? $competition->openRegistrationsCount()
            : 0;

        return view('competitions.book', [
            'competition' => $competition,
            'ground' => $competition->shootingGround,
            'squads' => $squads,
            'openTaken' => $openTaken,
        ]);
    }

    public function store(Request $request, Competition $competition): RedirectResponse
    {
        if (! $competition->bookingPageAvailable()) {
            return redirect()
                ->route('competitions.show', $competition)
                ->with('status', __('Online registration is not available for this event.'));
        }

        $rules = [
            'cpsa_number' => ['required', 'string', 'max:64'],
            'entrant_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'telephone' => ['required', 'string', 'max:64'],
        ];

        if ($competition->registration_format === Competition::REGISTRATION_SQUADDED) {
            $rules['competition_squad_id'] = ['required', 'integer', 'exists:competition_squads,id'];
        }

        $validated = $request->validate($rules);

        $cpsa = trim($validated['cpsa_number']);

        try {
            $registration = DB::transaction(function () use ($competition, $validated, $cpsa) {
                Competition::query()->whereKey($competition->id)->lockForUpdate()->firstOrFail();

                if ($competition->registration_format === Competition::REGISTRATION_OPEN) {
                    if ($competition->open_max_participants !== null) {
                        $openCount = $competition->registrations()
                            ->whereNull('competition_squad_id')
                            ->lockForUpdate()
                            ->count();
                        if ($openCount >= $competition->open_max_participants) {
                            throw ValidationException::withMessages([
                                'email' => __('This event is fully booked.'),
                            ]);
                        }
                    }

                    if (CompetitionRegistration::query()
                        ->where('competition_id', $competition->id)
                        ->where('cpsa_number', $cpsa)
                        ->lockForUpdate()
                        ->exists()) {
                        throw ValidationException::withMessages([
                            'cpsa_number' => __('This CPSA number is already registered for this event.'),
                        ]);
                    }

                    return CompetitionRegistration::query()->create([
                        'competition_id' => $competition->id,
                        'competition_squad_id' => null,
                        'cpsa_number' => $cpsa,
                        'entrant_name' => $validated['entrant_name'],
                        'email' => $validated['email'],
                        'telephone' => $validated['telephone'],
                    ]);
                }

                /** @var int $squadId */
                $squadId = (int) $validated['competition_squad_id'];

                $squad = CompetitionSquad::query()
                    ->where('competition_id', $competition->id)
                    ->whereKey($squadId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $competition->refresh();
                $squad->refresh();
                $squadCap = $squad->capacity();

                $used = $squad->registrations()->lockForUpdate()->count();
                if ($used >= $squadCap) {
                    throw ValidationException::withMessages([
                        'competition_squad_id' => __('That squad is full. Please choose another.'),
                    ]);
                }

                if (CompetitionRegistration::query()
                    ->where('competition_id', $competition->id)
                    ->where('cpsa_number', $cpsa)
                    ->lockForUpdate()
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'cpsa_number' => __('This CPSA number is already registered for this event.'),
                    ]);
                }

                return CompetitionRegistration::query()->create([
                    'competition_id' => $competition->id,
                    'competition_squad_id' => $squad->id,
                    'cpsa_number' => $cpsa,
                    'entrant_name' => $validated['entrant_name'],
                    'email' => $validated['email'],
                    'telephone' => $validated['telephone'],
                ]);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'UNIQUE constraint failed') || str_contains($e->getMessage(), 'Duplicate entry')) {
                return back()
                    ->withInput()
                    ->withErrors(['cpsa_number' => __('This CPSA number is already registered for this event.')]);
            }
            throw $e;
        }

        $competition->load(['shootingGround.owners']);
        $owners = $competition->shootingGround->owners->filter(fn ($u) => $u->email_verified_at !== null);
        foreach ($owners as $owner) {
            Mail::to($owner->email)->send(new CompetitionNewRegistrationMail(
                $competition->fresh(['shootingGround']),
                $registration->load('squad'),
            ));
        }

        return redirect()
            ->route('competitions.show', $competition)
            ->with('status', __('You are registered. The ground has been notified by email.'));
    }
}
