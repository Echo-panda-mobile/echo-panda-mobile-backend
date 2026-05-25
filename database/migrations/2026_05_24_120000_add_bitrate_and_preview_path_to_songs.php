<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            if (! Schema::hasColumn('songs', 'bitrate')) {
                $table->unsignedInteger('bitrate')->nullable()->after('duration');
            }

            if (! Schema::hasColumn('songs', 'preview_path')) {
                $table->string('preview_path', 1024)->nullable()->after('song_cover_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            foreach (['preview_path', 'bitrate'] as $column) {
                if (Schema::hasColumn('songs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};