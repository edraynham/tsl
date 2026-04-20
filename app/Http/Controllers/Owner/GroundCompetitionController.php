<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Discipline;
use App\Models\ShootingGround;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GroundCompetitionController extends Controller
{
    public function create(Request $request): View
    {
        $slug = $request->query('ground');
        abort_unless(is_string($slug) && $slug !== '', 404);
        $shooting_ground = ShootingGround::query()->where('slug', $slug)->firstOrFail();

        $this->authorize('create', [Competition::class, $shooting_ground]);

        return view('owner.grounds.competitions.form', [
            'ground' => $shooting_ground,
            'competition' => new Competition(['shooting_ground_id' => $shooting_ground->id]),
            'disciplines' => Discipline::query()->orderBy('code')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $shooting_ground_id = (int) $request->validate([
            'shooting_ground_id' => ['required', 'integer', 'exists:shooting_grounds,id'],
        ])['shooting_ground_id'];

        $shooting_ground = ShootingGround::query()->findOrFail($shooting_ground_id);
        $this->authorize('create', [Competition::class, $shooting_ground]);

        $validated = $this->validatedCompetition($request, null, $shooting_ground);
        $this->assertRegistrationSettings(null, $validated);

        Competition::query()->create($validated);

        return redirect()
            ->route('account.competitions.index', ['ground' => $shooting_ground->slug])
            ->with('status', __('Competition created.'));
    }

    public function edit(Competition $competition): View
    {
        $this->authorize('update', $competition);
        $shooting_ground = $competition->shootingGround;

        return view('owner.grounds.competitions.form', [
            'ground' => $shooting_ground,
            'competition' => $competition,
            'disciplines' => Discipline::query()->orderBy('code')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Competition $competition): RedirectResponse
    {
        $this->authorize('update', $competition);
        $shooting_ground = $competition->shootingGround;

        $validated = $this->validatedCompetition($request, $competition, $shooting_ground);
        $this->assertRegistrationSettings($competition, $validated);

        $competition->update($validated);

        return redirect()
            ->route('account.competitions.index', ['ground' => $shooting_ground->slug])
            ->with('status', __('Competition updated.'));
    }

    public function destroy(Competition $competition): RedirectResponse
    {
        $this->authorize('delete', $competition);
        $shooting_ground = $competition->shootingGround;

        $competition->delete();

        return redirect()
            ->route('account.competitions.index', ['ground' => $shooting_ground->slug])
            ->with('status', __('Competition removed.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedCompetition(Request $request, ?Competition $existing, ShootingGround $shooting_ground): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('competitions', 'slug')->ignore($existing?->id),
            ],
            'summary' => ['nullable', 'string', 'max:10000'],
            'starts_at' => ['required', 'date'],
            'discipline_id' => ['nullable', 'integer', 'exists:disciplines,id'],
            'external_url' => ['nullable', 'string', 'max:2048'],
            'registration_format' => ['required', 'string', Rule::in([
                Competition::REGISTRATION_CLOSED,
                Competition::REGISTRATION_OPEN,
                Competition::REGISTRATION_SQUADDED,
            ])],
        ]);

        $validated['cpsa_registered'] = $request->boolean('cpsa_registered');
        $validated['open_max_participants'] = null;

        $slug = $validated['slug'] ?? null;
        if ($slug === null || $slug === '') {
            $base = Str::slug($validated['title']);
            $slug = $base.'-'.$shooting_ground->id;
            $n = 0;
            while (Competition::query()->where('slug', $slug)->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))->exists()) {
                $slug = $base.'-'.$shooting_ground->id.'-'.(++$n);
            }
        }

        $validated['slug'] = $slug;
        $validated['shooting_ground_id'] = $shooting_ground->id;

        $disciplineId = $validated['discipline_id'] ?? null;
        if ($disciplineId) {
            $validated['discipline_id'] = (int) $disciplineId;
            $validated['discipline'] = Discipline::query()->whereKey($disciplineId)->value('name');
        } else {
            $validated['discipline_id'] = null;
            $validated['discipline'] = null;
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function assertRegistrationSettings(?Competition $existing, array $validated): void
    {
        if ($existing && $existing->registrations()->exists()) {
            if ($validated['registration_format'] !== $existing->registration_format) {
                throw ValidationException::withMessages([
                    'registration_format' => __('Registration format cannot be changed after entries exist.'),
                ]);
            }
        }
    }
}
