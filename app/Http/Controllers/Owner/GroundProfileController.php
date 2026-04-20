<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Discipline;
use App\Models\Facility;
use App\Models\ShootingGround;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroundProfileController extends Controller
{
    public function edit(ShootingGround $shooting_ground): View
    {
        $this->authorize('manage', $shooting_ground);
        $shooting_ground->load(['openingHours', 'disciplines', 'facilities']);

        return view('owner.grounds.edit', [
            'ground' => $shooting_ground,
            'disciplines' => Discipline::query()->orderBy('name')->get(),
            'facilities' => Facility::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ShootingGround $shooting_ground): RedirectResponse
    {
        $this->authorize('manage', $shooting_ground);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('shooting_grounds', 'slug')->ignore($shooting_ground->id)],
            'photo_url' => ['nullable', 'string', 'max:2048'],
            'city' => ['nullable', 'string', 'max:255'],
            'county' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:32'],
            'full_address' => ['nullable', 'string', 'max:2000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'website' => ['nullable', 'string', 'max:2048'],
            'facebook_url' => ['nullable', 'string', 'max:2048'],
            'instagram_url' => ['nullable', 'string', 'max:2048'],
            'description' => ['nullable', 'string', 'max:50000'],
            'has_practice' => ['boolean'],
            'has_lessons' => ['boolean'],
            'has_competitions' => ['boolean'],
            'practice_notes' => ['nullable', 'string', 'max:10000'],
            'lesson_notes' => ['nullable', 'string', 'max:10000'],
            'competition_notes' => ['nullable', 'string', 'max:10000'],
            'discipline_ids' => ['nullable', 'array'],
            'discipline_ids.*' => ['integer', 'exists:disciplines,id'],
            'facility_ids' => ['nullable', 'array'],
            'facility_ids.*' => ['integer', 'exists:facilities,id'],
        ]);

        $disciplineIds = array_values(array_unique(array_map('intval', $validated['discipline_ids'] ?? [])));
        $facilityIds = array_values(array_unique(array_map('intval', $validated['facility_ids'] ?? [])));

        unset($validated['discipline_ids'], $validated['facility_ids']);

        $validated['has_practice'] = $request->boolean('has_practice');
        $validated['has_lessons'] = $request->boolean('has_lessons');
        $validated['has_competitions'] = $request->boolean('has_competitions');

        DB::transaction(function () use ($shooting_ground, $validated, $disciplineIds, $facilityIds): void {
            $shooting_ground->update($validated);
            $shooting_ground->disciplines()->sync($disciplineIds);
            $shooting_ground->facilities()->sync($facilityIds);
        });

        return redirect()
            ->route('owner.grounds.edit', $shooting_ground)
            ->with('status', __('Profile saved.'));
    }
}
