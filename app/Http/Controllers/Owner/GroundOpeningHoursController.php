<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\OpeningHour;
use App\Models\ShootingGround;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class GroundOpeningHoursController extends Controller
{
    public function edit(ShootingGround $shooting_ground): View
    {
        $this->authorize('manage', $shooting_ground);
        $shooting_ground->load(['openingHours' => fn ($q) => $q->orderBy('weekday')->orderBy('sort_order')]);

        $slots = old('slots');
        if ($slots === null) {
            if ($shooting_ground->openingHours->isNotEmpty()) {
                $slots = $shooting_ground->openingHours->map(fn ($h) => [
                    'weekday' => $h->weekday,
                    'opens_at' => $h->opens_at ? $h->opens_at->format('H:i') : '',
                    'closes_at' => $h->closes_at ? $h->closes_at->format('H:i') : '',
                    'sort_order' => $h->sort_order,
                ])->values()->all();
            } else {
                $slots = collect(range(1, 7))->map(fn ($d) => [
                    'weekday' => $d,
                    'opens_at' => '09:00',
                    'closes_at' => '17:00',
                    'sort_order' => 0,
                ])->all();
            }
        }

        return view('owner.grounds.opening-hours', [
            'ground' => $shooting_ground,
            'slots' => $slots,
        ]);
    }

    public function update(Request $request, ShootingGround $shooting_ground): RedirectResponse
    {
        $this->authorize('manage', $shooting_ground);

        $validated = $request->validate([
            'slots' => ['present', 'array'],
            'slots.*.weekday' => ['required', 'integer', 'min:1', 'max:7'],
            'slots.*.opens_at' => ['nullable', 'date_format:H:i'],
            'slots.*.closes_at' => ['nullable', 'date_format:H:i'],
            'slots.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:10'],
        ]);

        foreach ($validated['slots'] as $i => $slot) {
            $open = $slot['opens_at'] ?? null;
            $close = $slot['closes_at'] ?? null;
            if (($open !== null && $open !== '') xor ($close !== null && $close !== '')) {
                throw ValidationException::withMessages([
                    "slots.$i.opens_at" => [__('Enter both opening and closing times, or leave both blank for that row.')],
                ]);
            }
        }

        DB::transaction(function () use ($shooting_ground, $validated): void {
            $shooting_ground->openingHours()->delete();

            foreach ($validated['slots'] as $i => $slot) {
                $open = $slot['opens_at'] ?? null;
                $close = $slot['closes_at'] ?? null;
                if (($open === null || $open === '') && ($close === null || $close === '')) {
                    continue;
                }

                OpeningHour::query()->create([
                    'shooting_ground_id' => $shooting_ground->id,
                    'weekday' => (int) $slot['weekday'],
                    'opens_at' => $open,
                    'closes_at' => $close,
                    'sort_order' => $i,
                ]);
            }
        });

        $shooting_ground->update(['opening_hours' => null]);

        return redirect()
            ->route('owner.grounds.opening-hours.edit', $shooting_ground)
            ->with('status', __('Opening hours saved.'));
    }
}
