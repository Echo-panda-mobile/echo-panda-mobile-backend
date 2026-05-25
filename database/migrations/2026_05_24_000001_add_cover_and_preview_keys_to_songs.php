<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            if (! Schema::hasColumn('songs', 'cover_key')) {
                $table->string('cover_key', 1024)->nullable()->after('original_key');
            }

            if (! Schema::hasColumn('songs', 'preview_key')) {
                $table->string('preview_key', 1024)->nullable()->after('cover_key');
            }
        });

        if (Schema::hasTable('songs')) {
            if (Schema::hasColumn('songs', 'song_cover_url')) {
                DB::table('songs')
                    ->whereNull('cover_key')
                    ->whereNotNull('song_cover_url')
                    ->update(['cover_key' => DB::raw('song_cover_url')]);
            }

            if (Schema::hasColumn('songs', 'preview_path')) {
                DB::table('songs')
                    ->whereNull('preview_key')
                    ->whereNotNull('preview_path')
                    ->update(['preview_key' => DB::raw('preview_path')]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            foreach (['preview_key', 'cover_key'] as $column) {
                if (Schema::hasColumn('songs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};