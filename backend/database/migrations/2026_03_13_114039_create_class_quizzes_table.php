<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->unique(['class_id', 'quiz_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_quizzes');
    }
};