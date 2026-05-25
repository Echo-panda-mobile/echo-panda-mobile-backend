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
        Schema::table('albums', function (Blueprint $table) {
            if (! Schema::hasColumn('albums', 'artist_id')) {
                $table->foreignId('artist_id')->nullable()->after('id')->constrained('artists')->nullOnDelete();
                $table->index('artist_id');
            }

            if (! Schema::hasColumn('albums', 'slug')) {
                $table->string('slug', 220)->nullable()->after('title');
                $table->unique('slug');
            }
        });

        Schema::table('songs', function (Blueprint $table) {
            if (! Schema::hasColumn('songs', 'artist_id')) {
                $table->foreignId('artist_id')->nullable()->after('album_id')->constrained('artists')->nullOnDelete();
                $table->index('artist_id');
            }

            if (! Schema::hasColumn('songs', 'slug')) {
                $table->string('slug', 275)->nullable()->after('title');
                $table->unique('slug');
            }

            if (! Schema::hasColumn('songs', 'file_size_bytes')) {
                $table->unsignedBigInteger('file_size_bytes')->default(0)->after('duration');
            }

            if (! Schema::hasColumn('songs', 'mime_type')) {
                $table->string('mime_type', 100)->default('audio/mpeg')->after('file_size_bytes');
            }

            if (! Schema::hasColumn('songs', 's3_key_128')) {
                $table->string('s3_key_128', 1024)->nullable()->after('s3_audio_url');
            }

            if (! Schema::hasColumn('songs', 's3_key_320')) {
                $table->string('s3_key_320', 1024)->nullable()->after('s3_key_128');
            }

            if (! Schema::hasColumn('songs', 'default_quality')) {
                $table->string('default_quality', 10)->default('320')->after('s3_key_320');
            }

            if (! Schema::hasColumn('songs', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('default_quality');
                $table->index('is_active');
            }

            if (! Schema::hasColumn('songs', 'play_count')) {
                $table->unsignedBigInteger('play_count')->default(0)->after('is_active');
            }

            if (! Schema::hasColumn('songs', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('play_count');
                $table->index('published_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            $dropColumns = [
                'artist_id',
                'slug',
                'file_size_bytes',
                'mime_type',
                's3_key_128',
                's3_key_320',
                'default_quality',
                'is_active',
                'play_count',
                'published_at',
            ];

            foreach ($dropColumns as $column) {
                if (Schema::hasColumn('songs', $column)) {
                    if ($column === 'artist_id') {
                        $table->dropConstrainedForeignId('artist_id');
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });

        Schema::table('albums', function (Blueprint $table) {
            if (Schema::hasColumn('albums', 'artist_id')) {
                $table->dropConstrainedForeignId('artist_id');
            }

            if (Schema::hasColumn('albums', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
