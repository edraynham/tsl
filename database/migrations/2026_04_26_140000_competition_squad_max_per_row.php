<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('competition_squads', 'max_participants')) {
            Schema::table('competition_squads', function (Blueprint $table): void {
                $table->unsignedTinyInteger('max_participants')->default(6)->after('starts_at');
            });
        }

        if (Schema::hasColumn('competitions', 'squad_max_participants')) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql') {
                DB::statement('
                    UPDATE competition_squads cs
                    INNER JOIN competitions c ON c.id = cs.competition_id
                    SET cs.max_participants = LEAST(12, GREATEST(1, c.squad_max_participants))
                    WHERE c.squad_max_participants IS NOT NULL
                ');
            } else {
                $comps = DB::table('competitions')
                    ->whereNotNull('squad_max_participants')
                    ->get(['id', 'squad_max_participants']);

                foreach ($comps as $c) {
                    $v = max(1, min(12, (int) $c->squad_max_participants));
                    DB::table('competition_squads')
                        ->where('competition_id', $c->id)
                        ->update(['max_participants' => $v]);
                }
            }

            Schema::table('competitions', function (Blueprint $table): void {
                $table->dropColumn('squad_max_participants');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('competitions', 'squad_max_participants')) {
            Schema::table('competitions', function (Blueprint $table): void {
                $table->unsignedInteger('squad_max_participants')->nullable()->after('open_max_participants');
            });
        }

        $rows = DB::table('competition_squads')
            ->select('competition_id')
            ->selectRaw('MAX(max_participants) as mx')
            ->groupBy('competition_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('competitions')
                ->where('id', $row->competition_id)
                ->update(['squad_max_participants' => $row->mx]);
        }

        if (Schema::hasColumn('competition_squads', 'max_participants')) {
            Schema::table('competition_squads', function (Blueprint $table): void {
                $table->dropColumn('max_participants');
            });
        }
    }
};
