<?php

namespace App\Http\Controllers;

use App\Http\Resources\SourceResource;
use App\Source;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    public function index(Request $request)
    {
        $data = Source::orderBy('order', 'ASC')->get();
        return SourceResource::collection($data);
    }
}
