<?php

namespace App\Http\Controllers;

use App\Models\DrumPattern;
use App\Models\DrumPatternTemplate;
use App\Services\MidiGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class DrumPatternController extends Controller
{
    private $midiService;

    public function __construct(MidiGenerationService $midiService)
    {
        $this->midiService = $midiService;
    }

    /**
     * Generate random parameters for pattern generation
     *
     * @return array
     */
    private function generateRandomParameters(): array
    {
        // Get available values from database
        $template = DrumPatternTemplate::inRandomOrder()->first();

        if (!$template) {
            // Fallback values if no templates exist
            return [
                'genre' => 'rock',
                'length_bars' => rand(4, 16),
                'feel' => 'normal_time',
                'tempo' => rand(80, 160),
                'song_part' => 'verse',
                'time_signature' => '4/4',
            ];
        }

        // Get unique values from database for each parameter
        $genres = DrumPatternTemplate::distinct()->pluck('genre')->toArray();
        $feels = DrumPatternTemplate::distinct()->pluck('feel')->toArray();
        $songParts = DrumPatternTemplate::distinct()->pluck('song_part')->toArray();
        $timeSignatures = DrumPatternTemplate::distinct()->pluck('time_signature')->toArray();

        // Generate random parameters
        return [
            'genre' => $genres[array_rand($genres)],
            'length_bars' => rand(4, 16),
            'feel' => $feels[array_rand($feels)],
            'tempo' => rand(80, 160),
            'song_part' => $songParts[array_rand($songParts)],
            'time_signature' => $timeSignatures[array_rand($timeSignatures)],
        ];
    }

    /**
     * Generate a new MIDI drum pattern
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        // If no parameters provided, generate random ones
        if ($request->query->count() === 0) {
            $parameters = $this->generateRandomParameters();
        } else {
            // Validate the request
            $validated = $request->validate([
                'genre' => 'nullable|string|max:50',
                'length_bars' => 'nullable|integer|min:1|max:32',
                'feel' => ['nullable', 'string', Rule::in(DrumPattern::$validFeels)],
                'tempo' => 'nullable|integer|min:40|max:300',
                'song_part' => ['nullable', 'string', Rule::in(DrumPattern::$validSongParts)],
                'time_signature' => ['nullable', 'string', Rule::in(DrumPattern::$validTimeSignatures)],
                'additional_parameters' => 'nullable|array',
            ]);

            // Set default values
            $parameters = [
                'genre' => $validated['genre'] ?? 'rock',
                'length_bars' => $validated['length_bars'] ?? 8,
                'feel' => $validated['feel'] ?? 'normal_time',
                'tempo' => $validated['tempo'] ?? 120,
                'song_part' => $validated['song_part'] ?? 'verse',
                'time_signature' => $validated['time_signature'] ?? '4/4',
                'additional_parameters' => $validated['additional_parameters'] ?? null,
            ];
        }

        // Generate a unique filename
        $filename = DrumPattern::generateFilename($parameters);
        $midiFilename = $filename . '.mid';
        
        // Generate the MIDI file using the service
        $midiData = $this->midiService->generateMidiFile($parameters);
        
        // Store the file
        $filePath = 'drum-patterns/' . $midiFilename;
        Storage::put($filePath, $midiData);

        // Create database record
        $pattern = DrumPattern::create([
            'filename' => $filename,
            'genre' => $parameters['genre'],
            'length_bars' => $parameters['length_bars'],
            'feel' => $parameters['feel'],
            'tempo' => $parameters['tempo'],
            'song_part' => $parameters['song_part'],
            'time_signature' => $parameters['time_signature'],
            'additional_parameters' => $parameters['additional_parameters'] ?? null,
            'file_path' => $filePath,
        ]);

        return response()->json([
            'message' => 'Drum pattern generated successfully',
            'parameters' => $parameters,
            'pattern' => $pattern,
            'download_url' => route('api.drum-patterns.download', $pattern->id),
        ]);
    }

    /**
     * Generate and directly download a MIDI drum pattern
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function generateAndDownload(Request $request)
    {
        // If no parameters provided, generate random ones
        if ($request->query->count() === 0) {
            $parameters = $this->generateRandomParameters();
        } else {
            // Validate the request
            $validated = $request->validate([
                'genre' => 'nullable|string|max:50',
                'length_bars' => 'nullable|integer|min:1|max:32',
                'feel' => ['nullable', 'string', Rule::in(DrumPattern::$validFeels)],
                'tempo' => 'nullable|integer|min:40|max:300',
                'song_part' => ['nullable', 'string', Rule::in(DrumPattern::$validSongParts)],
                'time_signature' => ['nullable', 'string', Rule::in(DrumPattern::$validTimeSignatures)],
                'additional_parameters' => 'nullable|array',
            ]);

            // Set default values
            $parameters = [
                'genre' => $validated['genre'] ?? 'rock',
                'length_bars' => $validated['length_bars'] ?? 8,
                'feel' => $validated['feel'] ?? 'normal_time',
                'tempo' => $validated['tempo'] ?? 120,
                'song_part' => $validated['song_part'] ?? 'verse',
                'time_signature' => $validated['time_signature'] ?? '4/4',
                'additional_parameters' => $validated['additional_parameters'] ?? null,
            ];
        }

        // Generate a unique filename
        $filename = DrumPattern::generateFilename($parameters);
        $midiFilename = $filename . '.mid';
        
        // Generate the MIDI file using the service
        $midiData = $this->midiService->generateMidiFile($parameters);
        
        // Store the file
        $filePath = 'drum-patterns/' . $midiFilename;
        Storage::put($filePath, $midiData);

        // Create database record
        DrumPattern::create([
            'filename' => $filename,
            'genre' => $parameters['genre'],
            'length_bars' => $parameters['length_bars'],
            'feel' => $parameters['feel'],
            'tempo' => $parameters['tempo'],
            'song_part' => $parameters['song_part'],
            'time_signature' => $parameters['time_signature'],
            'additional_parameters' => $parameters['additional_parameters'] ?? null,
            'file_path' => $filePath,
        ]);
        
        // Return the file directly as a download
        return response($midiData)
            ->header('Content-Type', 'audio/midi')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.mid"');
    }

    /**
     * Download a drum pattern
     *
     * @param DrumPattern $pattern
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(DrumPattern $pattern)
    {
        $pattern->incrementDownloadCount();
        
        return Storage::download(
            $pattern->file_path,
            $pattern->filename . '.mid',
            ['Content-Type' => 'audio/midi']
        );
    }
}
