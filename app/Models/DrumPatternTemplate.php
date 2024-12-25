<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrumPatternTemplate extends Model
{
    protected $fillable = [
        'name',
        'genre',
        'time_signature',
        'feel',
        'song_part',
        'length_beats',
        'description',
        'is_fill',
        'complexity'
    ];

    protected $casts = [
        'is_fill' => 'boolean',
        'complexity' => 'integer',
        'length_beats' => 'integer'
    ];

    /**
     * Get all hits in this pattern
     */
    public function hits(): HasMany
    {
        return $this->hasMany(DrumHit::class, 'pattern_template_id')->orderBy('position');
    }

    /**
     * Get all instruments used in this pattern
     */
    public function instruments()
    {
        return $this->belongsToMany(DrumInstrument::class, 'drum_hits', 'pattern_template_id', 'instrument_id')
            ->distinct();
    }
} 