<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    /**
     * Public endpoint — capture a lead from the slider modal form.
     */
    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'full_name'        => 'required|string|min:2|max:120',
            'email'            => 'required|email|max:191',
            'phone'            => 'required|string|max:30',
            'country_code'     => 'nullable|string|max:10',
            'property_type'    => 'nullable|string|max:60',
            'product_interest' => 'nullable|string|max:60',
            'source'           => 'nullable|string|max:60',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $v->errors(),
            ], 422);
        }

        $lead = Lead::create([
            'full_name'        => $request->full_name,
            'email'            => $request->email,
            'phone'            => $request->phone,
            'country_code'     => $request->country_code ?? '+966',
            'property_type'    => $request->property_type,
            'product_interest' => $request->product_interest,
            'source'           => $request->source ?? 'slider_modal',
            'status'           => 'new',
            'priority'         => 'medium',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Lead submitted successfully',
            'data'    => ['id' => $lead->id],
        ], 201);
    }

    /**
     * Admin: paginated list with filters.
     */
    public function index(Request $request)
    {
        $query = Lead::with('assignedUser')
            ->when($request->search, fn($q, $s) =>
                $q->where('full_name', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%")
            )
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->priority, fn($q, $p) => $q->where('priority', $p))
            ->when($request->product, fn($q, $p) => $q->where('product_interest', $p))
            ->latest();

        $leads = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'data'  => $leads->items(),
            'total' => $leads->total(),
            'meta'  => [
                'current_page' => $leads->currentPage(),
                'last_page'    => $leads->lastPage(),
                'per_page'     => $leads->perPage(),
                'total'        => $leads->total(),
            ],
            'stats' => [
                'total'     => Lead::count(),
                'new'       => Lead::where('status', 'new')->count(),
                'contacted' => Lead::where('status', 'contacted')->count(),
                'qualified' => Lead::where('status', 'qualified')->count(),
                'lost'      => Lead::where('status', 'lost')->count(),
            ],
        ]);
    }

    /**
     * Admin: update a lead's status / priority / notes.
     */
    public function update(Request $request, Lead $lead)
    {
        $lead->update($request->only(['status', 'priority', 'notes', 'assigned_to', 'contacted_at']));

        return response()->json(['status' => 'success', 'data' => $lead->fresh('assignedUser')]);
    }

    /**
     * Admin: delete a lead.
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();
        return response()->json(['status' => 'success']);
    }

    /**
     * Admin: summary stats for dashboard widgets.
     */
    public function stats()
    {
        return response()->json([
            'total'     => Lead::count(),
            'new'       => Lead::where('status', 'new')->count(),
            'contacted' => Lead::where('status', 'contacted')->count(),
            'qualified' => Lead::where('status', 'qualified')->count(),
            'lost'      => Lead::where('status', 'lost')->count(),
            'this_week' => Lead::where('created_at', '>=', now()->startOfWeek())->count(),
            'by_product'=> Lead::selectRaw('product_interest, count(*) as count')
                               ->groupBy('product_interest')
                               ->pluck('count', 'product_interest'),
        ]);
    }
}
