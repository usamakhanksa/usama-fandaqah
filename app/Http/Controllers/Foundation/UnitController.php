<?php
namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Foundation\StoreUnitRequest;
use App\Http\Requests\Foundation\UpdateUnitRequest;
use App\Models\Foundation\Unit;
use App\Models\Foundation\UnitCategory;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function index(){ return Inertia::render('Foundation/Units/Index',['rows'=>Unit::with('category')->orderBy('number')->paginate(10),'categories'=>UnitCategory::select('id','name_en')->orderBy('name_en')->get()]); }
    public function create(){ return Inertia::render('Foundation/Units/Create',['categories'=>UnitCategory::select('id','name_en')->get()]); }
    public function store(StoreUnitRequest $request){ Unit::create($request->validated()); return to_route('foundation.units.index')->with('success','Unit created.'); }
    public function show(Unit $unit){ return Inertia::render('Foundation/Units/Show',['row'=>$unit->load('category')]); }
    public function edit(Unit $unit){ return Inertia::render('Foundation/Units/Edit',['row'=>$unit,'categories'=>UnitCategory::select('id','name_en')->get()]); }
    public function update(UpdateUnitRequest $request, Unit $unit){ $unit->update($request->validated()); return to_route('foundation.units.index')->with('success','Unit updated.'); }
    public function destroy(Unit $unit){ $unit->delete(); return to_route('foundation.units.index')->with('success','Unit deleted.'); }
}
