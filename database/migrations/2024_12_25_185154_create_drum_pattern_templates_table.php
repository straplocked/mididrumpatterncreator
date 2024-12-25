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
        Schema::create('drum_pattern_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // e.g., 'Basic Rock Beat', 'Jazz Swing'
            $table->string('genre', 50);         // e.g., 'rock', 'jazz'
            $table->string('time_signature', 10); // e.g., '4/4', '3/4'
            $table->string('feel', 50);          // e.g., 'normal_time', 'half_time'
            $table->string('song_part', 50);     // e.g., 'verse', 'chorus'
            $table->integer('length_beats');     // Number of beats in the pattern
            $table->text('description')->nullable();
            $table->boolean('is_fill')->default(false);
            $table->integer('complexity')->default(1); // 1-5 scale
            $table->timestamps();
            
            // Indexes for commonly queried fields
            $table->index(['genre', 'time_signature', 'feel', 'song_part']);
            $table->index('complexity');
            $table->index('is_fill');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drum_pattern_templates');
    }
};
