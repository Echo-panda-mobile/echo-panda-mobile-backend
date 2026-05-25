<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RenameS3ColumnsToStandardNames extends Migration
{
    public function up()
    {
        // Songs table: add new columns, copy data, drop old s3_ columns
        Schema::table('songs', function (Blueprint $table) {
            if (! Schema::hasColumn('songs', 'audio_url')) {
                $table->string('audio_url')->nullable()->after('duration');
            }
            if (! Schema::hasColumn('songs', 'lyrics_url')) {
                $table->string('lyrics_url')->nullable()->after('audio_url');
            }
            if (! Schema::hasColumn('songs', 'variant_key_128')) {
                $table->string('variant_key_128', 1024)->nullable()->after('lyrics_url');
            }
            if (! Schema::hasColumn('songs', 'variant_key_320')) {
                $table->string('variant_key_320', 1024)->nullable()->after('variant_key_128');
            }
            if (! Schema::hasColumn('songs', 'original_key')) {
                $table->string('original_key', 1024)->nullable()->after('variant_key_320');
            }
        });

        // Albums table: add cover_url
        Schema::table('albums', function (Blueprint $table) {
            if (! Schema::hasColumn('albums', 'cover_url')) {
                $table->string('cover_url')->nullable()->after('title');
            }
        });

        // Copy data from old s3_ columns into new columns
        if (Schema::hasTable('songs')) {
            DB::table('songs')->whereNotNull('s3_audio_url')->update(['audio_url' => DB::raw('s3_audio_url')]);
            DB::table('songs')->whereNotNull('s3_lyrics_url')->update(['lyrics_url' => DB::raw('s3_lyrics_url')]);
            DB::table('songs')->whereNotNull('s3_key_128')->update(['variant_key_128' => DB::raw('s3_key_128')]);
            DB::table('songs')->whereNotNull('s3_key_320')->update(['variant_key_320' => DB::raw('s3_key_320')]);
            DB::table('songs')->whereNotNull('s3_original_key')->update(['original_key' => DB::raw('s3_original_key')]);
        }

        if (Schema::hasTable('albums')) {
            DB::table('albums')->whereNotNull('s3_cover_image_url')->update(['cover_url' => DB::raw('s3_cover_image_url')]);
        }

        // Finally drop old s3_ columns if they exist
        Schema::table('songs', function (Blueprint $table) {
            foreach (['s3_audio_url','s3_lyrics_url','s3_key_128','s3_key_320','s3_original_key'] as $col) {
                if (Schema::hasColumn('songs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('albums', function (Blueprint $table) {
            if (Schema::hasColumn('albums', 's3_cover_image_url')) {
                $table->dropColumn('s3_cover_image_url');
            }
        });
    }

    public function down()
    {
        // Recreate old columns (best-effort) and copy data back
        Schema::table('songs', function (Blueprint $table) {
            if (! Schema::hasColumn('songs', 's3_audio_url')) {
                $table->string('s3_audio_url')->nullable()->after('duration');
            }
            if (! Schema::hasColumn('songs', 's3_lyrics_url')) {
                $table->string('s3_lyrics_url')->nullable()->after('s3_audio_url');
            }
            if (! Schema::hasColumn('songs', 's3_key_128')) {
                $table->string('s3_key_128', 1024)->nullable()->after('s3_lyrics_url');
            }
            if (! Schema::hasColumn('songs', 's3_key_320')) {
                $table->string('s3_key_320', 1024)->nullable()->after('s3_key_128');
            }
            if (! Schema::hasColumn('songs', 's3_original_key')) {
                $table->string('s3_original_key', 1024)->nullable()->after('s3_key_320');
            }
        });

        Schema::table('albums', function (Blueprint $table) {
            if (! Schema::hasColumn('albums', 's3_cover_image_url')) {
                $table->string('s3_cover_image_url')->nullable()->after('title');
            }
        });

        if (Schema::hasTable('songs')) {
            DB::table('songs')->whereNotNull('audio_url')->update(['s3_audio_url' => DB::raw('audio_url')]);
            DB::table('songs')->whereNotNull('lyrics_url')->update(['s3_lyrics_url' => DB::raw('lyrics_url')]);
            DB::table('songs')->whereNotNull('variant_key_128')->update(['s3_key_128' => DB::raw('variant_key_128')]);
            DB::table('songs')->whereNotNull('variant_key_320')->update(['s3_key_320' => DB::raw('variant_key_320')]);
            DB::table('songs')->whereNotNull('original_key')->update(['s3_original_key' => DB::raw('original_key')]);
        }

        if (Schema::hasTable('albums')) {
            DB::table('albums')->whereNotNull('cover_url')->update(['s3_cover_image_url' => DB::raw('cover_url')]);
        }

        Schema::table('songs', function (Blueprint $table) {
            foreach (['audio_url','lyrics_url','variant_key_128','variant_key_320','original_key'] as $col) {
                if (Schema::hasColumn('songs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('albums', function (Blueprint $table) {
            if (Schema::hasColumn('albums', 'cover_url')) {
                $table->dropColumn('cover_url');
            }
        });
    }
}
