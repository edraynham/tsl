<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('competitions', 'ends_at')) {
            Schema::table('competitions', function (Blueprint $table): void {
                $table->dropColumn('ends_at');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('competitions', 'ends_at')) {
            Schema::table('competitions', function (Blueprint $table): void {
                $table->dateTime('ends_at')->nullable()->after('starts_at');
            });
        }
    }
};
