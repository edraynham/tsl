<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('shooting_ground_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shooting_ground_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shooting_ground_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shooting_ground_user');
        Schema::dropIfExists('login_tokens');
    }
};
