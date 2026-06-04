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
            if (!Schema::hasColumn('songs', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('album_id')->constrained('genres')->nullOnDelete();
            }
            if (!Schema::hasColumn('songs', 'mood')) {
                $table->string('mood')->nullable()->after('lyrics');
            }
            if (!Schema::hasColumn('songs', 'song_type')) {
                $table->string('song_type')->nullable()->after('mood');
            }
            if (!Schema::hasColumn('songs', 'bpm')) {
                $table->integer('bpm')->nullable()->after('song_type');
            }
            if (!Schema::hasColumn('songs', 'is_explicit')) {
                $table->boolean('is_explicit')->default(false)->after('bpm');
            }
            if (!Schema::hasColumn('songs', 'featured_artists')) {
                $table->text('featured_artists')->nullable()->after('is_explicit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['mood', 'song_type', 'bpm', 'is_explicit', 'featured_artists']);
        });
    }
};
