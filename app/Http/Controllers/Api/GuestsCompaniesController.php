<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Customer;
use App\CompanyNote;
use App\Models\Highlight;
use App\Models\CustomerNote;
use App\Models\BlockedGuest;
use App\Models\TurnawayReason;
use App\Models\TurnawayLog;
use App\Models\Comment;
use App\Models\MergeLog;
use App\Models\CompanyGroup;
use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class GuestsCompaniesController extends Controller
{
    private function teamId(Request $request): int
    {
        return $request->user()->currentTeam->id;
    }

    // ── 5.1 Guest Directory ──────────────────────────────────────

    public function guestsIndex(Request $request): JsonResponse
    {
        $query = DB::table('guests')
            ->where('team_id', $this->teamId($request))
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('nationality')) $query->where('nationality', $request->nationality);

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function guestsShow($id, Request $request): JsonResponse
    {
        $guest = DB::table('guests')->where('id', $id)->where('team_id', $this->teamId($request))->first();
        abort_if(!$guest, 404);

        return response()->json(['data' => $guest]);
    }

    // ── 5.2 Customer Profiles ────────────────────────────────────

    public function customersIndex(Request $request): JsonResponse
    {
        $query = Customer::where('team_id', $this->teamId($request))->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%')
                  ->orWhere('id_number', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('nationality')) $query->where('country_id', $request->nationality);

        $paginated = $query->paginate($request->input('per_page', 20));
        $paginated->getCollection()->each->setAppends([]);

        return response()->json($paginated);
    }

    public function customersStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'id_number'     => 'nullable|string|max:50',
            'id_type'       => 'nullable|integer',
            'customer_type' => 'nullable|integer',
            'country_id'    => 'nullable|exists:countries,id',
            'gender'        => 'nullable|in:male,female',
            'address'       => 'nullable|string',
        ]);

        $customer = Customer::create(array_merge($validated, ['team_id' => $this->teamId($request)]));
        $customer->setAppends([]);

        return response()->json(['message' => 'Customer created', 'data' => $customer], 201);
    }

    public function customersShow($id, Request $request): JsonResponse
    {
        $customer = Customer::where('team_id', $this->teamId($request))->findOrFail($id);
        $customer->setAppends([]);

        return response()->json(['data' => $customer]);
    }

    public function customersUpdate(Request $request, $id): JsonResponse
    {
        $customer = Customer::where('team_id', $this->teamId($request))->findOrFail($id);
        $customer->update($request->only(['name','phone','email','id_number','id_type','customer_type','country_id','gender','address','highlight_id']));
        $customer->setAppends([]);

        return response()->json(['message' => 'Customer updated', 'data' => $customer->fresh()->setAppends([])]);
    }

    public function customersDestroy(Request $request, $id): JsonResponse
    {
        Customer::where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Customer deleted']);
    }

    public function customersRestore(Request $request, $id): JsonResponse
    {
        Customer::withTrashed()->where('team_id', $this->teamId($request))->findOrFail($id)->restore();

        return response()->json(['message' => 'Customer restored']);
    }

    // ── 5.3 Companies ────────────────────────────────────────────

    public function companiesIndex(Request $request): JsonResponse
    {
        $query = DB::table('companies')
            ->where('team_id', $this->teamId($request))
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('tax_number', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('group')) $query->where('company_group_id', $request->group);

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function companiesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'tax_number'         => 'nullable|string|max:50',
            'email'              => 'nullable|email',
            'phone'              => 'nullable|string|max:20',
            'address'            => 'nullable|string',
            'company_group_id'   => 'nullable|exists:company_groups,id',
            'credit_limit'       => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
        ]);

        $id = DB::table('companies')->insertGetId(array_merge($validated, [
            'team_id'    => $this->teamId($request),
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return response()->json(['message' => 'Company created', 'data' => ['id' => $id]], 201);
    }

    public function companiesShow($id, Request $request): JsonResponse
    {
        $company = DB::table('companies')->where('id', $id)->where('team_id', $this->teamId($request))->whereNull('deleted_at')->first();
        abort_if(!$company, 404);

        return response()->json(['data' => $company]);
    }

    public function companiesUpdate(Request $request, $id): JsonResponse
    {
        DB::table('companies')->where('id', $id)->where('team_id', $this->teamId($request))
            ->update(array_merge($request->only(['name','tax_number','email','phone','address','company_group_id','credit_limit','payment_terms_days']), ['updated_at' => now()]));

        return response()->json(['message' => 'Company updated']);
    }

    public function companiesDestroy(Request $request, $id): JsonResponse
    {
        DB::table('companies')->where('id', $id)->where('team_id', $this->teamId($request))->update(['deleted_at' => now()]);

        return response()->json(['message' => 'Company deleted']);
    }

    // ── 5.4 Company Groups ───────────────────────────────────────

    public function groupsIndex(Request $request): JsonResponse
    {
        $query = CompanyGroup::where('team_id', $this->teamId($request));
        if ($request->filled('search')) $query->where('name', 'like', '%'.$request->search.'%');

        return response()->json($query->paginate($request->input('per_page', 20)));
    }

    public function groupsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'name_ar'            => 'nullable|string|max:255',
            'tax_number'         => 'nullable|string|max:50',
            'email'              => 'nullable|email',
            'phone'              => 'nullable|string|max:20',
            'credit_limit'       => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
        ]);

        $group = CompanyGroup::create(array_merge($validated, ['team_id' => $this->teamId($request)]));

        return response()->json(['message' => 'Group created', 'data' => $group], 201);
    }

    public function groupsShow($id, Request $request): JsonResponse
    {
        $group = CompanyGroup::where('team_id', $this->teamId($request))->findOrFail($id);

        return response()->json(['data' => $group]);
    }

    public function groupsUpdate(Request $request, $id): JsonResponse
    {
        $group = CompanyGroup::where('team_id', $this->teamId($request))->findOrFail($id);
        $group->update($request->only(['name','name_ar','tax_number','email','phone','credit_limit','payment_terms_days','description']));

        return response()->json(['message' => 'Group updated', 'data' => $group->fresh()]);
    }

    public function groupsDestroy(Request $request, $id): JsonResponse
    {
        CompanyGroup::where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Group deleted']);
    }

    public function groupsExposure($id, Request $request): JsonResponse
    {
        $group = CompanyGroup::where('team_id', $this->teamId($request))->findOrFail($id);

        $companies = DB::table('companies')
            ->where('company_group_id', $id)
            ->where('team_id', $this->teamId($request))
            ->whereNull('deleted_at')
            ->get(['id', 'name', 'credit_limit']);

        $totalCreditLimit = $companies->sum('credit_limit');

        $totalExposure = DB::table('promissories')
            ->whereIn('company_id', $companies->pluck('id'))
            ->where('status', '!=', 'paid')
            ->sum('total_amount');

        return response()->json([
            'data' => [
                'group'             => $group,
                'companies_count'   => $companies->count(),
                'total_credit_limit'=> $totalCreditLimit,
                'total_exposure'    => $totalExposure,
                'utilization_pct'   => $totalCreditLimit > 0 ? round($totalExposure / $totalCreditLimit * 100, 2) : 0,
            ]
        ]);
    }

    // ── 5.5 Company Notes ────────────────────────────────────────

    public function companyNotesIndex($companyId, Request $request): JsonResponse
    {
        $notes = CompanyNote::where('company_id', $companyId)->where('team_id', $this->teamId($request))->orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $notes]);
    }

    public function companyNotesStore(Request $request, $companyId): JsonResponse
    {
        $validated = $request->validate(['body' => 'required|string']);

        $note = CompanyNote::create([
            'company_id' => $companyId,
            'team_id'    => $this->teamId($request),
            'created_by' => $request->user()->id,
            'body'       => $validated['body'],
        ]);

        return response()->json(['message' => 'Note added', 'data' => $note], 201);
    }

    public function companyNotesDestroy(Request $request, $companyId, $noteId): JsonResponse
    {
        CompanyNote::where('company_id', $companyId)->where('team_id', $this->teamId($request))->findOrFail($noteId)->delete();

        return response()->json(['message' => 'Note deleted']);
    }

    // ── 5.6 Blocked Guests ───────────────────────────────────────

    public function blockedIndex(Request $request): JsonResponse
    {
        return response()->json(
            BlockedGuest::where('team_id', $this->teamId($request))->with(['blockedBy'])->orderBy('created_at', 'desc')->paginate(20)
        );
    }

    public function blockedStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'id_number'  => 'nullable|string|max:50',
            'reason'     => 'required|string',
            'block_type' => 'in:permanent,temporary',
            'end_date'   => 'nullable|date|required_if:block_type,temporary',
        ]);

        $blocked = BlockedGuest::create(array_merge($validated, [
            'team_id'    => $this->teamId($request),
            'blocked_by' => $request->user()->id,
        ]));

        return response()->json(['message' => 'Guest blocked', 'data' => $blocked], 201);
    }

    public function blockedDestroy(Request $request, $id): JsonResponse
    {
        BlockedGuest::where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Guest unblocked']);
    }

    // ── 5.7 Turnaway Logs ────────────────────────────────────────

    public function turnawayLogsIndex(Request $request): JsonResponse
    {
        $query = TurnawayLog::where('team_id', $this->teamId($request))->with(['reason', 'createdBy'])->orderBy('requested_date', 'desc');

        if ($request->filled('date')) $query->whereDate('requested_date', $request->date);

        return response()->json($query->paginate(20));
    }

    public function turnawayLogsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date'                => 'required|date',
            'guest_name'          => 'required|string|max:255',
            'guest_phone'         => 'nullable|string|max:20',
            'reason_id'           => 'nullable|exists:turnaway_reasons,id',
            'room_type_requested' => 'nullable|string|max:100',
            'notes'               => 'nullable|string',
        ]);

        $log = TurnawayLog::create(array_merge($validated, [
            'team_id'    => $this->teamId($request),
            'created_by' => $request->user()->id,
        ]));

        return response()->json(['message' => 'Turnaway log created', 'data' => $log], 201);
    }

    // ── 5.8 Turnaway Reasons ─────────────────────────────────────

    public function turnawayReasonsIndex(Request $request): JsonResponse
    {
        return response()->json(TurnawayReason::where('team_id', $this->teamId($request))->get());
    }

    public function turnawayReasonsStore(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:100', 'name_ar' => 'nullable|string|max:100', 'status' => 'boolean']);
        $reason = TurnawayReason::create(array_merge($validated, ['team_id' => $this->teamId($request)]));

        return response()->json(['message' => 'Reason created', 'data' => $reason], 201);
    }

    public function turnawayReasonsUpdate(Request $request, $id): JsonResponse
    {
        $reason = TurnawayReason::where('team_id', $this->teamId($request))->findOrFail($id);
        $reason->update($request->only(['name', 'name_ar', 'status']));

        return response()->json(['message' => 'Reason updated', 'data' => $reason->fresh()]);
    }

    public function turnawayReasonsDestroy(Request $request, $id): JsonResponse
    {
        TurnawayReason::where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Reason deleted']);
    }

    // ── 5.9 Customer Notes ───────────────────────────────────────

    public function customerNotesIndex($customerId, Request $request): JsonResponse
    {
        $notes = CustomerNote::where('customer_id', $customerId)->where('team_id', $this->teamId($request))->with(['createdBy'])->orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $notes]);
    }

    public function customerNotesStore(Request $request, $customerId): JsonResponse
    {
        $validated = $request->validate(['body' => 'required|string', 'type' => 'in:general,preference,complaint']);

        $note = CustomerNote::create([
            'customer_id' => $customerId,
            'team_id'     => $this->teamId($request),
            'created_by'  => $request->user()->id,
            'body'        => $validated['body'],
            'type'        => $validated['type'] ?? 'general',
        ]);

        return response()->json(['message' => 'Note added', 'data' => $note], 201);
    }

    public function customerNotesDestroy(Request $request, $customerId, $noteId): JsonResponse
    {
        CustomerNote::where('customer_id', $customerId)->where('team_id', $this->teamId($request))->findOrFail($noteId)->delete();

        return response()->json(['message' => 'Note deleted']);
    }

    // ── 5.10 Comments ────────────────────────────────────────────

    public function commentsIndex(Request $request): JsonResponse
    {
        $query = Comment::where('team_id', $this->teamId($request))->with(['user'])->orderBy('created_at', 'desc');

        if ($request->filled('type')) $query->where('commentable_type', $request->type);
        if ($request->filled('date')) $query->whereDate('created_at', $request->date);

        return response()->json($query->paginate(20));
    }

    public function commentsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id'   => 'required|integer',
            'body'             => 'required|string',
        ]);

        $comment = Comment::create(array_merge($validated, [
            'team_id' => $this->teamId($request),
            'user_id' => $request->user()->id,
        ]));

        return response()->json(['message' => 'Comment added', 'data' => $comment], 201);
    }

    public function commentsDestroy(Request $request, $id): JsonResponse
    {
        Comment::where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Comment deleted']);
    }

    // ── 5.11 Highlights ──────────────────────────────────────────

    public function highlightsIndex(Request $request): JsonResponse
    {
        return response()->json(Highlight::where('team_id', $this->teamId($request))->get());
    }

    public function highlightsStore(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'required|string|max:100', 'color' => 'required|string|max:20', 'status' => 'boolean']);
        $validated['name'] = json_encode(['en' => $validated['name']]);
        $highlight = Highlight::create(array_merge($validated, ['team_id' => $this->teamId($request)]));

        return response()->json(['message' => 'Highlight created', 'data' => $highlight], 201);
    }

    public function highlightsUpdate(Request $request, $id): JsonResponse
    {
        $highlight = Highlight::where('team_id', $this->teamId($request))->findOrFail($id);
        $data = $request->only(['name', 'color', 'status']);
        if (isset($data['name'])) $data['name'] = json_encode(['en' => $data['name']]);
        $highlight->update($data);

        return response()->json(['message' => 'Highlight updated', 'data' => $highlight->fresh()]);
    }

    public function highlightsDestroy(Request $request, $id): JsonResponse
    {
        Highlight::where('team_id', $this->teamId($request))->findOrFail($id)->delete();

        return response()->json(['message' => 'Highlight deleted']);
    }

    // ── 5.12 Duplicate Merge ─────────────────────────────────────

    public function mergeCustomers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'primary_id'  => 'required|exists:customers,id',
            'duplicate_id'=> 'required|exists:customers,id|different:primary_id',
            'fields_kept' => 'nullable|array',
        ]);

        $teamId = $this->teamId($request);
        $primary   = Customer::where('team_id', $teamId)->findOrFail($validated['primary_id']);
        $duplicate = Customer::where('team_id', $teamId)->findOrFail($validated['duplicate_id']);

        DB::transaction(function () use ($primary, $duplicate, $validated, $request, $teamId) {
            // Reassign reservations
            DB::table('reservations')->where('guest_id', $duplicate->id)->update(['guest_id' => $primary->id]);

            // Log the merge
            MergeLog::create([
                'team_id'             => $teamId,
                'primary_customer_id' => $primary->id,
                'merged_customer_id'  => $duplicate->id,
                'fields_kept'         => $validated['fields_kept'] ?? null,
                'merged_by'           => $request->user()->id,
            ]);

            // Soft-delete duplicate
            $duplicate->delete();
        });

        return response()->json(['message' => 'Customers merged successfully', 'data' => $primary->fresh()->makeHidden(['reservations_count','customer_types','customer_type_string','id_types','id_type_string','nationality_string','label'])]);
    }
}
