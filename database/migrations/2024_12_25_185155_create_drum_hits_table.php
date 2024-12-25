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
        Schema::create('drum_hits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pattern_template_id')->constrained('drum_pattern_templates')->onDelete('cascade');
            $table->foreignId('instrument_id')->constrained('drum_instruments')->onDelete('cascade');
            $table->integer('position');        // Position in ticks from start of pattern
            $table->integer('velocity')->default(100);  // MIDI velocity (0-127)
            $table->integer('duration')->default(24);   // Duration in ticks
            $table->timestamps();
            
            // Index for efficient pattern retrieval
            $table->index(['pattern_template_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drum_hits');
    }
};
