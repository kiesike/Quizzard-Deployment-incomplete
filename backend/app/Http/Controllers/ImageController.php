<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|string', // base64 string
            'type'  => 'nullable|string', // question or answer
        ]);

        try {
            $base64 = $request->image;

            // Strip the data URI prefix if present
            if (str_contains($base64, ',')) {
                $base64 = explode(',', $base64)[1];
            }

            // Decode base64
            $imageData = base64_decode($base64);

            if (!$imageData) {
                return response()->json([
                    'message' => 'Invalid image data.',
                ], 422);
            }

            // Generate unique filename
            $filename = 'quiz_images/' . Str::uuid() . '.jpg';

            // Store in public storage
            Storage::disk('public')->put($filename, $imageData);

            // Return the public URL
            $url = asset('storage/' . $filename);
            
            return response()->json([
                'message'   => 'Image uploaded successfully.',
                'image_url' => $url,
                'image_path' => $filename,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload image: ' . $e->getMessage(),
            ], 500);
        }
    }
}   