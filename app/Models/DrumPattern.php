<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DrumPattern extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'filename',
        'genre',
        'length_bars',
        'feel',
        'tempo',
        'song_part',
        'additional_parameters',
        'file_path',
        'download_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'additional_parameters' => 'array',
        'length_bars' => 'integer',
        'tempo' => 'integer',
        'download_count' => 'integer',
    ];

    /**
     * Valid feels for drum patterns
     *
     * @var array<string>
     */
    public static $validFeels = ['half_time', 'normal_time', 'double_time'];

    /**
     * Valid song parts for drum patterns
     *
     * @var array<string>
     */
    public static $validSongParts = ['intro', 'verse', 'chorus', 'bridge', 'outro', 'fill'];

    /**
     * Valid time signatures for drum patterns
     *
     * @var array<string>
     */
    public static $validTimeSignatures = [
        '4/4',   // Common time
        '3/4',   // Waltz time
        '6/8',   // Compound duple meter
        '12/8',  // Compound quadruple meter
        '5/4',   // Take Five style
        '7/8'    // Complex meter
    ];

    /**
     * Get the numerator and denominator from a time signature string
     *
     * @param string $timeSignature
     * @return array{numerator: int, denominator: int}
     */
    public static function parseTimeSignature(string $timeSignature): array
    {
        $parts = explode('/', $timeSignature);
        return [
            'numerator' => (int)$parts[0],
            'denominator' => (int)$parts[1]
        ];
    }

    /**
     * Increment the download count for this pattern
     *
     * @return void
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    /**
     * Generate a unique filename based on pattern attributes
     *
     * @return string
     */
    public static function generateFilename(array $attributes): string
    {
        $timestamp = now()->format('YmdHis');
        return sprintf(
            'drum_pattern_%s_%s_%s_%dbpm_%dbars_%s',
            $attributes['genre'],
            $attributes['song_part'],
            $attributes['feel'],
            $attributes['tempo'],
            $attributes['length_bars'],
            $timestamp
        );
    }
}
