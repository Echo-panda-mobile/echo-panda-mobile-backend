<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('genre')->comment('Genre name (Rock, Pop, Jazz, etc)');
            $table->integer('preference_score')->default(0)->comment('Score based on user actions');
            $table->timestamps();

            // One preference record per user-genre
            $table->unique(['user_id', 'genre']);
            $table->index('user_id');
            $table->index('preference_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
