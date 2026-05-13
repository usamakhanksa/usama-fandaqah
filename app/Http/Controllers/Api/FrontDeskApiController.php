<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Unit;
use App\Models\WakeUpCall;
use App\Models\IptvGuestNeed;
use App\Models\CheckoutBalanceTransfer;
use App\Models\StayChargeConfig;
use App\Models\StayChargeOverride;
use App\Models\Guest;
use App\Models\Promissory;
use App\Services\StayChargeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FrontDeskApiController extends Controller
{
    public function __construct(protected StayChargeService $stayChargeService) {}

    // ── 3.1 Check-in ─────────────────────────────────────────────

    public function arrivals(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        $query = Reservation::where('team_id', $team->id)
            ->whereIn('status', ['confirmed', 'booked'])
            ->whereDate('check_in', $request->input('date', now()->toDateString()))
            ->orderBy('check_in');

        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->paginate(20));
    }

    public function checkIn(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'unit_id'           => 'nullable|exists:units,id',
            'signature_data'    => 'nullable|string',
            'deposit_amount'    => 'nullable|numeric|min:0',
            'payment_method'    => 'nullable|string',
        ]);

        $reservation = Reservation::where('team_id', $request->user()->currentTeam->id)->findOrFail($id);

        DB::transaction(function () use ($reservation, $validated, $request) {
            $data = ['status' => 'checked_in'];
            if (!empty($validated['unit_id'])) {
                $data['unit_id'] = $validated['unit_id'];
                Unit::where('id', $validated['unit_id'])->update(['status' => 5]); // occupied
            }
            $reservation->update($data);
        });

        return response()->json(['message' => 'Check-in completed', 'data' => $reservation->fresh()]);
    }

    // ── 3.2 Check-out ────────────────────────────────────────────

    public function departures(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        $query = Reservation::where('team_id', $team->id)
            ->where('status', 'checked_in')
            ->whereDate('check_out', $request->input('date', now()->toDateString()))
            ->orderBy('check_out');

        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->paginate(20));
    }

    public function checkOut(Request $request, $id): JsonResponse
    {
        $request->validate([
            'balance_action' => 'nullable|in:to_promissory,to_credit_note,refunded,waived',
            'balance_amount'  => 'nullable|numeric|min:0',
            'balance_notes'   => 'nullable|string',
        ]);

        $reservation = Reservation::where('team_id', $request->user()->currentTeam->id)
            ->where('status', 'checked_in')
            ->findOrFail($id);

        DB::transaction(function () use ($reservation, $request) {
            $reservation->update(['status' => 'checked_out']);

            if ($request->filled('balance_action') && $request->input('balance_amount', 0) > 0) {
                CheckoutBalanceTransfer::create([
                    'team_id'        => $reservation->team_id,
                    'reservation_id' => $reservation->id,
                    'transfer_type'  => $request->balance_action,
                    'amount'         => $request->balance_amount,
                    'notes'          => $request->balance_notes,
                    'transferred_by' => $request->user()->id,
                ]);
            }

            if ($reservation->unit_id) {
                Unit::where('id', $reservation->unit_id)->update(['status' => 2]); // dirty/cleaning
            }
        });

        return response()->json(['message' => 'Check-out completed', 'data' => $reservation->fresh()]);
    }

    public function folio($id, Request $request): JsonResponse
    {
        $reservation = Reservation::where('team_id', $request->user()->currentTeam->id)->findOrFail($id);

        $transactions = DB::table('transactions')
            ->where('payable_id', $reservation->id)
            ->where('payable_type', 'App\\Models\\Reservation')
            ->whereNull('deleted_at')
            ->get();

        $charges  = $transactions->where('kind', 'charge')->sum('amount');
        $payments = $transactions->where('kind', 'payment')->sum('amount');

        return response()->json([
            'data' => [
                'reservation'  => $reservation,
                'transactions' => $transactions,
                'charges'      => $charges,
                'payments'     => $payments,
                'balance'      => $charges - $payments,
            ]
        ]);
    }

    // ── 3.3 Walk-in ──────────────────────────────────────────────

    public function walkIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'guest_name'     => 'required|string|max:255',
            'guest_phone'    => 'nullable|string|max:20',
            'id_number'      => 'nullable|string|max:50',
            'unit_id'        => 'required|exists:units,id',
            'check_in'       => 'required|date',
            'check_out'      => 'required|date|after:check_in',
            'total_price'    => 'nullable|numeric|min:0',
            'direct_checkin' => 'boolean',
        ]);

        $team = $request->user()->currentTeam;

        $reservation = DB::transaction(function () use ($validated, $team, $request) {
            $guest = \App\Models\Guest::withoutGlobalScopes()->firstOrCreate(
                ['team_id' => $team->id, 'id_number' => $validated['id_number'] ?? null],
                ['name' => $validated['guest_name'], 'phone' => $validated['guest_phone'] ?? null, 'team_id' => $team->id]
            );

            $status = ($validated['direct_checkin'] ?? false) ? 'checked_in' : 'confirmed';

            $reservation = Reservation::create([
                'team_id'     => $team->id,
                'code'        => 'WI-' . strtoupper(substr(uniqid(), -6)),
                'guest_id'    => $guest->id,
                'unit_id'     => $validated['unit_id'],
                'check_in'    => $validated['check_in'],
                'check_out'   => $validated['check_out'],
                'total_price' => $validated['total_price'] ?? 0,
                'status'      => $status,
                'stay_type'   => 'walkin',
                'created_by'  => $request->user()->id,
            ]);

            if ($status === 'checked_in') {
                Unit::where('id', $validated['unit_id'])->update(['status' => 5]);
            }

            return $reservation;
        });

        return response()->json(['message' => 'Walk-in created', 'data' => $reservation], 201);
    }

    // ── 3.4 Guest Registration ───────────────────────────────────

    public function saveRegistration(Request $request, $reservationId): JsonResponse
    {
        $validated = $request->validate([
            'full_name'       => 'required|string|max:255',
            'id_type'         => 'required|string',
            'id_number'       => 'required|string|max:50',
            'nationality'     => 'nullable|string',
            'date_of_birth'   => 'nullable|date',
            'gender'          => 'nullable|in:male,female',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email',
            'signature_data'  => 'nullable|string',
        ]);

        $reservation = Reservation::where('team_id', $request->user()->currentTeam->id)->findOrFail($reservationId);

        $guest = \App\Models\Guest::withoutGlobalScopes()->updateOrCreate(
            ['team_id' => $reservation->team_id, 'id_number' => $validated['id_number']],
            [
                'name'         => $validated['full_name'],
                'id_type'      => $validated['id_type'],
                'nationality'  => $validated['nationality'] ?? null,
                'date_of_birth'=> $validated['date_of_birth'] ?? null,
                'gender'       => $validated['gender'] ?? null,
                'phone'        => $validated['phone'] ?? null,
                'email'        => $validated['email'] ?? null,
                'team_id'      => $reservation->team_id,
            ]
        );

        $reservation->update(['guest_id' => $guest->id]);

        return response()->json(['message' => 'Registration saved', 'data' => $guest]);
    }

    // ── 3.5 Room Assignment ──────────────────────────────────────

    public function unassignedReservations(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        $reservations = Reservation::where('team_id', $team->id)
            ->whereNull('unit_id')
            ->whereIn('status', ['confirmed', 'booked'])
            ->orderBy('check_in')
            ->get(['id', 'code', 'check_in', 'check_out', 'status']);

        return response()->json(['data' => $reservations]);
    }

    public function availableRooms(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        $units = Unit::where('team_id', $team->id)
            ->where('is_active', true)
            ->whereIn('status', [1, 3]) // available, clean
            ->get(['id', 'unit_number', 'name', 'status', 'unit_type_id', 'floor']);

        return response()->json(['data' => $units]);
    }

    public function assignRoom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'unit_id'        => 'required|exists:units,id',
        ]);

        $reservation = Reservation::where('team_id', $request->user()->currentTeam->id)
            ->findOrFail($validated['reservation_id']);

        $reservation->update(['unit_id' => $validated['unit_id']]);

        return response()->json(['message' => 'Room assigned', 'data' => $reservation->fresh()]);
    }

    // ── 3.6 Room Swap ────────────────────────────────────────────

    public function swapRoom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'new_unit_id'    => 'required|exists:units,id',
            'reason'         => 'required|string|max:500',
        ]);

        $reservation = Reservation::where('team_id', $request->user()->currentTeam->id)
            ->findOrFail($validated['reservation_id']);

        $oldUnitId = $reservation->unit_id;

        DB::transaction(function () use ($reservation, $validated, $oldUnitId) {
            $reservation->update(['unit_id' => $validated['new_unit_id']]);

            if ($oldUnitId) {
                Unit::where('id', $oldUnitId)->update(['status' => 2]); // dirty
            }
            Unit::where('id', $validated['new_unit_id'])->update(['status' => 5]); // occupied
        });

        return response()->json(['message' => 'Room swapped', 'data' => $reservation->fresh()]);
    }

    // ── 3.7 Early Check-in ───────────────────────────────────────

    public function earlyCheckinCharge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'actual_time'    => 'required|date_format:H:i',
        ]);

        $reservation = Reservation::where('team_id', $request->user()->currentTeam->id)
            ->findOrFail($validated['reservation_id']);

        $amount = $this->stayChargeService->calculateCharge($reservation, $validated['actual_time'], 'early_checkin');

        return response()->json(['data' => ['charge_amount' => $amount, 'reservation_id' => $reservation->id]]);
    }

    public function applyEarlyCheckin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'actual_time'    => 'required|date_format:H:i',
            'override'       => 'boolean',
            'override_reason'=> 'required_if:override,true|nullable|string|max:500',
            'charge_amount'  => 'required|numeric|min:0',
        ]);

        $reservation = Reservation::where('team_id', $request->user()->currentTeam->id)
            ->findOrFail($validated['reservation_id']);

        if ($validated['override'] ?? false) {
            $original = $this->stayChargeService->calculateCharge($reservation, $validated['actual_time'], 'early_checkin');
            $this->stayChargeService->logOverride([
                'team_id'          => $reservation->team_id,
                'reservation_id'   => $reservation->id,
                'charge_type'      => 'early_checkin',
                'original_amount'  => $original,
                'overridden_amount' => $validated['charge_amount'],
                'reason'           => $validated['override_reason'],
            ]);
        }

        return response()->json(['message' => 'Early check-in charge applied', 'charge_amount' => $validated['charge_amount']]);
    }

    // ── 3.8 Late Checkout ────────────────────────────────────────

    public function lateCheckoutCharge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'actual_time'    => 'required|date_format:H:i',
        ]);

        $reservation = Reservation::where('team_id', $request->user()->currentTeam->id)
            ->findOrFail($validated['reservation_id']);

        $amount = $this->stayChargeService->calculateCharge($reservation, $validated['actual_time'], 'late_checkout');

        return response()->json(['data' => ['charge_amount' => $amount, 'reservation_id' => $reservation->id]]);
    }

    public function applyLateCheckout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'actual_time'    => 'required|date_format:H:i',
            'override'       => 'boolean',
            'override_reason'=> 'required_if:override,true|nullable|string|max:500',
            'charge_amount'  => 'required|numeric|min:0',
        ]);

        $reservation = Reservation::where('team_id', $request->user()->currentTeam->id)
            ->findOrFail($validated['reservation_id']);

        if ($validated['override'] ?? false) {
            $original = $this->stayChargeService->calculateCharge($reservation, $validated['actual_time'], 'late_checkout');
            $this->stayChargeService->logOverride([
                'team_id'          => $reservation->team_id,
                'reservation_id'   => $reservation->id,
                'charge_type'      => 'late_checkout',
                'original_amount'  => $original,
                'overridden_amount' => $validated['charge_amount'],
                'reason'           => $validated['override_reason'],
            ]);
        }

        return response()->json(['message' => 'Late checkout charge applied', 'charge_amount' => $validated['charge_amount']]);
    }

    // ── 3.9 No-Show ──────────────────────────────────────────────

    public function markNoShow(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'reason'          => 'required|string|max:500',
            'cancel'          => 'boolean',
            'charge_amount'   => 'nullable|numeric|min:0',
        ]);

        $reservation = Reservation::where('team_id', $request->user()->currentTeam->id)
            ->whereIn('status', ['confirmed', 'booked'])
            ->findOrFail($id);

        $status = ($validated['cancel'] ?? true) ? 'no_show' : 'no_show';
        $reservation->update([
            'status'            => $status,
            'noshow_flag'       => true,
            'cancellation_reason' => $validated['reason'],
        ]);

        return response()->json(['message' => 'Reservation marked as no-show', 'data' => $reservation->fresh()]);
    }

    // ── 3.10 Wake-up Calls ───────────────────────────────────────

    public function wakeUpCalls(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        $query = WakeUpCall::where('team_id', $team->id)->orderBy('call_date')->orderBy('call_time');

        if ($request->filled('date'))   $query->whereDate('call_date', $request->date);
        if ($request->filled('status')) $query->where('status', $request->status);

        return response()->json($query->paginate(20));
    }

    public function storeWakeUpCall(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'unit_number'    => 'nullable|string',
            'call_time'      => 'required|date_format:H:i',
            'call_date'      => 'required|date',
            'repeat'         => 'in:once,daily',
        ]);

        $call = WakeUpCall::create(array_merge($validated, [
            'team_id'    => $request->user()->currentTeam->id,
            'status'     => 'pending',
            'created_by' => $request->user()->id,
        ]));

        return response()->json(['message' => 'Wake-up call scheduled', 'data' => $call], 201);
    }

    public function updateWakeUpCall(Request $request, $id): JsonResponse
    {
        $call = WakeUpCall::where('team_id', $request->user()->currentTeam->id)->findOrFail($id);
        $call->update($request->only(['call_time', 'call_date', 'repeat', 'status']));

        return response()->json(['message' => 'Updated', 'data' => $call->fresh()]);
    }

    public function completeWakeUpCall(Request $request, $id): JsonResponse
    {
        $call = WakeUpCall::where('team_id', $request->user()->currentTeam->id)->findOrFail($id);
        $call->update(['status' => 'completed']);

        return response()->json(['message' => 'Marked as completed']);
    }

    public function deleteWakeUpCall(Request $request, $id): JsonResponse
    {
        WakeUpCall::where('team_id', $request->user()->currentTeam->id)->findOrFail($id)->delete();

        return response()->json(['message' => 'Deleted']);
    }

    // ── 3.11 IPTV Guest Needs ────────────────────────────────────

    public function iptvNeeds(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        $query = IptvGuestNeed::where('team_id', $team->id)->orderBy('created_at', 'desc');

        if ($request->filled('date'))   $query->whereDate('created_at', $request->date);
        if ($request->filled('type'))   $query->where('request_type', $request->type);
        if ($request->filled('status')) $query->where('status', $request->status);

        return response()->json($query->paginate(20));
    }

    public function storeIptvNeed(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reservation_id'  => 'required|exists:reservations,id',
            'request_type'    => 'required|string|max:100',
            'request_details' => 'nullable|string',
        ]);

        $need = IptvGuestNeed::create(array_merge($validated, [
            'team_id' => $request->user()->currentTeam->id,
            'status'  => 'pending',
        ]));

        return response()->json(['message' => 'Request created', 'data' => $need], 201);
    }

    public function updateIptvNeed(Request $request, $id): JsonResponse
    {
        $need = IptvGuestNeed::where('team_id', $request->user()->currentTeam->id)->findOrFail($id);
        $data = $request->only(['status', 'request_details']);
        if (in_array($data['status'] ?? '', ['completed', 'in_progress'])) {
            $data['handled_by'] = $request->user()->id;
            $data['handled_at'] = now();
        }
        $need->update($data);

        return response()->json(['message' => 'Updated', 'data' => $need->fresh()]);
    }

    public function deleteIptvNeed(Request $request, $id): JsonResponse
    {
        IptvGuestNeed::where('team_id', $request->user()->currentTeam->id)->findOrFail($id)->delete();

        return response()->json(['message' => 'Deleted']);
    }

    // ── 3.12 Registration Cards ──────────────────────────────────

    public function registrationCards(Request $request): JsonResponse
    {
        $team = $request->user()->currentTeam;
        $query = Reservation::where('team_id', $team->id)
            ->whereIn('status', ['checked_in', 'checked_out', 'confirmed'])
            ->orderBy('check_in', 'desc');

        if ($request->filled('date'))  $query->whereDate('check_in', $request->date);
        if ($request->filled('guest')) $query->where('code', 'like', '%' . $request->guest . '%');

        return response()->json($query->paginate(20));
    }

    public function registrationCardData($id, Request $request): JsonResponse
    {
        $reservation = Reservation::where('team_id', $request->user()->currentTeam->id)->findOrFail($id);

        return response()->json([
            'data' => [
                'reservation' => $reservation,
                'hotel'       => ['name' => $request->user()->currentTeam->name ?? 'Hotel'],
            ]
        ]);
    }

    // ── 3.13 Balance Transfer ────────────────────────────────────

    public function balanceTransfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'transfer_type'  => 'required|in:to_promissory,to_credit_note,refunded,waived',
            'amount'         => 'required|numeric|min:0.01',
            'notes'          => 'nullable|string|max:500',
            'promissory_id'  => 'nullable|exists:promissories,id',
        ]);

        $reservation = Reservation::where('team_id', $request->user()->currentTeam->id)
            ->findOrFail($validated['reservation_id']);

        $transfer = CheckoutBalanceTransfer::create([
            'team_id'        => $reservation->team_id,
            'reservation_id' => $reservation->id,
            'transfer_type'  => $validated['transfer_type'],
            'amount'         => $validated['amount'],
            'promissory_id'  => $validated['promissory_id'] ?? null,
            'notes'          => $validated['notes'] ?? null,
            'transferred_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Balance transfer recorded', 'data' => $transfer], 201);
    }
}
