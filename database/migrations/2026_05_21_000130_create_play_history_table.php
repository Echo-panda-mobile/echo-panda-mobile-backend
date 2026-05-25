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
        Schema::create('play_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('song_id')->constrained('songs')->cascadeOnDelete();
            $table->timestamp('played_at')->useCurrent()->index();
            $table->unsignedInteger('progress_seconds')->default(0);
            $table->boolean('completed')->default(false);
            $table->string('source', 30)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'played_at']);
            $table->index(['song_id', 'played_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('play_history');
    }
};
