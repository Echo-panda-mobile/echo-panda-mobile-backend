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
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('target_type'); // song, album, artist, playlist
            $table->unsignedBigInteger('target_id');
            $table->string('platform'); // instagram, facebook, messenger, telegram, whatsapp, copy_link
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
            $table->index(['platform']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shares');
    }
};
