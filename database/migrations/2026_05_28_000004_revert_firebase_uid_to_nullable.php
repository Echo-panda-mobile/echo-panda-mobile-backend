<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Revert firebase_uid to nullable.
     * 
     * While we want firebase_uid to be set for all active users,
     * making it NOT NULL breaks the creation flow:
     *   1. Create user in Laravel
     *   2. Provision to Firebase (async or may fail)
     *   3. Update user with firebase_uid
     *
     * Application-level validation ensures active users have firebase_uid.
     * Database constraint can be enforced after provisioning completes.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('firebase_uid', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't revert to NOT NULL; that breaks user creation flow
        // Keep it nullable - application-level checks ensure active users are provisioned
    }
};
