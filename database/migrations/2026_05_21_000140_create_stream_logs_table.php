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
        Schema::create('stream_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('song_id')->constrained('songs')->cascadeOnDelete();
            $table->string('quality', 10)->nullable();
            $table->unsignedBigInteger('range_start')->nullable();
            $table->unsignedBigInteger('range_end')->nullable();
            $table->unsignedBigInteger('bytes_sent')->default(0);
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->unsignedSmallInteger('status_code')->default(200);
            $table->timestamp('started_at')->useCurrent()->index();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['song_id', 'started_at']);
            $table->index(['user_id', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stream_logs');
    }
};
