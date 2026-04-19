<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shooting_ground_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('discipline', 64)->nullable();
            $table->string('external_url')->nullable();
            $table->timestamps();

            $table->index(['starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
