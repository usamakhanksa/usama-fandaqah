<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DashboardNotice;
use App\Http\Requests\StoreDashboardNoticeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardNoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return DashboardNotice::query()
            ->when($request->search, fn($q, $s) => $q->search($s))
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->latest()
            ->paginate($request->per_page ?? 15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDashboardNoticeRequest $request)
    {
        $this->authorize('create', DashboardNotice::class);
        
        $notice = DashboardNotice::create($request->validated());
        
        return response()->json([
            'message' => 'Notice created successfully.',
            'data' => $notice
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DashboardNotice $dashboardNotice)
    {
        return $dashboardNotice;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreDashboardNoticeRequest $request, DashboardNotice $dashboardNotice)
    {
        $this->authorize('update', $dashboardNotice);
        
        $dashboardNotice->update($request->validated());
        
        return response()->json([
            'message' => 'Notice updated successfully.',
            'data' => $dashboardNotice
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DashboardNotice $dashboardNotice)
    {
        $this->authorize('delete', $dashboardNotice);
        
        $dashboardNotice->delete();
        
        return response()->json([
            'message' => 'Notice deleted successfully.'
        ]);
    }

    /**
     * Internal method to authorize actions
     */
    protected function authorize($ability, $arguments = [])
    {
        if (Gate::denies($ability, $arguments)) {
            abort(403, 'Unauthorized action.');
        }
    }
}
