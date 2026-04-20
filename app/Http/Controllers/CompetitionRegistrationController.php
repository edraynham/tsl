<?php

namespace App\Http\Controllers;

use App\Mail\CompetitionNewRegistrationMail;
use App\Models\Competition;
use App\Models\CompetitionRegistration;
use App\Models\CompetitionSquad;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        $competition->load(['shootingGround', 'squads' => fn ($q) => $q->withSum('registrations', 'party_size')]);

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

        $rules = $competition->registration_format === Competition::REGISTRATION_OPEN
            ? [
                'cpsa_number' => ['required', 'string', 'max:64'],
                'entrant_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'telephone' => ['required', 'string', 'max:64'],
            ]
            : [
                'competition_squad_id' => ['required', 'integer', 'exists:competition_squads,id'],
                'party_size' => ['required', 'integer', 'min:1', 'max:12'],
                'entrants' => ['required', 'array', 'min:1', 'max:12'],
                'entrants.*.cpsa_number' => ['required', 'string', 'max:64'],
                'entrants.*.entrant_name' => ['required', 'string', 'max:255'],
                'entrants.0.email' => ['required', 'email', 'max:255'],
                'entrants.0.telephone' => ['required', 'string', 'max:64'],
            ];

        $validated = $request->validate($rules);

        if ($competition->registration_format === Competition::REGISTRATION_SQUADDED) {
            $partySize = (int) $validated['party_size'];
            if (count($validated['entrants']) !== $partySize) {
                throw ValidationException::withMessages([
                    'party_size' => __('Add one entry for each person you selected.'),
                ]);
            }
        }

        $cpsa = $competition->registration_format === Competition::REGISTRATION_OPEN
            ? trim($validated['cpsa_number'])
            : null;

        try {
            /** @var Collection<int, CompetitionRegistration> $registrations */
            $registrations = DB::transaction(function () use ($competition, $validated, $cpsa) {
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

                    $row = CompetitionRegistration::query()->create([
                        'competition_id' => $competition->id,
                        'competition_squad_id' => null,
                        'cpsa_number' => $cpsa,
                        'entrant_name' => $validated['entrant_name'],
                        'email' => $validated['email'],
                        'telephone' => $validated['telephone'],
                        'party_size' => 1,
                    ]);

                    return collect([$row]);
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

                $used = (int) $squad->registrations()->lockForUpdate()->sum('party_size');
                $partySize = (int) $validated['party_size'];
                if ($partySize < 1 || $partySize > $squadCap) {
                    throw ValidationException::withMessages([
                        'party_size' => __('Choose between 1 and :max people for this squad.', ['max' => $squadCap]),
                    ]);
                }
                if ($used + $partySize > $squadCap) {
                    throw ValidationException::withMessages([
                        'party_size' => __('Only :free place(s) left in that squad.', ['free' => max(0, $squadCap - $used)]),
                    ]);
                }

                $entrants = $validated['entrants'];
                $cpsaNumbers = [];
                foreach ($entrants as $i => $entrant) {
                    $cpsaNumbers[$i] = trim($entrant['cpsa_number']);
                }
                if (count($cpsaNumbers) !== count(array_unique($cpsaNumbers))) {
                    throw ValidationException::withMessages([
                        'entrants' => __('Each person must have a different CPSA number.'),
                    ]);
                }

                $existing = CompetitionRegistration::query()
                    ->where('competition_id', $competition->id)
                    ->whereIn('cpsa_number', $cpsaNumbers)
                    ->lockForUpdate()
                    ->pluck('cpsa_number')
                    ->all();
                if ($existing !== []) {
                    throw ValidationException::withMessages([
                        'entrants' => __('One or more CPSA numbers are already registered for this event: :list.', [
                            'list' => implode(', ', $existing),
                        ]),
                    ]);
                }

                $email = $entrants[0]['email'];
                $telephone = $entrants[0]['telephone'];
                $created = collect();

                foreach ($entrants as $entrant) {
                    $created->push(CompetitionRegistration::query()->create([
                        'competition_id' => $competition->id,
                        'competition_squad_id' => $squad->id,
                        'cpsa_number' => trim($entrant['cpsa_number']),
                        'entrant_name' => $entrant['entrant_name'],
                        'email' => $email,
                        'telephone' => $telephone,
                        'party_size' => 1,
                    ]));
                }

                return $created;
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'UNIQUE constraint failed') || str_contains($e->getMessage(), 'Duplicate entry')) {
                $duplicateKey = $competition->registration_format === Competition::REGISTRATION_SQUADDED
                    ? 'entrants'
                    : 'cpsa_number';

                return back()
                    ->withInput()
                    ->withErrors([
                        $duplicateKey => __('This CPSA number is already registered for this event.'),
                    ]);
            }
            throw $e;
        }

        $registrations->each->load('squad');

        $competition->load(['shootingGround.owners']);
        $owners = $competition->shootingGround->owners->filter(fn ($u) => $u->email_verified_at !== null);
        foreach ($owners as $owner) {
            Mail::to($owner->email)->send(new CompetitionNewRegistrationMail(
                $competition->fresh(['shootingGround']),
                $registrations,
            ));
        }

        return redirect()
            ->route('competitions.show', $competition)
            ->with('status', __('You are registered. The ground has been notified by email.'));
    }
}
