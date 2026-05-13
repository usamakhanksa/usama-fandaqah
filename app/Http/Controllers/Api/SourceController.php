<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SourceResource;
use App\Source;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SourceController extends Controller
{
    public function index(Request $request)
    {
        $teamId = auth()->user()->current_team_id;
        $query = Source::where('team_id', $teamId);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('is_travel_agent')) {
            $query->where('is_travel_agent', $request->boolean('is_travel_agent'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->orderBy('order')->paginate($request->get('per_page', 30));
        return SourceResource::collection($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'is_travel_agent' => 'boolean',
            'iata_number' => 'nullable|string',
            'commission_rate' => 'nullable|numeric',
            'commission_type' => 'nullable|string|in:percentage,fixed',
            'status' => 'boolean',
            'order' => 'integer',
        ]);

        $source = Source::create(array_merge($validated, [
            'team_id' => auth()->user()->current_team_id
        ]));

        return new SourceResource($source);
    }

    public function update(Request $request, Source $source)
    {
        $validated = $request->validate([
            'name' => 'nullable|array',
            'is_travel_agent' => 'boolean',
            'iata_number' => 'nullable|string',
            'commission_rate' => 'nullable|numeric',
            'commission_type' => 'nullable|string|in:percentage,fixed',
            'status' => 'boolean',
            'order' => 'integer',
        ]);

        $source->update($validated);

        return new SourceResource($source);
    }

    public function destroy(Source $source)
    {
        $source->delete();
        return response()->json(['message' => 'Source deleted successfully']);
    }
}