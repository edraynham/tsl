<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->foreignId('discipline_id')->nullable()->after('ends_at')->constrained()->nullOnDelete();
        });

        $this->backfillDisciplineIds();
    }

    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discipline_id');
        });
    }

    private function backfillDisciplineIds(): void
    {
        $rows = DB::table('competitions')
            ->whereNotNull('discipline')
            ->where('discipline', '!=', '')
            ->select('id', 'discipline')
            ->get();

        foreach ($rows as $row) {
            $name = trim((string) $row->discipline);
            if ($name === '') {
                continue;
            }

            $id = DB::table('disciplines')
                ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])
                ->value('id');

            if ($id !== null) {
                DB::table('competitions')->where('id', $row->id)->update(['discipline_id' => $id]);
            }
        }
    }
};
