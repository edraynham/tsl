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
        if (! Schema::hasTable('opening_hours')) {
            $this->createWideTable();

            return;
        }

        if (! Schema::hasColumn('opening_hours', 'weekday')) {
            return;
        }

        $rows = DB::table('opening_hours')
            ->orderBy('shooting_ground_id')
            ->orderBy('weekday')
            ->orderBy('sort_order')
            ->get();

        $wide = [];
        foreach ($rows->groupBy('shooting_ground_id') as $groundId => $slots) {
            $record = [
                'shooting_ground_id' => (int) $groundId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            foreach ($this->dayPrefixes as $day) {
                $record[$day.'_opens_at'] = null;
                $record[$day.'_closes_at'] = null;
            }
            foreach (range(1, 7) as $iso) {
                $prefix = $this->prefixForIsoWeekday($iso);
                if ($prefix === null) {
                    continue;
                }
                $first = $slots->filter(fn ($r) => (int) $r->weekday === $iso)
                    ->sortBy('sort_order')
                    ->first();
                $record[$prefix.'_opens_at'] = $first?->opens_at;
                $record[$prefix.'_closes_at'] = $first?->closes_at;
            }
            $wide[] = $record;
        }

        Schema::drop('opening_hours');
        $this->createWideTable();

        foreach ($wide as $record) {
            DB::table('opening_hours')->insert($record);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('opening_hours')) {
            return;
        }

        if (Schema::hasColumn('opening_hours', 'weekday')) {
            return;
        }

        $wideRows = DB::table('opening_hours')->get();

        Schema::drop('opening_hours');

        Schema::create('opening_hours', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shooting_ground_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['shooting_ground_id', 'weekday']);
        });

        foreach ($wideRows as $g) {
            foreach (range(1, 7) as $iso) {
                $prefix = $this->prefixForIsoWeekday($iso);
                if ($prefix === null) {
                    continue;
                }
                $open = $g->{$prefix.'_opens_at'};
                $close = $g->{$prefix.'_closes_at'};
                if ($open !== null && $close !== null) {
                    DB::table('opening_hours')->insert([
                        'shooting_ground_id' => $g->shooting_ground_id,
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
    }

    private function createWideTable(): void
    {
        Schema::create('opening_hours', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('shooting_ground_id')->constrained()->cascadeOnDelete()->unique();
            foreach ($this->dayPrefixes as $day) {
                $table->time($day.'_opens_at')->nullable();
                $table->time($day.'_closes_at')->nullable();
            }
            $table->timestamps();
        });
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
