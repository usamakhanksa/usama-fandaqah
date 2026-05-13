<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceLog;
use App\Models\QuickPayment;
use App\Models\ServiceQoyod;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PosServicesController extends Controller
{
    private function teamId(Request $request): int
    {
        return $request->user()->currentTeam->id;
    }

    // ── 6.1 POS Dashboard ────────────────────────────────────────

    public function dashboard(Request $request): JsonResponse
    {
        $teamId = $this->teamId($request);
        $today  = now()->toDateString();

        $todaySales = DB::table('service_logs')
            ->where('team_id', $teamId)
            ->whereDate('created_at', $today)
            ->whereNull('deleted_at')
            ->sum('amount');

        $recentLogs = ServiceLog::where('team_id', $teamId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'number', 'amount', 'type', 'created_at']);

        return response()->json([
            'data' => [
                'today_sales'  => $todaySales / 100,
                'recent_sales' => $recentLogs,
            ]
        ]);
    }

    // ── 6.2 Service Categories ───────────────────────────────────

    public function categoriesIndex(Request $request): JsonResponse
    {
        $query = ServiceCategory::where('team_id', $this->teamId($request))->orderBy('order');
        if ($request->filled('search')) $query->where('name', 'like', '%'.$request->search.'%');

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function categoriesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'status'  => 'boolean',
            'order'   => 'nullable|integer',
            'rev_type'=> 'nullable|string|max:30',
            'show_in_reservation' => 'boolean',
            'show_in_pos'         => 'boolean',
        ]);

        $category = ServiceCategory::create([
            'team_id'             => $this->teamId($request),
            'name'                => json_encode(['en' => $validated['name'], 'ar' => $validated['name_ar'] ?? $validated['name']]),
            'status'              => $validated['status'] ?? 1,
            'order'               => $validated['order'] ?? 0,
            'rev_type'            => $validated['rev_type'] ?? null,
            'show_in_reservation' => $validated['show_in_reservation'] ?? true,
            'show_in_pos'         => $validated['show_in_pos'] ?? true,
        ]);

        return response()->json(['message' => 'Category created', 'data' => $category], 201);
    }

    public function categoriesUpdate(Request $request, $id): JsonResponse
    {
        $category = ServiceCategory::where('team_id', $this->teamId($request))->findOrFail($id);
        $data = $request->only(['status', 'order', 'rev_type', 'show_in_reservation', 'show_in_pos']);
        if ($request->filled('name')) {
            $data['name'] = json_encode(['en' => $request->name, 'ar' => $request->input('name_ar', $request->name)]);
        }
        $category->update($data);

        return response()->json(['message' => 'Category updated', 'data' => $category->fresh()]);
    }

    public function categoriesDestroy(Request $request, $id): JsonResponse
    {
        ServiceCategory::where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Category deleted']);
    }

    // ── 6.3 Services ─────────────────────────────────────────────

    public function servicesIndex(Request $request): JsonResponse
    {
        $query = Service::where('team_id', $this->teamId($request))->orderBy('name');

        if ($request->filled('search'))   $query->where(function($q) use ($request) { $q->where('name', 'like', '%'.$request->search.'%')->orWhere('name_en', 'like', '%'.$request->search.'%'); });
        if ($request->filled('category')) $query->where('service_category_id', $request->category);
        if ($request->filled('status'))   $query->where('status', $request->status);

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function servicesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'name_ar'             => 'nullable|string|max:255',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'price'               => 'nullable|numeric|min:0',
            'status'              => 'nullable|string',
            'show_in_reservation' => 'boolean',
            'show_in_pos'         => 'boolean',
        ]);

        $service = Service::create(array_merge($validated, [
            'team_id' => $this->teamId($request),
            'name_en' => $validated['name'],
            'name_ar' => $validated['name_ar'] ?? $validated['name'],
            'is_active' => true,
        ]));

        return response()->json(['message' => 'Service created', 'data' => $service], 201);
    }

    public function servicesShow($id, Request $request): JsonResponse
    {
        $service = Service::where('team_id', $this->teamId($request))->findOrFail($id);

        return response()->json(['data' => $service]);
    }

    public function servicesUpdate(Request $request, $id): JsonResponse
    {
        $service = Service::where('team_id', $this->teamId($request))->findOrFail($id);
        $service->update($request->only(['name','name_ar','service_category_id','price','status','show_in_reservation','show_in_pos','is_active']));

        return response()->json(['message' => 'Service updated', 'data' => $service->fresh()]);
    }

    public function servicesDestroy(Request $request, $id): JsonResponse
    {
        Service::where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Service deleted']);
    }

    // ── 6.4 POS Sale ─────────────────────────────────────────────

    public function saleStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'nullable|exists:reservations,id',
            'items'          => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        $teamId = $this->teamId($request);
        $total  = collect($validated['items'])->sum(fn($i) => $i['quantity'] * $i['price']);

        $log = DB::transaction(function () use ($validated, $teamId, $total, $request) {
            $log = ServiceLog::create([
                'team_id'  => $teamId,
                'user_id'  => $request->user()->id,
                'type'     => 'pos_sale',
                'number'   => 'POS-' . strtoupper(substr(uniqid(), -6)),
                'amount'   => (int) ($total * 100),
                'decimals' => 2,
                'meta'     => ['items' => $validated['items'], 'payment_method' => $validated['payment_method'], 'reservation_id' => $validated['reservation_id'] ?? null],
                'business_date' => now()->toDateString(),
            ]);

            return $log;
        });

        return response()->json(['message' => 'Sale completed', 'data' => $log], 201);
    }

    // ── 6.5 Service Logs ─────────────────────────────────────────

    public function serviceLogsIndex(Request $request): JsonResponse
    {
        $query = ServiceLog::where('team_id', $this->teamId($request))->orderBy('created_at', 'desc');

        if ($request->filled('date'))   $query->whereDate('created_at', $request->date);
        if ($request->filled('type'))   $query->where('type', $request->type);

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function serviceLogsShow($id, Request $request): JsonResponse
    {
        $log = ServiceLog::where('team_id', $this->teamId($request))->findOrFail($id);

        return response()->json(['data' => $log]);
    }

    // ── 6.6 Service Log Notes ────────────────────────────────────

    public function serviceLogNoteStore(Request $request, $logId): JsonResponse
    {
        $validated = $request->validate(['body' => 'required|string']);

        $log = ServiceLog::where('team_id', $this->teamId($request))->findOrFail($logId);

        DB::table('service_logs_notes')->insert([
            'service_log_id' => $log->id,
            'type'           => 'note',
            'payload'        => json_encode(['body' => $validated['body'], 'user_id' => $request->user()->id]),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return response()->json(['message' => 'Note added'], 201);
    }

    // ── 6.7 Quick Payments ───────────────────────────────────────

    public function quickPaymentsIndex(Request $request): JsonResponse
    {
        $query = QuickPayment::where('team_id', $this->teamId($request))->with(['createdBy'])->orderBy('created_at', 'desc');

        if ($request->filled('date'))           $query->whereDate('created_at', $request->date);
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function quickPaymentsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'reference'      => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
        ]);

        $payment = QuickPayment::create(array_merge($validated, [
            'team_id'    => $this->teamId($request),
            'created_by' => $request->user()->id,
        ]));

        return response()->json(['message' => 'Quick payment recorded', 'data' => $payment], 201);
    }

    // ── 6.8 POS Transactions (via service_logs) ──────────────────

    public function posTransactionsIndex(Request $request): JsonResponse
    {
        $query = ServiceLog::where('team_id', $this->teamId($request))->orderBy('created_at', 'desc');

        if ($request->filled('date')) $query->whereDate('created_at', $request->date);
        if ($request->filled('type')) $query->where('type', $request->type);

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function voidTransaction(Request $request, $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string']);

        $log = ServiceLog::where('team_id', $this->teamId($request))->findOrFail($id);
        $log->update(['is_freezed' => true, 'correction_reason' => $request->reason, 'corrected_by' => $request->user()->id]);

        return response()->json(['message' => 'Transaction voided']);
    }

    public function refundTransaction(Request $request, $id): JsonResponse
    {
        $request->validate(['amount' => 'required|numeric|min:0.01', 'reason' => 'required|string']);

        $log = ServiceLog::where('team_id', $this->teamId($request))->findOrFail($id);

        $refund = ServiceLog::create([
            'team_id'          => $log->team_id,
            'user_id'          => $request->user()->id,
            'type'             => 'refund',
            'number'           => 'REF-' . strtoupper(substr(uniqid(), -6)),
            'amount'           => (int) ($request->amount * 100),
            'decimals'         => 2,
            'is_subtraction'   => true,
            'correction_reason'=> $request->reason,
            'business_date'    => now()->toDateString(),
        ]);

        return response()->json(['message' => 'Refund created', 'data' => $refund], 201);
    }

    // ── 6.9 Reservation Services ─────────────────────────────────

    public function reservationServicesIndex($reservationId, Request $request): JsonResponse
    {
        $logs = ServiceLog::where('team_id', $this->teamId($request))
            ->where(function($q) use ($reservationId) {
                $q->whereJsonContains('meta->reservation_id', (int) $reservationId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $logs]);
    }

    public function reservationServicesStore(Request $request, $reservationId): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity'   => 'required|integer|min:1',
            'price'      => 'required|numeric|min:0',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $total   = $validated['quantity'] * $validated['price'];

        $log = ServiceLog::create([
            'team_id'       => $this->teamId($request),
            'user_id'       => $request->user()->id,
            'type'          => 'reservation_service',
            'number'        => 'SVC-' . strtoupper(substr(uniqid(), -6)),
            'amount'        => (int) ($total * 100),
            'decimals'      => 2,
            'meta'          => ['service_id' => $validated['service_id'], 'service_name' => $service->name, 'quantity' => $validated['quantity'], 'price' => $validated['price'], 'reservation_id' => (int) $reservationId],
            'business_date' => now()->toDateString(),
        ]);

        return response()->json(['message' => 'Service added to reservation', 'data' => $log], 201);
    }

    // ── 6.10 Qoyod Service Mapping ───────────────────────────────

    public function qoyodIndex(Request $request): JsonResponse
    {
        $query = ServiceQoyod::where('team_id', $this->teamId($request))->with(['service']);

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function qoyodStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id'     => 'required|exists:services,id',
            'qoyod_account'  => 'nullable|string|max:100',
            'qoyod_product'  => 'nullable|string|max:100',
            'is_active'      => 'boolean',
        ]);

        $mapping = ServiceQoyod::updateOrCreate(
            ['team_id' => $this->teamId($request), 'service_id' => $validated['service_id']],
            array_merge($validated, ['team_id' => $this->teamId($request)])
        );

        return response()->json(['message' => 'Mapping saved', 'data' => $mapping], 201);
    }

    public function qoyodUpdate(Request $request, $id): JsonResponse
    {
        $mapping = ServiceQoyod::where('team_id', $this->teamId($request))->findOrFail($id);
        $mapping->update($request->only(['qoyod_account', 'qoyod_product', 'is_active']));

        return response()->json(['message' => 'Mapping updated', 'data' => $mapping->fresh()]);
    }

    public function qoyodDestroy(Request $request, $id): JsonResponse
    {
        ServiceQoyod::where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Mapping deleted']);
    }
}
