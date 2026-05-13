<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Source;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SourceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('sources.view');
        $sources = Source::where('team_id', $request->user()->currentTeam->id)->get();
        return Inertia::render('Sources/Index', ['sources' => $sources]);
    }

    public function create()
    {
        $this->authorize('sources.create');
        return Inertia::render('Sources/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('sources.create');
        $validated = $request->validate(['name' => 'required|string|max:255', 'type' => 'nullable|string']);
        $source = Source::create([...$validated, 'team_id' => $request->user()->currentTeam->id]);
        return redirect()->route('sources.show', $source);
    }

    public function show(Source $source)
    {
        $this->authorize('sources.view', $source);
        return Inertia::render('Sources/Show', ['source' => $source]);
    }

    public function edit(Source $source)
    {
        $this->authorize('sources.update', $source);
        return Inertia::render('Sources/Edit', ['source' => $source]);
    }

    public function update(Request $request, Source $source)
    {
        $this->authorize('sources.update', $source);
        $validated = $request->validate(['name' => 'required|string|max:255', 'type' => 'nullable|string']);
        $source->update($validated);
        return redirect()->route('sources.show', $source);
    }

    public function destroy(Source $source)
    {
        $this->authorize('sources.delete', $source);
        $source->delete();
        return redirect()->route('sources.index');
    }
}
