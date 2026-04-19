<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shooting_grounds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('postcode', 32)->nullable();
            $table->text('full_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('website')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('has_practice')->default(false);
            $table->boolean('has_lessons')->default(false);
            $table->boolean('has_competitions')->default(false);
            $table->text('practice_notes')->nullable();
            $table->text('lesson_notes')->nullable();
            $table->text('competition_notes')->nullable();
            $table->json('events_urls')->nullable();
            $table->timestamps();

            $table->index('county');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shooting_grounds');
    }
};
