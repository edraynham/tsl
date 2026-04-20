<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\OpeningHours;
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
        $shooting_ground->load('openingHours');

        $days = old('days');
        if ($days === null) {
            $row = $shooting_ground->openingHours;
            $days = [];
            foreach (ShootingGround::DAY_PREFIXES as $day) {
                $o = $row?->{$day.'_opens_at'};
                $c = $row?->{$day.'_closes_at'};
                $days[$day] = [
                    'opens_at' => $o ? $o->format('H:i') : '',
                    'closes_at' => $c ? $c->format('H:i') : '',
                ];
            }
        }

        return view('owner.grounds.opening-hours', [
            'ground' => $shooting_ground,
            'days' => $days,
        ]);
    }

    public function update(Request $request, ShootingGround $shooting_ground): RedirectResponse
    {
        $this->authorize('manage', $shooting_ground);

        $rules = [];
        foreach (ShootingGround::DAY_PREFIXES as $day) {
            $rules['days.'.$day.'.opens_at'] = ['nullable', 'date_format:H:i'];
            $rules['days.'.$day.'.closes_at'] = ['nullable', 'date_format:H:i'];
        }
        $rules['days'] = ['required', 'array'];

        $validated = $request->validate($rules);

        foreach (ShootingGround::DAY_PREFIXES as $day) {
            $open = $validated['days'][$day]['opens_at'] ?? null;
            $close = $validated['days'][$day]['closes_at'] ?? null;
            $open = ($open !== null && $open !== '') ? $open : null;
            $close = ($close !== null && $close !== '') ? $close : null;
            if (($open !== null) xor ($close !== null)) {
                throw ValidationException::withMessages([
                    "days.$day.opens_at" => [__('Enter both opening and closing times, or leave both blank for that day.')],
                ]);
            }
        }

        DB::transaction(function () use ($shooting_ground, $validated): void {
            $attrs = ['shooting_ground_id' => $shooting_ground->id];
            foreach (ShootingGround::DAY_PREFIXES as $day) {
                $open = $validated['days'][$day]['opens_at'] ?? null;
                $close = $validated['days'][$day]['closes_at'] ?? null;
                $open = ($open !== null && $open !== '') ? $open : null;
                $close = ($close !== null && $close !== '') ? $close : null;
                $attrs[$day.'_opens_at'] = $open;
                $attrs[$day.'_closes_at'] = $close;
            }

            $hasAnyOpenDay = false;
            foreach (ShootingGround::DAY_PREFIXES as $day) {
                if ($attrs[$day.'_opens_at'] !== null && $attrs[$day.'_closes_at'] !== null) {
                    $hasAnyOpenDay = true;
                    break;
                }
            }

            if ($hasAnyOpenDay) {
                OpeningHours::query()->updateOrCreate(
                    ['shooting_ground_id' => $shooting_ground->id],
                    $attrs
                );
            } else {
                $shooting_ground->openingHours()?->delete();
            }

        });

        return redirect()
            ->route('owner.grounds.opening-hours.edit', $shooting_ground)
            ->with('status', __('Opening hours saved.'));
    }
}
