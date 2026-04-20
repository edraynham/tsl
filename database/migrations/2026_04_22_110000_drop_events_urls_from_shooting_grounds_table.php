<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shooting_grounds', function (Blueprint $table): void {
            if (Schema::hasColumn('shooting_grounds', 'events_urls')) {
                $table->dropColumn('events_urls');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shooting_grounds', function (Blueprint $table): void {
            if (! Schema::hasColumn('shooting_grounds', 'events_urls')) {
                $table->json('events_urls')->nullable();
            }
        });
    }
};
