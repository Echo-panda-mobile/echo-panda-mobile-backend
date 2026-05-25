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
        Schema::table('artists', function (Blueprint $table) {
            if (! Schema::hasColumn('artists', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }

            if (! Schema::hasColumn('artists', 'verification_status')) {
                $table->enum('verification_status', ['pending', 'approved', 'rejected'])
                      ->default('pending')
                      ->after('is_active');
                $table->text('verification_reason')->nullable()->after('verification_status');
                $table->timestamp('verified_at')->nullable()->after('verification_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            if (Schema::hasColumn('artists', 'verified_at')) {
                $table->dropColumn('verified_at');
            }
            if (Schema::hasColumn('artists', 'verification_reason')) {
                $table->dropColumn('verification_reason');
            }
            if (Schema::hasColumn('artists', 'verification_status')) {
                $table->dropColumn('verification_status');
            }
            if (Schema::hasColumn('artists', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
