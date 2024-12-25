<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrumInstrument extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'midi_note',
        'description'
    ];

    /**
     * Get all hits using this instrument
     */
    public function hits(): HasMany
    {
        return $this->hasMany(DrumHit::class, 'instrument_id');
    }
} 