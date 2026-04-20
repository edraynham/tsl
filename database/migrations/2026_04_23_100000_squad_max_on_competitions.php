<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('competitions', 'squad_max_participants')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->unsignedInteger('squad_max_participants')->nullable()->after('open_max_participants');
            });
        }

        if (Schema::hasColumn('competition_squads', 'max_participants')) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'mysql') {
                DB::statement('
                    UPDATE competitions c
                    INNER JOIN (
                        SELECT competition_id, MAX(max_participants) AS mx
                        FROM competition_squads
                        GROUP BY competition_id
                    ) s ON s.competition_id = c.id
                    SET c.squad_max_participants = s.mx
                    WHERE c.squad_max_participants IS NULL
                ');
            } else {
                $rows = DB::table('competition_squads')
                    ->select('competition_id')
                    ->selectRaw('MAX(max_participants) as mx')
                    ->groupBy('competition_id')
                    ->get();

                foreach ($rows as $row) {
                    DB::table('competitions')
                        ->where('id', $row->competition_id)
                        ->whereNull('squad_max_participants')
                        ->update(['squad_max_participants' => $row->mx]);
                }
            }

            Schema::table('competition_squads', function (Blueprint $table) {
                $table->dropColumn('max_participants');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('competition_squads', 'max_participants')) {
            Schema::table('competition_squads', function (Blueprint $table) {
                $table->unsignedInteger('max_participants')->default(1)->after('starts_at');
            });
        }

        $competitions = DB::table('competitions')
            ->whereNotNull('squad_max_participants')
            ->get(['id', 'squad_max_participants']);

        foreach ($competitions as $c) {
            DB::table('competition_squads')
                ->where('competition_id', $c->id)
                ->update(['max_participants' => $c->squad_max_participants]);
        }

        if (Schema::hasColumn('competitions', 'squad_max_participants')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->dropColumn('squad_max_participants');
            });
        }
    }
};
