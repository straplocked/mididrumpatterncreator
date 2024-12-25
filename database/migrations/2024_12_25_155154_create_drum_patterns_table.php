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
        Schema::create('drum_patterns', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->unique();
            $table->string('genre', 50);
            $table->integer('length_bars');
            $table->string('feel', 50);
            $table->integer('tempo');
            $table->string('song_part', 50);
            $table->string('time_signature', 10)->default('4/4');
            $table->json('additional_parameters')->nullable();
            $table->string('file_path');
            $table->integer('download_count')->default(0);
            $table->timestamps();
            
            // Add indexes for commonly queried fields
            $table->index('genre');
            $table->index('feel');
            $table->index('song_part');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drum_patterns');
    }
};
