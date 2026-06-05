<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Generated Playlists Metadata
        Schema::create('generated_playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('prompt');
            $table->json('extracted_criteria')->nullable(); // AI results
            $table->string('cover_url')->nullable();
            $table->timestamps();
        });

        // 2. Playlist Songs (Junction)
        Schema::create('generated_playlist_songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained('generated_playlists')->onDelete('cascade');
            $table->foreignId('song_id')->constrained()->onDelete('cascade');
            $table->integer('position');
            $table->timestamps();
        });

        // 3. User Prompt History
        Schema::create('playlist_prompts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('prompt');
            $table->foreignId('generated_playlist_id')->nullable()->constrained('generated_playlists')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playlist_prompts');
        Schema::dropIfExists('generated_playlist_songs');
        Schema::dropIfExists('generated_playlists');
    }
};
