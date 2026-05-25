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
        Schema::create('lyrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('song_id')->unique()->constrained('songs')->cascadeOnDelete();
            $table->string('format', 20)->default('lrc');
            $table->text('lrc_content')->nullable();
            $table->json('parsed_json')->nullable();
            $table->string('language', 10)->nullable();
            $table->timestamps();

            $table->index('song_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lyrics');
    }
};
