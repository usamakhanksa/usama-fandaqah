<?php
namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Foundation\StoreUnitCategoryRequest;
use App\Http\Requests\Foundation\UpdateUnitCategoryRequest;
use App\Models\Foundation\UnitCategory;
use Inertia\Inertia;

class UnitCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Foundation/UnitCategories/Index', [
            'rows' => UnitCategory::query()->orderBy('name_en')->paginate(10)->withQueryString(),
        ]);
    }
    public function create(){ return Inertia::render('Foundation/UnitCategories/Create'); }
    public function store(StoreUnitCategoryRequest $request){ UnitCategory::create($request->validated()); return to_route('foundation.unit-categories.index')->with('success','Category created.'); }
    public function show(UnitCategory $unit_category){ return Inertia::render('Foundation/UnitCategories/Show',['row'=>$unit_category]); }
    public function edit(UnitCategory $unit_category){ return Inertia::render('Foundation/UnitCategories/Edit',['row'=>$unit_category]); }
    public function update(UpdateUnitCategoryRequest $request, UnitCategory $unit_category){ $unit_category->update($request->validated()); return to_route('foundation.unit-categories.index')->with('success','Category updated.'); }
    public function destroy(UnitCategory $unit_category){ $unit_category->delete(); return to_route('foundation.unit-categories.index')->with('success','Category deleted.'); }
}
