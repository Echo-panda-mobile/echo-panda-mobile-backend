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
        Schema::create('user_listen_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('song_id')->constrained('songs')->onDelete('cascade');
            $table->integer('play_count')->default(1)->comment('How many times user played this song');
            $table->integer('duration_listened')->default(0)->comment('Seconds listened');
            $table->boolean('completed')->default(false)->comment('Did user finish the song?');
            $table->timestamps();

            // Unique constraint: one record per user-song pair
            $table->unique(['user_id', 'song_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_listen_history');
    }
};
