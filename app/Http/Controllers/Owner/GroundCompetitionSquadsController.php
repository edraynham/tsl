<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionSquad;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class GroundCompetitionSquadsController extends Controller
{
    private const SQUAD_SCHEDULE_TZ = 'Europe/London';

    public function edit(Competition $competition): View|RedirectResponse
    {
        $this->authorize('update', $competition);
        $shooting_ground = $competition->shootingGround;

        if ($competition->registration_format !== Competition::REGISTRATION_SQUADDED) {
            return redirect()
                ->route('account.competitions.edit', $competition)
                ->with('status', __('Squads only apply to squadded registration. Change the registration format first.'));
        }

        $squads = $competition->squads()->orderBy('sort_order')->orderBy('id')->get();

        $tz = self::SQUAD_SCHEDULE_TZ;
        $compStart = $competition->starts_at->copy()->timezone($tz);

        $defaultRows = $squads->map(function (CompetitionSquad $s) use ($tz) {
            $dt = $s->starts_at?->timezone($tz);

            return [
                'id' => $s->id,
                'start_date' => $dt?->format('Y-m-d'),
                'start_time' => $dt?->format('G:i'),
                'max_participants' => $s->max_participants ?? 6,
            ];
        })->values()->all();

        if ($defaultRows === []) {
            $defaultRows = [[
                'id' => '',
                'start_date' => $compStart->format('Y-m-d'),
                'start_time' => $compStart->format('G:i'),
                'max_participants' => 6,
            ]];
        } elseif ($this->squadRowDateTimeBlank($defaultRows[0])) {
            $defaultRows[0]['start_date'] = $compStart->format('Y-m-d');
            $defaultRows[0]['start_time'] = $compStart->format('G:i');
        }

        $squadRows = old('squads', $defaultRows);
        if (isset($squadRows[0]) && is_array($squadRows[0]) && $this->squadRowDateTimeBlank($squadRows[0])) {
            $squadRows[0]['start_date'] = $compStart->format('Y-m-d');
            $squadRows[0]['start_time'] = $compStart->format('G:i');
        }

        return view('owner.grounds.competitions.squads', [
            'ground' => $shooting_ground,
            'competition' => $competition,
            'squadRows' => $squadRows,
            'nextSquadIndex' => count($squadRows),
            'squadSizeOptions' => range(1, 12),
            'squadScheduleTz' => $tz,
        ]);
    }

    public function update(Request $request, Competition $competition): RedirectResponse
    {
        $this->authorize('update', $competition);
        $shooting_ground = $competition->shootingGround;

        if ($competition->registration_format !== Competition::REGISTRATION_SQUADDED) {
            return redirect()
                ->route('account.competitions.edit', $competition)
                ->with('status', __('Squads only apply to squadded registration. Change the registration format first.'));
        }

        $validated = $request->validate([
            'squads' => ['present', 'array'],
            'squads.*.id' => ['nullable', 'integer'],
            'squads.*.start_date' => ['nullable', 'date'],
            'squads.*.start_time' => ['nullable', 'string', 'max:32'],
            'squads.*.max_participants' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $squadsInput = $validated['squads'];
        ksort($squadsInput, SORT_NUMERIC);

        $rows = collect($squadsInput)
            ->filter(fn (array $row): bool => filled($row['id'] ?? null) || filled($row['start_date'] ?? null) || filled($row['start_time'] ?? null))
            ->values()
            ->all();

        foreach ($rows as $i => $row) {
            if (empty($row['start_date']) || trim((string) ($row['start_time'] ?? '')) === '') {
                throw ValidationException::withMessages([
                    'squads.'.$i.'.start_time' => __('Each squad needs a date and a start time.'),
                ]);
            }
        }

        $rowsWithStarts = [];
        foreach ($rows as $i => $row) {
            try {
                $startsAt = $this->parseSquadStartsAt((string) $row['start_date'], (string) $row['start_time']);
            } catch (Throwable) {
                throw ValidationException::withMessages([
                    'squads.'.$i.'.start_time' => __('Use a time like 9:00, 09:30, 14:00, or 2:30pm.'),
                ]);
            }
            $rowsWithStarts[] = array_merge($row, ['_starts_at' => $startsAt]);
        }

        foreach ($rowsWithStarts as $i => $row) {
            if (! empty($row['id'])) {
                $exists = CompetitionSquad::query()
                    ->where('competition_id', $competition->id)
                    ->whereKey($row['id'])
                    ->exists();
                if (! $exists) {
                    return back()
                        ->withInput()
                        ->withErrors(['squads.'.$i.'.id' => __('Invalid squad reference.')]);
                }
            }
        }

        $existing = $competition->squads()
            ->withCount('registrations')
            ->get()
            ->keyBy('id');

        foreach ($rowsWithStarts as $i => $row) {
            $max = max(1, min(12, (int) ($row['max_participants'] ?? 6)));
            if (! empty($row['id'])) {
                $squad = $existing->get((int) $row['id']);
                $placesTaken = (int) ($squad?->registrations_count ?? 0);
                if ($squad !== null && $max < $placesTaken) {
                    throw ValidationException::withMessages([
                        'squads.'.$i.'.max_participants' => __('Cannot be less than the number of people already booked in this squad (:n).', ['n' => $placesTaken]),
                    ]);
                }
            }
        }

        $incomingIds = collect($rowsWithStarts)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

        DB::transaction(function () use ($competition, $rowsWithStarts, $incomingIds, $existing): void {
            foreach ($existing as $id => $squad) {
                if (in_array($id, $incomingIds, true)) {
                    continue;
                }
                if ($squad->registrations_count > 0) {
                    throw ValidationException::withMessages([
                        'squads' => __('You cannot remove :squad — it already has registrations.', ['squad' => $squad->label()]),
                    ]);
                }
                $squad->delete();
            }

            foreach ($rowsWithStarts as $index => $row) {
                $max = max(1, min(12, (int) ($row['max_participants'] ?? 6)));
                $startsAt = $row['_starts_at'];
                if (! empty($row['id'])) {
                    CompetitionSquad::query()
                        ->where('competition_id', $competition->id)
                        ->whereKey($row['id'])
                        ->lockForUpdate()
                        ->firstOrFail()
                        ->update([
                            'starts_at' => $startsAt,
                            'max_participants' => $max,
                            'sort_order' => $index,
                        ]);
                } else {
                    CompetitionSquad::query()->create([
                        'competition_id' => $competition->id,
                        'starts_at' => $startsAt,
                        'max_participants' => $max,
                        'sort_order' => $index,
                    ]);
                }
            }
        });

        return redirect()
            ->route('account.competitions.squads.edit', $competition)
            ->with('status', __('Squads saved.'));
    }

    /**
     * Interpret date + free-typed time in UK (London), then store using the app timezone.
     *
     * @throws Throwable
     */
    /**
     * @param  array<string, mixed>  $row
     */
    private function squadRowDateTimeBlank(array $row): bool
    {
        return trim((string) ($row['start_date'] ?? '')) === ''
            || trim((string) ($row['start_time'] ?? '')) === '';
    }

    private function parseSquadStartsAt(string $date, string $time): Carbon
    {
        $date = trim($date);
        $time = trim($time);
        $combined = $date.' '.$time;

        $parsed = Carbon::parse($combined, self::SQUAD_SCHEDULE_TZ);

        return $parsed->timezone(config('app.timezone'));
    }
}
