<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->string('sender_name', 120);
            $table->string('sender_email', 255);
            $table->string('sender_phone', 40)->nullable();
            $table->string('subject', 180)->nullable();
            $table->string('skill_level', 32)->nullable();
            $table->text('message');
            $table->timestamps();

            $table->index(['instructor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_messages');
    }
};
