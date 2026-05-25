<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            if (! Schema::hasColumn('songs', 'processing_status')) {
                $table->enum('processing_status', ['draft','uploaded','processing','ready','failed'])
                      ->default('draft')
                      ->after('published_at');
            }

            if (! Schema::hasColumn('songs', 'processing_error')) {
                $table->text('processing_error')->nullable()->after('processing_status');
            }

            if (! Schema::hasColumn('songs', 's3_original_key')) {
                $table->string('s3_original_key', 1024)->nullable()->after('processing_error');
            }

            if (! Schema::hasColumn('songs', 'waveform_json')) {
                $table->json('waveform_json')->nullable()->after('s3_original_key');
            }
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            foreach (['waveform_json','s3_original_key','processing_error','processing_status'] as $col) {
                if (Schema::hasColumn('songs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
