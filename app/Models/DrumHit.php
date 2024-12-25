<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrumHit extends Model
{
    protected $fillable = [
        'pattern_template_id',
        'instrument_id',
        'position',
        'velocity',
        'duration'
    ];

    protected $casts = [
        'position' => 'integer',
        'velocity' => 'integer',
        'duration' => 'integer'
    ];

    /**
     * Get the pattern template this hit belongs to
     */
    public function patternTemplate(): BelongsTo
    {
        return $this->belongsTo(DrumPatternTemplate::class, 'pattern_template_id');
    }

    /**
     * Get the instrument for this hit
     */
    public function instrument(): BelongsTo
    {
        return $this->belongsTo(DrumInstrument::class, 'instrument_id');
    }
} 