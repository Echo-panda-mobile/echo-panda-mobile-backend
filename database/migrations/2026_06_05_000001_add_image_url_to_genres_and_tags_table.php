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
        Schema::table('genres', function (Blueprint $table) {
            if (! Schema::hasColumn('genres', 'image_url')) {
                $table->string('image_url', 1024)->nullable()->after('slug');
            }
        });

        Schema::table('tags', function (Blueprint $table) {
            if (! Schema::hasColumn('tags', 'image_url')) {
                $table->string('image_url', 1024)->nullable()->after('slug');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('genres', function (Blueprint $table) {
            if (Schema::hasColumn('genres', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });

        Schema::table('tags', function (Blueprint $table) {
            if (Schema::hasColumn('tags', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }
};
