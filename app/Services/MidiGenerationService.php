<?php

namespace App\Services;

use App\Models\DrumPatternTemplate;

class MidiGenerationService
{
    /**
     * Generate MIDI file content for a drum pattern
     *
     * @param array $parameters
     * @return string
     */
    public function generateMidiFile(array $parameters): string
    {
        $pattern = $this->getPatternForParameters($parameters);
        return $this->createMidiFileContent($pattern, $parameters['time_signature']);
    }

    /**
     * Get the drum pattern based on the provided parameters
     *
     * @param array $parameters
     * @return array
     */
    private function getPatternForParameters(array $parameters): array
    {
        // Find matching pattern template
        $template = DrumPatternTemplate::where([
            'genre' => $parameters['genre'],
            'time_signature' => $parameters['time_signature'],
            'feel' => $parameters['feel'],
            'song_part' => $parameters['song_part']
        ])->first();

        \Log::debug('Pattern search parameters:', $parameters);
        \Log::debug('Selected template:', ['template' => $template ? $template->toArray() : null]);

        if (!$template) {
            // Log the attempted parameters
            \Log::debug('No template found for parameters:', $parameters);
            // Fallback to basic pattern if no match found
            $template = DrumPatternTemplate::where([
                'genre' => 'rock',
                'time_signature' => '4/4',
                'feel' => 'normal_time',
                'song_part' => 'verse'
            ])->first();
            
            if (!$template) {
                \Log::error('No fallback template found');
                throw new \Exception('No drum pattern template found');
            }
            \Log::debug('Using fallback template:', ['template' => $template->toArray()]);
        }

        // Get all hits for the pattern, ordered by position
        $hits = $template->hits()->with('instrument')->orderBy('position')->get();
        \Log::debug('Found hits:', [
            'count' => $hits->count(),
            'hits' => $hits->map(function($hit) {
                return [
                    'position' => $hit->position,
                    'instrument' => $hit->instrument ? $hit->instrument->name : null
                ];
            })->toArray()
        ]);

        // Group hits by position
        $pattern = [];
        $currentPosition = -1;
        $currentBeat = [];
        $currentSubBeat = [];

        foreach ($hits as $hit) {
            if ($hit->position !== $currentPosition) {
                if (!empty($currentSubBeat)) {
                    $currentBeat[] = $currentSubBeat;
                    $currentSubBeat = [];
                }
                if ($hit->position % ($template->length_beats * 96) === 0 && !empty($currentBeat)) {
                    $pattern = array_merge($pattern, $currentBeat);
                    $currentBeat = [];
                }
                $currentPosition = $hit->position;
            }
            // Make sure we have a valid instrument relationship
            if ($hit->instrument) {
                $currentSubBeat[] = $hit->instrument->name;
            } else {
                \Log::warning('Hit without instrument:', ['hit_id' => $hit->id]);
            }
        }

        // Add any remaining hits
        if (!empty($currentSubBeat)) {
            $currentBeat[] = $currentSubBeat;
        }
        if (!empty($currentBeat)) {
            $pattern = array_merge($pattern, $currentBeat);
        }

        // Log the pattern structure
        \Log::debug('Generated pattern structure:', ['pattern' => $pattern]);

        // Repeat pattern to match requested length
        $fullPattern = [];
        $numBars = $parameters['length_bars'];
        for ($bar = 0; $bar < $numBars; $bar++) {
            $fullPattern = array_merge($fullPattern, $pattern);
        }

        return $fullPattern;
    }

    /**
     * Write a variable-length value
     *
     * @param int $value
     * @return string
     */
    private function writeVarLen(int $value): string
    {
        if ($value < 128) {
            return chr($value);
        }
        
        $bytes = [];
        while ($value > 0) {
            array_unshift($bytes, $value & 0x7F);
            $value >>= 7;
        }
        
        $result = '';
        for ($i = 0; $i < count($bytes) - 1; $i++) {
            $result .= chr($bytes[$i] | 0x80);
        }
        $result .= chr(end($bytes));
        
        return $result;
    }

    /**
     * Create MIDI file content
     *
     * @param array $pattern
     * @param string $timeSignature
     * @return string
     */
    private function createMidiFileContent(array $pattern, string $timeSignature): string
    {
        $trackData = '';
        \Log::debug('Creating MIDI content for pattern:', [
            'pattern' => $pattern,
            'timeSignature' => $timeSignature
        ]);

        // Get time signature parts
        $timeSigParts = explode('/', $timeSignature);
        $numerator = (int)$timeSigParts[0];
        $denominator = (int)$timeSigParts[1];
        
        // Track MIDI notes and their usage
        $usedNotes = [];
        
        // Process each hit in the pattern
        foreach ($pattern as $beat) {
            if (!is_array($beat)) {
                continue;
            }
            
            // Write all notes that start at this time together
            foreach ($beat as $instrumentName) {
                if (!is_string($instrumentName)) {
                    continue;
                }
                
                // Get MIDI note from cache or database
                if (!isset($midiNotes[$instrumentName])) {
                    $midiNotes[$instrumentName] = DrumPatternTemplate::query()
                        ->join('drum_hits', 'drum_pattern_templates.id', '=', 'drum_hits.pattern_template_id')
                        ->join('drum_instruments', 'drum_hits.instrument_id', '=', 'drum_instruments.id')
                        ->where('drum_instruments.name', $instrumentName)
                        ->value('drum_instruments.midi_note') ?? 36; // Fallback to bass drum
                    
                    if (!isset($usedNotes[$instrumentName])) {
                        $usedNotes[$instrumentName] = [
                            'midi_note' => $midiNotes[$instrumentName],
                            'count' => 0
                        ];
                    }
                }
                
                $midiNote = $midiNotes[$instrumentName];
                $usedNotes[$instrumentName]['count']++;
                
                // Add humanization to timing (-2 to +2 ticks)
                $timingOffset = rand(-2, 2);
                
                // Add humanization to velocity (base velocity ±10%)
                $baseVelocity = $this->getBaseVelocityForInstrument($instrumentName);
                $velocity = $baseVelocity + rand(-10, 10);
                $velocity = max(60, min(127, $velocity)); // Keep within reasonable bounds

                \Log::debug('Note event:', [
                    'instrument' => $instrumentName,
                    'midi_note' => $midiNote,
                    'timing_offset' => $timingOffset,
                    'velocity' => $velocity
                ]);
                
                // Note on with humanized timing
                if ($timingOffset > 0) {
                    $trackData .= chr($timingOffset);    // Delay the note slightly
                } else {
                    $trackData .= chr(0x00);            // No delay
                }
                $trackData .= chr(0x90);                // Note on, channel 1
                $trackData .= chr($midiNote);           // Note number
                $trackData .= chr($velocity);           // Humanized velocity
            }
            
            // Write all note-offs after a fixed duration
            $trackData .= chr(12);                      // Delta time (12 ticks, like example)
            foreach ($beat as $instrumentName) {
                if (!is_string($instrumentName)) {
                    continue;
                }
                $midiNote = $midiNotes[$instrumentName];
                
                // Add slight humanization to note-off timing (-1 to +1 ticks)
                $offTimingOffset = rand(-1, 1);
                if ($offTimingOffset > 0) {
                    $trackData .= chr($offTimingOffset); // Slight delay in note-off
                } else {
                    $trackData .= chr(0x00);            // No delay
                }
                
                $trackData .= chr(0x80);                // Note off
                $trackData .= chr($midiNote);           // Note number
                $trackData .= chr(0x4E);                // Release velocity
            }
            
            // Add slightly humanized time until next beat (36 ±2 ticks)
            $nextBeatOffset = 36 + rand(-2, 2);
            $trackData .= chr($nextBeatOffset);
        }
        
        // End of track
        $trackData .= chr(0x00);              // Delta time
        $trackData .= chr(0xFF);              // Meta event
        $trackData .= chr(0x2F);              // End of track
        $trackData .= chr(0x00);              // Length
        
        // Add track header and data
        $data = "MThd";
        $data .= pack("N", 6);                // Header length
        $data .= pack("n", 0);                // Format 0
        $data .= pack("n", 1);                // One track
        $data .= pack("n", 96);               // 96 ticks per quarter note (0x60)
        
        $data .= "MTrk";
        $data .= pack("N", strlen($trackData));
        $data .= $trackData;
        
        \Log::debug('MIDI generation summary:', [
            'used_notes' => $usedNotes,
            'time_signature' => "{$numerator}/{$denominator}"
        ]);

        return $data;
    }

    /**
     * Get the base velocity for different instruments
     * This helps create more realistic dynamics between instruments
     */
    private function getBaseVelocityForInstrument(string $instrumentName): int
    {
        return match($instrumentName) {
            'bass_drum' => 110,    // Strong bass drum
            'snare' => 100,        // Solid snare
            'closed_hh' => 85,     // Lighter hi-hat
            'open_hh' => 90,       // Slightly stronger open hi-hat
            'crash' => 115,        // Strong crash
            'ride' => 95,          // Medium ride
            'tom1' => 100,         // Strong high tom
            'tom2' => 100,         // Strong mid tom
            'tom3' => 100,         // Strong low tom
            'rimshot' => 95,       // Medium rimshot
            'clap' => 100,         // Strong clap
            'cowbell' => 90,       // Medium cowbell
            'tambourine' => 85,    // Light tambourine
            'conga_high' => 95,    // Medium high conga
            'conga_low' => 95,     // Medium low conga
            default => 100,        // Default medium velocity
        };
    }
} 