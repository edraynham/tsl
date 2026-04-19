<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_shooting_ground', function (Blueprint $table) {
            $table->foreignId('discipline_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shooting_ground_id')->constrained()->cascadeOnDelete();

            $table->primary(['discipline_id', 'shooting_ground_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_shooting_ground');
    }
};
