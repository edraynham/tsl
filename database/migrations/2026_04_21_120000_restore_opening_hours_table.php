<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $dayPrefixes = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    public function up(): void
    {
        Schema::create('opening_hours', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shooting_ground_id')->constrained()->cascadeOnDelete();
            /** ISO 8601: 1 = Monday … 7 = Sunday */
            $table->unsignedTinyInteger('weekday');
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['shooting_ground_id', 'weekday']);
        });

        if (! Schema::hasColumn('shooting_grounds', 'monday_opens_at')) {
            return;
        }

        $grounds = DB::table('shooting_grounds')->get();

        foreach ($grounds as $g) {
            foreach (range(1, 7) as $iso) {
                $prefix = $this->prefixForIsoWeekday($iso);
                if ($prefix === null) {
                    continue;
                }
                $open = $g->{$prefix.'_opens_at'};
                $close = $g->{$prefix.'_closes_at'};
                if ($open !== null && $close !== null) {
                    DB::table('opening_hours')->insert([
                        'shooting_ground_id' => $g->id,
                        'weekday' => $iso,
                        'opens_at' => $open,
                        'closes_at' => $close,
                        'sort_order' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        Schema::table('shooting_grounds', function (Blueprint $table): void {
            foreach ($this->dayPrefixes as $day) {
                $table->dropColumn([$day.'_opens_at', $day.'_closes_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('shooting_grounds', function (Blueprint $table): void {
            foreach ($this->dayPrefixes as $day) {
                $table->time($day.'_opens_at')->nullable();
                $table->time($day.'_closes_at')->nullable();
            }
        });

        if (! Schema::hasTable('opening_hours')) {
            return;
        }

        $rows = DB::table('opening_hours')
            ->orderBy('shooting_ground_id')
            ->orderBy('weekday')
            ->orderBy('sort_order')
            ->get();

        $grouped = $rows->groupBy('shooting_ground_id');

        foreach ($grouped as $groundId => $slots) {
            $byWeekday = $slots->groupBy('weekday');
            $updates = [];
            foreach ($byWeekday as $weekday => $daySlots) {
                $first = $daySlots->first();
                $prefix = $this->prefixForIsoWeekday((int) $weekday);
                if ($prefix === null) {
                    continue;
                }
                $updates[$prefix.'_opens_at'] = $first->opens_at;
                $updates[$prefix.'_closes_at'] = $first->closes_at;
            }
            if ($updates !== []) {
                DB::table('shooting_grounds')->where('id', $groundId)->update($updates);
            }
        }

        Schema::dropIfExists('opening_hours');
    }

    private function prefixForIsoWeekday(int $weekday): ?string
    {
        return match ($weekday) {
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
            default => null,
        };
    }
};
