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
        // Add S3 audio URL to songs
        Schema::table('songs', function (Blueprint $table) {
            $table->string('s3_audio_url')->nullable()->comment('S3 URL for streaming audio');
            $table->string('s3_lyrics_url')->nullable()->comment('S3 URL for live lyrics (JSON)');
        });

        // Add S3 cover image URL to albums
        Schema::table('albums', function (Blueprint $table) {
            $table->string('s3_cover_image_url')->nullable()->comment('S3 URL for album cover');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropColumn(['s3_audio_url', 's3_lyrics_url']);
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn('s3_cover_image_url');
        });
    }
};
