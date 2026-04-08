<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AudioController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimetypes:audio/mpeg|max:20480', // mp3 only, max 20MB
        ]);

        try {
            $file = $request->file('audio');

            // Double check the extension as extra safety
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension !== 'mp3') {
                return response()->json([
                    'message' => 'Only .mp3 audio files are supported.',
                ], 422);
            }

            // Generate unique filename
            $filename = 'quiz_audios/' . Str::uuid() . '.mp3';

            // Store in public storage
            Storage::disk('public')->put($filename, file_get_contents($file));

            // Return the public URL
            $url = asset('storage/' . $filename);

            return response()->json([
                'message'    => 'Audio uploaded successfully.',
                'audio_url'  => $url,
                'audio_path' => $filename,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload audio: ' . $e->getMessage(),
            ], 500);
        }
    }
}