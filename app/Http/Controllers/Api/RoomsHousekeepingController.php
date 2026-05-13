<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\RoomStatusLog;
use App\Models\HousekeepingTask;
use App\Models\UnitFeature;
use App\Models\UnitOption;
use App\Models\UnitCategoryService;
use App\Models\UploadedMedia;
use App\UnitCategory;
use App\UnitCleaning;
use App\UnitMaintenance;
use App\Services\RoomStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RoomsHousekeepingController extends Controller
{
    public function __construct(protected RoomStatusService $statusService) {}

    private function teamId(Request $request): int
    {
        return $request->user()->currentTeam->id;
    }

    // ── 4.1 Units CRUD ───────────────────────────────────────────

    public function unitsIndex(Request $request): JsonResponse
    {
        $query = Unit::where('team_id', $this->teamId($request))
            ->with(['unitCategory'])
            ->orderBy('unit_number');

        if ($request->filled('search'))   $query->where(function($q) use ($request) { $q->where('unit_number','like','%'.$request->search.'%')->orWhere('name','like','%'.$request->search.'%'); });
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('unit_category_id', $request->category);
        if ($request->filled('floor'))    $query->where('floor', $request->floor);

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function unitsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_number'      => 'required|string|max:20',
            'name'             => 'nullable|string|max:100',
            'unit_category_id' => 'nullable|exists:unit_categories,id',
            'floor'            => 'nullable|string|max:10',
            'capacity'         => 'nullable|integer|min:1',
            'beds'             => 'nullable|integer|min:0',
            'baths'            => 'nullable|integer|min:0',
            'status'           => 'nullable|integer',
            'is_active'        => 'boolean',
        ]);

        $unit = Unit::create(array_merge($validated, ['team_id' => $this->teamId($request)]));

        return response()->json(['message' => 'Unit created', 'data' => $unit], 201);
    }

    public function unitsShow($id, Request $request): JsonResponse
    {
        $unit = Unit::where('team_id', $this->teamId($request))->with(['unitCategory'])->findOrFail($id);

        return response()->json(['data' => $unit]);
    }

    public function unitsUpdate(Request $request, $id): JsonResponse
    {
        $unit = Unit::where('team_id', $this->teamId($request))->findOrFail($id);
        $unit->update($request->only(['unit_number','name','unit_category_id','floor','capacity','beds','baths','status','is_active','enabled']));

        return response()->json(['message' => 'Unit updated', 'data' => $unit->fresh()]);
    }

    public function unitsDestroy(Request $request, $id): JsonResponse
    {
        Unit::where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Unit deleted']);
    }

    public function unitsRestore(Request $request, $id): JsonResponse
    {
        Unit::withTrashed()->where('team_id', $this->teamId($request))->findOrFail($id)->restore();

        return response()->json(['message' => 'Unit restored']);
    }

    // ── 4.2 Unit Categories CRUD ─────────────────────────────────

    public function categoriesIndex(Request $request): JsonResponse
    {
        $query = UnitCategory::withoutGlobalScopes()->where('team_id', $this->teamId($request));

        if ($request->filled('search')) $query->where('name', 'like', '%'.$request->search.'%');
        if ($request->filled('status')) $query->where('status', $request->status);

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function categoriesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'number_of_adults' => 'nullable|integer|min:1',
            'number_of_children'=> 'nullable|integer|min:0',
            'number_of_beds'   => 'nullable|integer|min:0',
            'sunday_day_price' => 'nullable|numeric|min:0',
            'month_price'      => 'nullable|numeric|min:0',
            'hour_price'       => 'nullable|numeric|min:0',
            'status'           => 'nullable|integer',
        ]);

        if (isset($validated['name'])) {
            $validated['name'] = json_encode(['en' => $validated['name']]);
        }
        $category = (new UnitCategory())->forceFill(array_merge($validated, ['team_id' => $this->teamId($request)]));
        $category->save();

        return response()->json(['message' => 'Category created', 'data' => $category], 201);
    }

    public function categoriesShow($id, Request $request): JsonResponse
    {
        $category = UnitCategory::withoutGlobalScopes()->where('team_id', $this->teamId($request))->withCount('allUnits')->findOrFail($id);

        return response()->json(['data' => $category]);
    }

    public function categoriesUpdate(Request $request, $id): JsonResponse
    {
        $category = UnitCategory::withoutGlobalScopes()->where('team_id', $this->teamId($request))->findOrFail($id);
        $data = $request->only(['name','description','number_of_adults','number_of_children','number_of_beds','sunday_day_price','monday_day_price','tuesday_day_price','wednesday_day_price','thursday_day_price','friday_day_price','saturday_day_price','month_price','hour_price','status']);
        $category->forceFill($data)->save();

        return response()->json(['message' => 'Category updated', 'data' => $category->fresh()]);
    }

    public function categoriesDestroy(Request $request, $id): JsonResponse
    {
        UnitCategory::withoutGlobalScopes()->where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Category deleted']);
    }

    // ── 4.3 Availability Board ───────────────────────────────────

    public function availabilityBoard(Request $request): JsonResponse
    {
        $teamId = $this->teamId($request);
        $start  = $request->input('start', now()->toDateString());
        $end    = $request->input('end', now()->addDays(13)->toDateString());

        $units = Unit::where('team_id', $teamId)
            ->where('is_active', true)
            ->with(['unitCategory'])
            ->when($request->filled('category'), fn($q) => $q->where('unit_category_id', $request->category))
            ->when($request->filled('floor'), fn($q) => $q->where('floor', $request->floor))
            ->get(['id','unit_number','name','floor','status','unit_category_id']);

        $reservations = DB::table('reservations')
            ->where('team_id', $teamId)
            ->whereNotIn('status', ['cancelled','no_show'])
            ->whereDate('check_in', '<=', $end)
            ->whereDate('check_out', '>=', $start)
            ->whereIn('unit_id', $units->pluck('id'))
            ->get(['id','unit_id','check_in','check_out','status','code']);

        return response()->json([
            'data' => [
                'units'        => $units,
                'reservations' => $reservations,
                'start'        => $start,
                'end'          => $end,
            ]
        ]);
    }

    // ── 4.4 Room Status Board ────────────────────────────────────

    public function statusBoard(Request $request): JsonResponse
    {
        $query = Unit::where('team_id', $this->teamId($request))
            ->where('is_active', true)
            ->with(['unitCategory']);

        if ($request->filled('floor'))    $query->where('floor', $request->floor);
        if ($request->filled('category')) $query->where('unit_category_id', $request->category);

        return response()->json(['data' => $query->get(['id','unit_number','name','floor','status','unit_category_id'])]);
    }

    public function updateUnitStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        $unit = Unit::where('team_id', $this->teamId($request))->findOrFail($id);
        $oldStatus = $unit->status;
        $unit->update(['status' => $validated['status']]);

        $this->statusService->logStatusChange($unit, $validated['status'], $validated['reason'] ?? 'Manual change');

        return response()->json(['message' => 'Status updated', 'data' => $unit->fresh()]);
    }

    // ── 4.5 Housekeeping Board ───────────────────────────────────

    public function housekeepingBoard(Request $request): JsonResponse
    {
        $teamId = $this->teamId($request);

        $query = HousekeepingTask::where('team_id', $teamId)
            ->with(['unit']);

        if ($request->filled('floor'))    $query->whereHas('unit', fn($q) => $q->where('floor', $request->floor));
        if ($request->filled('category')) $query->whereHas('unit', fn($q) => $q->where('unit_category_id', $request->category));

        $tasks = $query->get();

        return response()->json([
            'data' => [
                'pending'     => $tasks->where('status', 'pending')->values(),
                'in_progress' => $tasks->where('status', 'in_progress')->values(),
                'inspection'  => $tasks->where('status', 'inspection')->values(),
                'completed'   => $tasks->where('status', 'completed')->values(),
            ]
        ]);
    }

    public function updateHousekeepingTask(Request $request, $id): JsonResponse
    {
        $task = HousekeepingTask::where('team_id', $this->teamId($request))->findOrFail($id);
        $task->update($request->only(['status', 'task_type']));

        return response()->json(['message' => 'Task updated', 'data' => $task->fresh()]);
    }

    // ── 4.6 Unit Cleanings ───────────────────────────────────────

    public function cleaningsIndex(Request $request): JsonResponse
    {
        $teamId = $this->teamId($request);
        $query  = UnitCleaning::where('team_id', $teamId)->with(['unit', 'creator', 'completedBy'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $request->status === 'completed'
                ? $query->whereNotNull('completed_at')
                : $query->whereNull('completed_at');
        }
        if ($request->filled('date')) $query->whereDate('created_at', $request->date);

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function startCleaning(Request $request, $id): JsonResponse
    {
        $cleaning = UnitCleaning::where('team_id', $this->teamId($request))->findOrFail($id);
        $cleaning->update(['start_at' => now()]);

        return response()->json(['message' => 'Cleaning started', 'data' => $cleaning->fresh()]);
    }

    public function completeCleaning(Request $request, $id): JsonResponse
    {
        $validated = $request->validate(['note' => 'nullable|string']);

        $cleaning = UnitCleaning::where('team_id', $this->teamId($request))->findOrFail($id);
        $cleaning->update([
            'completed_at' => now(),
            'completed_by' => $request->user()->id,
            'note'         => $validated['note'] ?? null,
        ]);

        $unit = Unit::withoutGlobalScopes()->find($cleaning->unit_id);
        if ($unit) {
            $this->statusService->logStatusChange($unit, 'available', 'Cleaning completed', $cleaning);
            $unit->save();
        }

        return response()->json(['message' => 'Cleaning completed', 'data' => $cleaning->fresh()]);
    }

    // ── 4.7 Maintenance Requests ─────────────────────────────────

    public function maintenancesIndex(Request $request): JsonResponse
    {
        $teamId = $this->teamId($request);
        $query  = UnitMaintenance::where('team_id', $teamId)->with(['unit', 'creator', 'completedBy'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $request->status === 'completed'
                ? $query->whereNotNull('completed_at')
                : $query->whereNull('completed_at');
        }
        if ($request->filled('date')) $query->whereDate('created_at', $request->date);

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function maintenancesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_id'     => 'required|exists:units,id',
            'note'        => 'nullable|string',
            'expected_at' => 'nullable|date',
            'action_id'   => 'nullable|integer',
        ]);

        $maintenance = UnitMaintenance::create(array_merge($validated, [
            'team_id'    => $this->teamId($request),
            'created_by' => $request->user()->id,
            'start_at'   => now(),
        ]));

        return response()->json(['message' => 'Maintenance request created', 'data' => $maintenance], 201);
    }

    public function assignMaintenance(Request $request, $id): JsonResponse
    {
        $validated = $request->validate(['assigned_to' => 'required|exists:users,id']);
        $maintenance = UnitMaintenance::where('team_id', $this->teamId($request))->findOrFail($id);
        $maintenance->update(['completed_by' => $validated['assigned_to']]);

        return response()->json(['message' => 'Assigned', 'data' => $maintenance->fresh()]);
    }

    public function completeMaintenance(Request $request, $id): JsonResponse
    {
        $validated = $request->validate(['note' => 'nullable|string']);

        $maintenance = UnitMaintenance::where('team_id', $this->teamId($request))->findOrFail($id);
        $maintenance->update([
            'completed_at' => now(),
            'completed_by' => $request->user()->id,
            'note'         => $validated['note'] ?? null,
        ]);

        $unit = Unit::withoutGlobalScopes()->find($maintenance->unit_id);
        if ($unit) {
            $this->statusService->logStatusChange($unit, 'available', 'Maintenance completed', $maintenance);
            $unit->save();
        }

        return response()->json(['message' => 'Maintenance completed', 'data' => $maintenance->fresh()]);
    }

    // ── 4.8 Room Status Log ──────────────────────────────────────

    public function statusLog(Request $request): JsonResponse
    {
        $query = RoomStatusLog::where('team_id', $this->teamId($request))
            ->with(['unit', 'user'])
            ->orderBy('changed_at', 'desc');

        if ($request->filled('unit_id'))   $query->where('unit_id', $request->unit_id);
        if ($request->filled('date_from')) $query->whereDate('changed_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('changed_at', '<=', $request->date_to);
        if ($request->filled('reason'))    $query->where('change_reason', 'like', '%'.$request->reason.'%');

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function statusLogTimeline($unitId, Request $request): JsonResponse
    {
        $logs = RoomStatusLog::where('team_id', $this->teamId($request))
            ->where('unit_id', $unitId)
            ->with(['user'])
            ->orderBy('changed_at', 'desc')
            ->get();

        return response()->json(['data' => $logs]);
    }

    // ── 4.9 Unit Media ───────────────────────────────────────────

    public function mediaIndex($unitId, Request $request): JsonResponse
    {
        $unit  = Unit::where('team_id', $this->teamId($request))->findOrFail($unitId);
        $media = UploadedMedia::where('owner_id', $unit->id)->where('owner_type', 'App\\Models\\Unit')->get();

        return response()->json(['data' => $media]);
    }

    public function mediaStore(Request $request, $unitId): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:jpg,jpeg,png,webp,mp4|max:20480']);

        $unit = Unit::where('team_id', $this->teamId($request))->findOrFail($unitId);
        $file = $request->file('file');
        $path = $file->store("units/{$unit->id}", 'public');

        $media = UploadedMedia::create([
            'team_id'    => $unit->team_id,
            'path'       => $path,
            'name'       => $file->getClientOriginalName(),
            'mime_type'  => $file->getMimeType(),
            'owner_id'   => $unit->id,
            'owner_type' => 'App\\Models\\Unit',
        ]);

        return response()->json(['message' => 'Media uploaded', 'data' => $media], 201);
    }

    public function mediaDestroy(Request $request, $unitId, $mediaId): JsonResponse
    {
        $unit  = Unit::where('team_id', $this->teamId($request))->findOrFail($unitId);
        $media = UploadedMedia::where('owner_id', $unit->id)->findOrFail($mediaId);
        $media->delete();

        return response()->json(['message' => 'Media deleted']);
    }

    // ── 4.10 Unit Features ───────────────────────────────────────

    public function featuresIndex(Request $request): JsonResponse
    {
        $query = UnitFeature::where('team_id', $this->teamId($request));
        if ($request->filled('search')) $query->where('name', 'like', '%'.$request->search.'%');

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function featuresStore(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:100', 'icon' => 'nullable|string', 'description' => 'nullable|string', 'active' => 'boolean']);
        $validated['name'] = json_encode(['en' => $validated['name']]);
        $feature = UnitFeature::create(array_merge($validated, ['team_id' => $this->teamId($request)]));

        return response()->json(['message' => 'Feature created', 'data' => $feature], 201);
    }

    public function featuresUpdate(Request $request, $id): JsonResponse
    {
        $feature = UnitFeature::where('team_id', $this->teamId($request))->findOrFail($id);
        $data = $request->only(['name', 'icon', 'description', 'active']);
        if (isset($data['name'])) $data['name'] = json_encode(['en' => $data['name']]);
        $feature->update($data);

        return response()->json(['message' => 'Feature updated', 'data' => $feature->fresh()]);
    }

    public function featuresDestroy(Request $request, $id): JsonResponse
    {
        UnitFeature::where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Feature deleted']);
    }

    // ── 4.11 Unit Options ────────────────────────────────────────

    public function optionsIndex(Request $request): JsonResponse
    {
        $query = UnitOption::where('team_id', $this->teamId($request));
        if ($request->filled('search')) $query->where('name', 'like', '%'.$request->search.'%');

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function optionsStore(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:100', 'price' => 'nullable|numeric|min:0', 'description' => 'nullable|string', 'active' => 'boolean']);
        $validated['name'] = json_encode(['en' => $validated['name']]);
        $option = UnitOption::create(array_merge($validated, ['team_id' => $this->teamId($request)]));

        return response()->json(['message' => 'Option created', 'data' => $option], 201);
    }

    public function optionsUpdate(Request $request, $id): JsonResponse
    {
        $option = UnitOption::where('team_id', $this->teamId($request))->findOrFail($id);
        $data = $request->only(['name', 'price', 'description', 'active']);
        if (isset($data['name'])) $data['name'] = json_encode(['en' => $data['name']]);
        $option->update($data);

        return response()->json(['message' => 'Option updated', 'data' => $option->fresh()]);
    }

    public function optionsDestroy(Request $request, $id): JsonResponse
    {
        UnitOption::where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Option deleted']);
    }

    // ── 4.12 Unit Category Services ──────────────────────────────

    public function categoryServicesIndex(Request $request): JsonResponse
    {
        $query = UnitCategoryService::where('team_id', $this->teamId($request))
            ->with(['unitCategory', 'service']);

        if ($request->filled('category_id')) $query->where('unit_category_id', $request->category_id);

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function categoryServicesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_category_id' => 'required|exists:unit_categories,id',
            'service_id'       => 'required|exists:services,id',
            'is_included'      => 'boolean',
            'price_override'   => 'nullable|numeric|min:0',
            'is_active'        => 'boolean',
        ]);

        $mapping = UnitCategoryService::updateOrCreate(
            ['unit_category_id' => $validated['unit_category_id'], 'service_id' => $validated['service_id']],
            array_merge($validated, ['team_id' => $this->teamId($request)])
        );

        return response()->json(['message' => 'Mapping saved', 'data' => $mapping], 201);
    }

    public function categoryServicesDestroy(Request $request, $id): JsonResponse
    {
        UnitCategoryService::where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Mapping deleted']);
    }
}
