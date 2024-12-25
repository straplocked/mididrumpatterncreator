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
        Schema::create('drum_instruments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);          // e.g., 'bass_drum', 'snare', etc.
            $table->string('display_name', 50);  // e.g., 'Bass Drum', 'Snare', etc.
            $table->integer('midi_note');        // MIDI note number
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique('name');
            $table->index('midi_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drum_instruments');
    }
};
