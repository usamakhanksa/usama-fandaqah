<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UploadedMedia;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'image', 'max:3072'],
        ]);

        $stored = $data['file']->store('uploads', 'public');

        $media = UploadedMedia::create([
            'path' => '/storage/'.$stored,
            'name' => $data['file']->getClientOriginalName(),
            'mime_type' => $data['file']->getMimeType(),
        ]);

        return response()->json($media, 201);
    }

    public function destroy(UploadedMedia $uploadedMedia)
    {
        $uploadedMedia->delete();

        return response()->json(['message' => 'deleted']);
    }
}
