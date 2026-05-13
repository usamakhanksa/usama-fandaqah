<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NoShowChargeRule;
use App\Http\Requests\NoShowChargeRuleRequest;
use App\Http\Resources\NoShowChargeRuleResource;
use App\Services\NoShowChargeRuleService;
use Illuminate\Http\Request;

class NoShowChargeRuleController extends Controller
{
    protected $service;

    public function __construct(NoShowChargeRuleService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $teamId = $request->user()->current_team_id;
        $rules = NoShowChargeRule::where('team_id', $teamId)
            ->when($request->is_active, function($q) use ($request) {
                $q->where('is_active', $request->is_active === 'true');
            })
            ->orderBy('start_date', 'asc')
            ->get();

        return NoShowChargeRuleResource::collection($rules);
    }

    public function store(NoShowChargeRuleRequest $request)
    {
        $teamId = $request->user()->current_team_id;

        // Check for overlaps
        if ($this->service->checkOverlaps($teamId, $request->start_date, $request->end_date, $request->applies_to)) {
            return response()->json(['message' => 'This rule overlaps with an existing active rule.'], 422);
        }

        $rule = NoShowChargeRule::create(array_merge($request->validated(), [
            'team_id' => $teamId,
            'created_by' => $request->user()->id
        ]));

        return new NoShowChargeRuleResource($rule);
    }

    public function show(NoShowChargeRule $no_show_rule)
    {
        return new NoShowChargeRuleResource($no_show_rule);
    }

    public function update(NoShowChargeRuleRequest $request, NoShowChargeRule $no_show_rule)
    {
        $teamId = $request->user()->current_team_id;

        // Rule 3: Cannot edit an active rule if Night Audit already ran for its date range
        if ($no_show_rule->is_active && $this->service->hasNightAuditRun($teamId, $no_show_rule->start_date, $no_show_rule->end_date)) {
            return response()->json(['message' => 'Cannot edit a rule for which Night Audit has already been processed.'], 422);
        }

        // Check for overlaps (excluding self)
        if ($this->service->checkOverlaps($teamId, $request->start_date, $request->end_date, $request->applies_to, $no_show_rule->id)) {
            return response()->json(['message' => 'This rule overlaps with another active rule.'], 422);
        }

        $no_show_rule->update($request->validated());

        return new NoShowChargeRuleResource($no_show_rule);
    }

    public function destroy(NoShowChargeRule $no_show_rule)
    {
        $no_show_rule->delete();
        return response()->json(['message' => 'Rule deleted successfully.']);
    }

    public function previewAffected(Request $request)
    {
        $teamId = $request->user()->current_team_id;
        $date = $request->date ?: \App\Team::find($teamId)->business_date;

        // Find reservations that would be marked as no-show today
        $noshows = \DB::table('reservations')
            ->where('team_id', $teamId)
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_in', '<=', $date)
            ->get();

        $preview = $noshows->map(function($res) use ($teamId, $date) {
            $rule = $this->service->getApplicableRule($teamId, $date, $res->rent_type ?? 'daily');
            $charge = 0;
            if ($rule) {
                if ($rule->charge_type === 'fixed') {
                    $charge = $rule->charge_amount;
                } else {
                    $total = $res->total_price ?? 0;
                    $charge = ($total * $rule->charge_amount) / 100;
                }
            }
            return [
                'reservation_id' => $res->id,
                'customer_name' => $res->customer_name ?? 'N/A',
                'check_in' => $res->check_in,
                'total_price' => $res->total_price ?? 0,
                'applicable_rule' => $rule ? $rule->name : 'None (0 Charge)',
                'projected_charge' => $charge
            ];
        });

        return response()->json($preview);
    }
}
