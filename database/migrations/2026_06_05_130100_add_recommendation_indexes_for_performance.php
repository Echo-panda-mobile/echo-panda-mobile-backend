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
        Schema::table('songs', function (Blueprint $table) {
            $table->index(['is_active', 'category_id'], 'songs_active_category_idx');
            $table->index(['is_active', 'artist_id'], 'songs_active_artist_idx');
            $table->index(['is_active', 'mood'], 'songs_active_mood_idx');
            $table->index(['is_active', 'tag_id'], 'songs_active_tag_idx');
            $table->index(['is_active', 'play_count'], 'songs_active_play_count_idx');
            $table->index('created_at', 'songs_created_at_idx');
        });

        Schema::table('user_preferences', function (Blueprint $table) {
            $table->index(['user_id', 'preference_type', 'preference_score'], 'user_pref_user_type_score_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropIndex('user_pref_user_type_score_idx');
        });

        Schema::table('songs', function (Blueprint $table) {
            $table->dropIndex('songs_active_category_idx');
            $table->dropIndex('songs_active_artist_idx');
            $table->dropIndex('songs_active_mood_idx');
            $table->dropIndex('songs_active_tag_idx');
            $table->dropIndex('songs_active_play_count_idx');
            $table->dropIndex('songs_created_at_idx');
        });
    }
};
