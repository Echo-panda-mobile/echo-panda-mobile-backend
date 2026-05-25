<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            if (! Schema::hasColumn('albums', 'cover_key')) {
                $table->string('cover_key', 1024)->nullable()->after('scheduled_at');
            }
        });

        if (Schema::hasTable('albums') && Schema::hasColumn('albums', 'cover_url')) {
            DB::table('albums')
                ->whereNull('cover_key')
                ->whereNotNull('cover_url')
                ->update(['cover_key' => DB::raw('cover_url')]);
        }
    }

    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            if (Schema::hasColumn('albums', 'cover_key')) {
                $table->dropColumn('cover_key');
            }
        });
    }
};