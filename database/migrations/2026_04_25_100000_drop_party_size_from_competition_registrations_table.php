<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('competition_registrations', 'party_size')) {
            Schema::table('competition_registrations', function (Blueprint $table) {
                $table->dropColumn('party_size');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('competition_registrations', 'party_size')) {
            Schema::table('competition_registrations', function (Blueprint $table) {
                $table->unsignedSmallInteger('party_size')->default(1)->after('telephone');
            });
        }
    }
};
