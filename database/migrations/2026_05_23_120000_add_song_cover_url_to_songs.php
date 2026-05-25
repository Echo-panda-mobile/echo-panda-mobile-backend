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
        if (! Schema::hasColumn('songs', 'song_cover_url')) {
            Schema::table('songs', function (Blueprint $table) {
                $table->string('song_cover_url', 2048)->nullable()->after('audio_url');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('songs', 'song_cover_url')) {
            Schema::table('songs', function (Blueprint $table) {
                $table->dropColumn('song_cover_url');
            });
        }
    }
};
