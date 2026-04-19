<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ShortUrl;
use Illuminate\Support\Str;

class ShortUrlController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'destination_url' => 'required|url',
        ]);

        $code = Str::random(6);

        ShortUrl::create([
            'code' => $code,
            'destination_url' => $request->destination_url,
        ]);

        return response()->json(['short_url' => url("/s/{$code}")]);
    }

    public function redirect($code)
    {
        $shortUrl = ShortUrl::where('code', $code)->firstOrFail();
        return redirect($shortUrl->destination_url . '&co='. $code);
    }
}
