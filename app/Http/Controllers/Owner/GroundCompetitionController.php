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
use Illuminate\View\View;

class GroundCompetitionController extends Controller
{
    public function index(ShootingGround $shooting_ground): View
    {
        $this->authorize('manage', $shooting_ground);
        $competitions = $shooting_ground->competitions()->orderBy('starts_at')->get();

        return view('owner.grounds.competitions.index', [
            'ground' => $shooting_ground,
            'competitions' => $competitions,
        ]);
    }

    public function create(ShootingGround $shooting_ground): View
    {
        $this->authorize('create', [Competition::class, $shooting_ground]);

        return view('owner.grounds.competitions.form', [
            'ground' => $shooting_ground,
            'competition' => new Competition(['shooting_ground_id' => $shooting_ground->id]),
            'disciplines' => Discipline::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, ShootingGround $shooting_ground): RedirectResponse
    {
        $this->authorize('create', [Competition::class, $shooting_ground]);

        $validated = $this->validatedCompetition($request, null, $shooting_ground);

        Competition::query()->create($validated);

        return redirect()
            ->route('owner.grounds.competitions.index', $shooting_ground)
            ->with('status', __('Competition created.'));
    }

    public function edit(ShootingGround $shooting_ground, Competition $competition): View
    {
        $this->authorize('update', $competition);
        abort_unless($competition->shooting_ground_id === $shooting_ground->id, 404);

        return view('owner.grounds.competitions.form', [
            'ground' => $shooting_ground,
            'competition' => $competition,
            'disciplines' => Discipline::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ShootingGround $shooting_ground, Competition $competition): RedirectResponse
    {
        $this->authorize('update', $competition);
        abort_unless($competition->shooting_ground_id === $shooting_ground->id, 404);

        $validated = $this->validatedCompetition($request, $competition, $shooting_ground);

        $competition->update($validated);

        return redirect()
            ->route('owner.grounds.competitions.index', $shooting_ground)
            ->with('status', __('Competition updated.'));
    }

    public function destroy(ShootingGround $shooting_ground, Competition $competition): RedirectResponse
    {
        $this->authorize('delete', $competition);
        abort_unless($competition->shooting_ground_id === $shooting_ground->id, 404);

        $competition->delete();

        return redirect()
            ->route('owner.grounds.competitions.index', $shooting_ground)
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
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'discipline_id' => ['nullable', 'integer', 'exists:disciplines,id'],
            'external_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $validated['cpsa_registered'] = $request->boolean('cpsa_registered');

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

        if (empty($validated['ends_at'])) {
            $validated['ends_at'] = null;
        }

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
}
