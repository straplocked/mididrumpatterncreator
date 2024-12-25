<?php

namespace Database\Seeders;

use App\Models\DrumInstrument;
use App\Models\DrumPatternTemplate;
use App\Models\DrumHit;
use Illuminate\Database\Seeder;

class DrumPatternSeeder extends Seeder
{
    private $instruments = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createInstruments();
        $this->createRockPatterns();
        $this->createJazzPatterns();
        $this->createFunkPatterns();
        $this->createHipHopPatterns();
        $this->createLatinPatterns();
    }

    private function createInstruments(): void
    {
        $instrumentData = [
            ['name' => 'bass_drum', 'display_name' => 'Bass Drum', 'midi_note' => 36],
            ['name' => 'snare', 'display_name' => 'Snare', 'midi_note' => 38],
            ['name' => 'closed_hh', 'display_name' => 'Closed Hi-Hat', 'midi_note' => 42],
            ['name' => 'open_hh', 'display_name' => 'Open Hi-Hat', 'midi_note' => 46],
            ['name' => 'crash', 'display_name' => 'Crash Cymbal', 'midi_note' => 49],
            ['name' => 'ride', 'display_name' => 'Ride Cymbal', 'midi_note' => 51],
            ['name' => 'tom1', 'display_name' => 'High Tom', 'midi_note' => 48],
            ['name' => 'tom2', 'display_name' => 'Mid Tom', 'midi_note' => 45],
            ['name' => 'tom3', 'display_name' => 'Low Tom', 'midi_note' => 43],
            ['name' => 'rimshot', 'display_name' => 'Rimshot', 'midi_note' => 37],
            ['name' => 'clap', 'display_name' => 'Hand Clap', 'midi_note' => 39],
            ['name' => 'cowbell', 'display_name' => 'Cowbell', 'midi_note' => 56],
            ['name' => 'tambourine', 'display_name' => 'Tambourine', 'midi_note' => 54],
            ['name' => 'conga_high', 'display_name' => 'High Conga', 'midi_note' => 62],
            ['name' => 'conga_low', 'display_name' => 'Low Conga', 'midi_note' => 64],
        ];

        foreach ($instrumentData as $instrument) {
            $this->instruments[$instrument['name']] = DrumInstrument::create($instrument);
        }
    }

    private function createRockPatterns(): void
    {
        // Basic rock beat
        $this->createPattern(
            'Basic Rock Beat',
            'rock',
            '4/4',
            'normal_time',
            'verse',
            4,
            [
                ['instrument' => 'bass_drum', 'positions' => [0, 192]],  // 1 and 3
                ['instrument' => 'snare', 'positions' => [96, 288]],     // 2 and 4
                ['instrument' => 'closed_hh', 'positions' => [0, 48, 96, 144, 192, 240, 288, 336]], // eighth notes
            ]
        );

        // Rock chorus pattern
        $this->createPattern(
            'Rock Chorus',
            'rock',
            '4/4',
            'normal_time',
            'chorus',
            4,
            [
                ['instrument' => 'bass_drum', 'positions' => [0, 96, 192, 288]],  // all quarter notes
                ['instrument' => 'snare', 'positions' => [96, 288]],              // 2 and 4
                ['instrument' => 'crash', 'positions' => [0]],                    // crash on 1
                ['instrument' => 'closed_hh', 'positions' => [48, 144, 240, 336]], // offbeats
            ]
        );

        // Half-time rock pattern
        $this->createPattern(
            'Half-time Rock',
            'rock',
            '4/4',
            'half_time',
            'verse',
            4,
            [
                ['instrument' => 'bass_drum', 'positions' => [0, 144]],          // 1 and 2&
                ['instrument' => 'snare', 'positions' => [192]],                 // 3
                ['instrument' => 'closed_hh', 'positions' => [0, 96, 192, 288]], // quarter notes
            ]
        );
    }

    private function createJazzPatterns(): void
    {
        // Jazz swing ride pattern
        $this->createPattern(
            'Jazz Swing Ride',
            'jazz',
            '4/4',
            'normal_time',
            'verse',
            4,
            [
                ['instrument' => 'ride', 'positions' => [0, 48, 96, 144, 192, 240, 288, 336]],  // swing pattern
                ['instrument' => 'closed_hh', 'positions' => [96, 288]],                        // 2 and 4
                ['instrument' => 'bass_drum', 'positions' => [0, 192]],                         // feathering
            ]
        );

        // Jazz waltz
        $this->createPattern(
            'Jazz Waltz',
            'jazz',
            '3/4',
            'normal_time',
            'verse',
            3,
            [
                ['instrument' => 'ride', 'positions' => [0, 96, 192]],           // quarter notes
                ['instrument' => 'bass_drum', 'positions' => [0]],               // 1
                ['instrument' => 'closed_hh', 'positions' => [96, 192]],         // 2 and 3
            ]
        );

        // Jazz ballad
        $this->createPattern(
            'Jazz Ballad',
            'jazz',
            '4/4',
            'half_time',
            'verse',
            4,
            [
                ['instrument' => 'ride', 'positions' => [0, 48, 96, 144, 192, 240, 288, 336]],  // swing pattern
                ['instrument' => 'bass_drum', 'positions' => [0]],                              // 1
                ['instrument' => 'closed_hh', 'positions' => [192]],                            // 3
            ]
        );
    }

    private function createFunkPatterns(): void
    {
        // Basic funk pattern
        $this->createPattern(
            'Basic Funk',
            'funk',
            '4/4',
            'normal_time',
            'verse',
            4,
            [
                ['instrument' => 'bass_drum', 'positions' => [0, 144, 240]],     // syncopated kick
                ['instrument' => 'snare', 'positions' => [96, 288]],             // 2 and 4
                ['instrument' => 'closed_hh', 'positions' => [0, 48, 96, 144, 192, 240, 288, 336]], // sixteenths
            ]
        );

        // Syncopated funk
        $this->createPattern(
            'Syncopated Funk',
            'funk',
            '4/4',
            'normal_time',
            'verse',
            4,
            [
                ['instrument' => 'bass_drum', 'positions' => [0, 144, 192, 336]],
                ['instrument' => 'snare', 'positions' => [96, 240, 288]],
                ['instrument' => 'closed_hh', 'positions' => [0, 48, 96, 144, 192, 240, 288, 336]],
                ['instrument' => 'open_hh', 'positions' => [144, 336]],
            ]
        );
    }

    private function createHipHopPatterns(): void
    {
        // Boom bap pattern
        $this->createPattern(
            'Boom Bap',
            'hip_hop',
            '4/4',
            'normal_time',
            'verse',
            4,
            [
                ['instrument' => 'bass_drum', 'positions' => [0, 144, 192]],     // kick pattern
                ['instrument' => 'snare', 'positions' => [96, 288]],             // 2 and 4
                ['instrument' => 'closed_hh', 'positions' => [0, 48, 96, 144, 192, 240, 288, 336]], // sixteenths
            ]
        );

        // Trap pattern
        $this->createPattern(
            'Trap Beat',
            'hip_hop',
            '4/4',
            'half_time',
            'verse',
            4,
            [
                ['instrument' => 'bass_drum', 'positions' => [0, 48, 96, 144]],  // rolling kicks
                ['instrument' => 'snare', 'positions' => [192]],                 // 3
                ['instrument' => 'closed_hh', 'positions' => [0, 48, 96, 144, 192, 240, 288, 336]], // fast hats
            ]
        );
    }

    private function createLatinPatterns(): void
    {
        // Basic samba
        $this->createPattern(
            'Basic Samba',
            'latin',
            '4/4',
            'normal_time',
            'verse',
            4,
            [
                ['instrument' => 'bass_drum', 'positions' => [0, 144, 192, 336]],
                ['instrument' => 'snare', 'positions' => [96, 288]],
                ['instrument' => 'closed_hh', 'positions' => [0, 48, 96, 144, 192, 240, 288, 336]],
                ['instrument' => 'conga_high', 'positions' => [48, 144, 240, 336]],
                ['instrument' => 'conga_low', 'positions' => [0, 96, 192, 288]],
            ]
        );

        // Bossa nova
        $this->createPattern(
            'Bossa Nova',
            'latin',
            '4/4',
            'normal_time',
            'verse',
            4,
            [
                ['instrument' => 'bass_drum', 'positions' => [0, 192]],
                ['instrument' => 'snare', 'positions' => [96, 288]],
                ['instrument' => 'closed_hh', 'positions' => [0, 48, 96, 144, 192, 240, 288, 336]],
                ['instrument' => 'cowbell', 'positions' => [0, 96, 192, 288]],
            ]
        );
    }

    private function createPattern(string $name, string $genre, string $timeSignature, string $feel, string $songPart, int $lengthBeats, array $hits): void
    {
        $pattern = DrumPatternTemplate::create([
            'name' => $name,
            'genre' => $genre,
            'time_signature' => $timeSignature,
            'feel' => $feel,
            'song_part' => $songPart,
            'length_beats' => $lengthBeats,
            'description' => "A {$feel} {$genre} pattern for {$songPart}",
            'complexity' => 2,
        ]);

        foreach ($hits as $hit) {
            foreach ($hit['positions'] as $position) {
                DrumHit::create([
                    'pattern_template_id' => $pattern->id,
                    'instrument_id' => $this->instruments[$hit['instrument']]->id,
                    'position' => $position,
                ]);
            }
        }
    }
}
