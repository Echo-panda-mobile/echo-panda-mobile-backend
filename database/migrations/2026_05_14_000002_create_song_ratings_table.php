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
        Schema::create('song_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('song_id')->constrained('songs')->onDelete('cascade');
            $table->integer('rating')->comment('Rating 1-5 stars');
            $table->text('review')->nullable()->comment('Optional user review/comment');
            $table->timestamps();

            // One rating per user per song
            $table->unique(['user_id', 'song_id']);
            $table->index('user_id');
            $table->index('song_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('song_ratings');
    }
};
