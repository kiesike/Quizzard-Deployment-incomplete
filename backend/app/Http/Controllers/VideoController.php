<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimetypes:video/mp4|max:51200', // mp4 only, max 50MB
        ]);

        try {
            $file = $request->file('video');

            // Double check the extension as extra safety
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension !== 'mp4') {
                return response()->json([
                    'message' => 'Only .mp4 video files are supported.',
                ], 422);
            }

            // Generate unique filename
            $filename = 'quiz_videos/' . Str::uuid() . '.mp4';

            // Store in public storage
            Storage::disk('public')->put($filename, file_get_contents($file));

            // Return the public URL
            $url = asset('storage/' . $filename);

            return response()->json([
                'message'    => 'Video uploaded successfully.',
                'video_url'  => $url,
                'video_path' => $filename,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload video: ' . $e->getMessage(),
            ], 500);
        }
    }
}