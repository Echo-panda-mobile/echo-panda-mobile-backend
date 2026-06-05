<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('playlists', function (Blueprint $table) {
            if (! Schema::hasColumn('playlists', 'description')) {
                $table->text('description')->nullable()->after('name');
            }

            if (! Schema::hasColumn('playlists', 'image_url')) {
                $table->string('image_url', 2048)->nullable()->after('name');
            }

            if (! Schema::hasColumn('playlists', 'cover_key')) {
                $table->string('cover_key', 1024)->nullable()->after(
                    Schema::hasColumn('playlists', 'description') ? 'description' : 'name'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('playlists', function (Blueprint $table) {
            if (Schema::hasColumn('playlists', 'cover_key')) {
                $table->dropColumn('cover_key');
            }
        });
    }
};
