<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generated_playlists', function (Blueprint $table) {
            $table->string('type')->nullable()->after('cover_url'); // daily_mix, discover_weekly, trending, ai_prompt
        });
    }

    public function down(): void
    {
        Schema::table('generated_playlists', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
