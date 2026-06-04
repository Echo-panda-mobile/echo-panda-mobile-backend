<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            if (! Schema::hasColumn('songs', 'tag_id')) {
                $table->foreignId('tag_id')
                    ->nullable()
                    ->constrained('tags')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('songs', function (Blueprint $table) {
            if (Schema::hasColumn('songs', 'tag_id')) {
                $table->dropConstrainedForeignId('tag_id');
            }
        });
    }
};
