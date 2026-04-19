<?php

namespace App\Http\Controllers;

use App\Highlight;
use App\Http\Resources\HighlightResource;
use Illuminate\Http\Request;

class HighlightController extends Controller
{
    public function index(Request $request)
    {
        $data = Highlight::orderBy('order', 'ASC')->get();
        return HighlightResource::collection($data);
    }
}
