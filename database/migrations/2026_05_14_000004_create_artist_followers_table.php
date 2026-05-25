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
        Schema::create('artist_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Follower');
            $table->foreignId('artist_user_id')->constrained('users')->onDelete('cascade')->comment('Artist being followed');
            $table->timestamps();

            // One follow per user-artist pair
            $table->unique(['user_id', 'artist_user_id']);
            $table->index('user_id');
            $table->index('artist_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artist_followers');
    }
};
