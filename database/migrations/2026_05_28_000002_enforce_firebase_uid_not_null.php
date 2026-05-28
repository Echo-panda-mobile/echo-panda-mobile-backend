<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Enforce firebase_uid as NOT NULL.
     * UNIQUE constraint already exists from previous migration.
     * All users should have firebase_uid by now (via orphan provisioning).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only change to NOT NULL, UNIQUE already exists
            $table->string('firebase_uid', 255)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert to nullable
            $table->string('firebase_uid', 255)->nullable()->change();
        });
    }
};
