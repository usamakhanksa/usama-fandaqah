<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceCategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('service-categories.view');
        $categories = ServiceCategory::where('team_id', $request->user()->currentTeam->id)->get();
        return Inertia::render('ServiceCategories/Index', ['categories' => $categories]);
    }

    public function create()
    {
        $this->authorize('service-categories.create');
        return Inertia::render('ServiceCategories/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('service-categories.create');
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $category = ServiceCategory::create([...$validated, 'team_id' => $request->user()->currentTeam->id]);
        return redirect()->route('service-categories.show', $category);
    }

    public function show(ServiceCategory $serviceCategory)
    {
        $this->authorize('service-categories.view', $serviceCategory);
        return Inertia::render('ServiceCategories/Show', ['category' => $serviceCategory]);
    }

    public function edit(ServiceCategory $serviceCategory)
    {
        $this->authorize('service-categories.update', $serviceCategory);
        return Inertia::render('ServiceCategories/Edit', ['category' => $serviceCategory]);
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $this->authorize('service-categories.update', $serviceCategory);
        $validated = $request->validate(['name' => 'required|string|max:255']);
        $serviceCategory->update($validated);
        return redirect()->route('service-categories.show', $serviceCategory);
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        $this->authorize('service-categories.delete', $serviceCategory);
        $serviceCategory->delete();
        return redirect()->route('service-categories.index');
    }
}
