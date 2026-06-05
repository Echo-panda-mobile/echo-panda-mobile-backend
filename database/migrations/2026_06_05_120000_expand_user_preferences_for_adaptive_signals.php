<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->string('preference_type')->default('genre')->after('user_id');
            $table->string('preference_value')->default('')->after('preference_type');
        });

        DB::table('user_preferences')
            ->where(function ($q) {
                $q->whereNull('preference_value')->orWhere('preference_value', '');
            })
            ->update([
                'preference_type' => 'genre',
                'preference_value' => DB::raw('genre'),
            ]);

        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropUnique('user_preferences_user_id_genre_unique');
            $table->unique(['user_id', 'preference_type', 'preference_value'], 'user_preferences_user_type_value_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropUnique('user_preferences_user_type_value_unique');
            $table->unique(['user_id', 'genre'], 'user_preferences_user_id_genre_unique');
            $table->dropColumn(['preference_type', 'preference_value']);
        });
    }
};
