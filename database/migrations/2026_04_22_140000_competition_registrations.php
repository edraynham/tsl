<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('competitions', 'registration_format')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->string('registration_format', 16)->default('closed')->after('cpsa_registered');
                $table->unsignedInteger('open_max_participants')->nullable()->after('registration_format');
            });
        }

        if (! Schema::hasTable('competition_squads')) {
            Schema::create('competition_squads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->dateTime('starts_at');
                $table->unsignedInteger('max_participants');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['competition_id', 'sort_order'], 'comp_squads_comp_sort_idx');
            });
        }

        if (! Schema::hasTable('competition_registrations')) {
            Schema::create('competition_registrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
                $table->foreignId('competition_squad_id')->nullable()->constrained()->restrictOnDelete();
                $table->string('cpsa_number', 64);
                $table->string('entrant_name');
                $table->string('email');
                $table->string('telephone', 64);
                $table->timestamps();

                $table->unique(['competition_id', 'cpsa_number'], 'comp_reg_comp_cpsa_unique');
                $table->index(['competition_id', 'competition_squad_id'], 'comp_reg_comp_squad_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_registrations');
        Schema::dropIfExists('competition_squads');

        if (Schema::hasColumn('competitions', 'registration_format')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->dropColumn(['registration_format', 'open_max_participants']);
            });
        }
    }
};
