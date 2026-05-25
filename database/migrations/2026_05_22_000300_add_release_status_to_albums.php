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
            if (! Schema::hasColumn('albums', 'release_status')) {
                $table->enum('release_status', ['draft', 'pending_review', 'published', 'rejected'])
                    ->default('draft')
                    ->after('description');
            }

            if (! Schema::hasColumn('albums', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('release_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            if (Schema::hasColumn('albums', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }

            if (Schema::hasColumn('albums', 'release_status')) {
                $table->dropColumn('release_status');
            }
        });
    }
};
