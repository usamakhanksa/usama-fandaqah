<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CheckInRecord;
use App\Models\CheckOutRecord;
use App\Models\Reservation;
use App\Models\RoomFloor;
use App\Models\Unit;
use App\Models\UnitStatus;
use App\Models\UnitType;
use App\Models\FinancialRecord;
use App\Services\StayChargeService;
use Illuminate\Http\Request;

class UnitHousingController extends Controller
{
    protected $service;
    protected $balanceService;
    protected $statusService;

    public function __construct(StayChargeService $service, \App\Services\CheckoutBalanceService $balanceService, \App\Services\RoomStatusService $statusService)
    {
        $this->service = $service;
        $this->balanceService = $balanceService;
        $this->statusService = $statusService;
    }
    public function filters()
    {
        return response()->json([
            'statuses' => UnitStatus::query()->orderBy('name')->get(['id', 'name', 'slug', 'color']),
            'types' => UnitType::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function floors(Request $request)
    {
        $search = $request->string('search')->value();
        $statusId = $request->integer('status_id');
        $typeId = $request->integer('type_id');

        $floors = RoomFloor::query()->orderBy('level')->get()->map(function (RoomFloor $floor) use ($search, $statusId, $typeId) {
            $units = Unit::query()->with(['unitStatus:id,name,slug,color'])
                ->where('room_floor_id', $floor->id)
                ->when($search, fn ($q) => $q->where(fn ($qq) => $qq->where('number', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")))
                ->when($statusId, fn ($q) => $q->where('unit_status_id', $statusId))
                ->when($typeId, fn ($q) => $q->where('unit_type_id', $typeId))
                ->orderBy('number')
                ->get();

            // Load latest reservation for occupied units
            $units->load(['unitStatus', 'unitType']);
            
            $legend = UnitStatus::query()->get(['id', 'name', 'slug', 'color'])->map(function (UnitStatus $status) use ($units) {
                return [
                    'name' => $status->name,
                    'slug' => $status->slug,
                    'color' => $status->color,
                    'count' => $units->where('unit_status_id', $status->id)->count(),
                ];
            })->values();

            return [
                'id' => $floor->id,
                'name' => $floor->name,
                'count' => $units->count(),
                'units' => $units->map(function (Unit $u) {
                    $res = null;
                    if ($u->unitStatus?->slug === 'occupied') {
                        $res = Reservation::where('unit_id', $u->id)
                            ->where('stay_type', '!=', 'checkout')
                            ->with('guest')
                            ->latest()
                            ->first();
                    }

                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'number' => $u->number,
                        'capacity' => $u->capacity,
                        'beds' => $u->beds,
                        'baths' => $u->baths,
                        'status_slug' => $u->unitStatus?->slug ?? 'maintenance',
                        'status_name' => $u->unitStatus?->name ?? 'Maintenance',
                        'status_color' => $u->unitStatus?->color ?? '#9ca3af',
                        'customer_name' => $res?->guest?->name,
                        'reservation_id' => $res?->id,
                        'action' => $this->actionByStatus($u->unitStatus?->slug),
                    ];
                })->values(),
                'legend' => $legend,
            ];
        });

        return response()->json($floors);
    }

    public function dailyStatus()
    {
        $arrivals = Reservation::query()->with(['guest', 'unit', 'booking'])
            ->whereDate('check_in', today())
            ->limit(20)
            ->get();

        $departures = Reservation::query()->with(['guest', 'unit', 'booking'])
            ->whereDate('check_out', today())
            ->limit(20)
            ->get();

        return response()->json([
            'arrivals' => $arrivals->map(fn (Reservation $r) => $this->dailyRow($r, 'checkin'))->values(),
            'departures' => $departures->map(fn (Reservation $r) => $this->dailyRow($r, 'checkout'))->values(),
        ]);
    }

    public function getBalance(Reservation $reservation)
    {
        return response()->json([
            'balance' => $this->balanceService->getBalance($reservation)
        ]);
    }

    public function checkIn(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'date' => ['required', 'date'],
            'time' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:2000'],
            'override_amount' => ['nullable', 'numeric', 'min:0'],
            'override_reason' => ['required_with:override_amount', 'nullable', 'string'],
        ]);

        $reservation = Reservation::with(['unit', 'booking'])->findOrFail($data['reservation_id']);
        $calculatedAmount = $this->service->calculateCharge($reservation, $data['time'], 'early_checkin');
        
        $finalAmount = $calculatedAmount;
        if (isset($data['override_amount'])) {
            if (!$request->user()->can('checkin.override_early_charge')) {
                return response()->json(['message' => 'You do not have permission to override early check-in charges.'], 403);
            }
            $finalAmount = $data['override_amount'];
            $this->service->logOverride([
                 'team_id' => $reservation->team_id,
                 'reservation_id' => $reservation->id,
                 'charge_type' => 'early_checkin',
                 'original_amount' => $calculatedAmount,
                 'overridden_amount' => $finalAmount,
                 'reason' => $data['override_reason']
            ]);
        }

        // Post financial record if amount > 0
        if ($finalAmount > 0) {
            FinancialRecord::create([
                'team_id' => $reservation->team_id,
                'booking_id' => $reservation->booking?->id,
                'label' => 'Early Check-in Charge',
                'amount' => $finalAmount,
                'type' => 'charge'
            ]);
        }

        $record = CheckInRecord::query()->create($data);
        $reservation->update(['stay_type' => 'checkin']);

        $checkedInStatus = UnitStatus::query()->where('slug', 'booked')->first();
        Unit::whereKey($data['unit_id'])->update(['unit_status_id' => $checkedInStatus?->id]);

        ActivityLog::query()->create([
            'user_id' => $request->user()?->id,
            'action' => 'check_in',
            'meta' => array_merge($data, ['calculated_charge' => $calculatedAmount, 'final_charge' => $finalAmount]),
        ]);

        return response()->json($record, 201);
    }

    public function checkOut(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'date' => ['required', 'date'],
            'time' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:2000'],
            'final_charges' => ['nullable', 'numeric', 'min:0'],
            'override_amount' => ['nullable', 'numeric', 'min:0'],
            'override_reason' => ['required_with:override_amount', 'nullable', 'string'],
            // Resolution fields
            'resolution_type' => ['nullable', 'string', 'in:collect_now,signed_promissory,unsigned_promissory,waived_promissory,corporate_transfer,refund_now,credit_note'],
            'resolution_amount' => ['nullable', 'numeric'],
            'resolution_notes' => ['nullable', 'string'],
            'promissory_due_date' => ['nullable', 'date'],
            'unsigned_reason' => ['required_if:resolution_type,unsigned_promissory', 'nullable', 'string'],
        ]);

        $reservation = Reservation::with(['unit', 'booking'])->findOrFail($data['reservation_id']);
        $calculatedAmount = $this->service->calculateCharge($reservation, $data['time'], 'late_checkout');
        
        $finalAmount = $calculatedAmount;
        if (isset($data['override_amount'])) {
            if (!$request->user()->can('checkout.override_late_charge')) {
                return response()->json(['message' => 'You do not have permission to override late check-out charges.'], 403);
            }
            $finalAmount = $data['override_amount'];
            $this->service->logOverride([
                 'team_id' => $reservation->team_id,
                 'reservation_id' => $reservation->id,
                 'charge_type' => 'late_checkout',
                 'original_amount' => $calculatedAmount,
                 'overridden_amount' => $finalAmount,
                 'reason' => $data['override_reason']
            ]);
        }

        // Post financial record if amount > 0
        if ($finalAmount > 0) {
            FinancialRecord::create([
                'team_id' => $reservation->team_id,
                'booking_id' => $reservation->booking?->id,
                'label' => 'Late Check-out Charge',
                'amount' => $finalAmount,
                'type' => 'charge'
            ]);
        }

        // Add additional final charges if any
        if (isset($data['final_charges']) && $data['final_charges'] > 0) {
            FinancialRecord::create([
                'team_id' => $reservation->team_id,
                'booking_id' => $reservation->booking?->id,
                'label' => 'Additional Checkout Charges',
                'amount' => $data['final_charges'],
                'type' => 'charge'
            ]);
        }

        // Check balance AFTER adding checkout charges
        $balance = $this->balanceService->getBalance($reservation);

        if ($balance != 0) {
            if (!isset($data['resolution_type'])) {
                return response()->json([
                    'message' => 'Unresolved balance of ' . $balance . ' SAR. Please provide a resolution method.',
                    'balance' => $balance,
                    'requires_resolution' => true
                ], 422);
            }

            // Resolve balance
            $this->balanceService->resolveBalance($reservation, [
                'resolution_type' => $data['resolution_type'],
                'amount' => $data['resolution_amount'] ?? abs($balance),
                'notes' => $data['resolution_notes'] ?? null,
                'due_date' => $data['promissory_due_date'] ?? null,
                'unsigned_reason' => $data['unsigned_reason'] ?? null,
            ]);
            
            // Re-check balance
            $balance = $this->balanceService->getBalance($reservation);
            if ($balance != 0) {
                return response()->json(['message' => 'Balance still unresolved after processing: ' . $balance . ' SAR'], 422);
            }
        }

        $record = CheckOutRecord::query()->create([
            ...$data,
            'final_charges' => ($data['final_charges'] ?? 0) + $finalAmount,
        ]);

        $reservation->update(['stay_type' => 'checkout']);

        $unit = Unit::findOrFail($data['unit_id']);
        $this->statusService->logStatusChange($unit, 'dirty', 'Checkout completed', $record);

        ActivityLog::query()->create([
            'user_id' => $request->user()?->id,
            'action' => 'check_out',
            'meta' => array_merge($data, ['calculated_charge' => $calculatedAmount, 'final_charge' => $finalAmount, 'balance_resolved' => true]),
        ]);

        return response()->json($record, 201);
    }

    public function updateStatus(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'status_id' => ['required', 'exists:unit_statuses,id'],
        ]);

        $status = UnitStatus::findOrFail($data['status_id']);
        $this->statusService->logStatusChange($unit, $status->slug, 'Manual management update');

        return response()->json(['message' => 'Status updated successfully']);
    }

    private function dailyRow(Reservation $reservation, string $type): array
    {
        return [
            'reservation_id' => $reservation->id,
            'unit_id' => $reservation->unit_id,
            'guest_name' => $reservation->guest?->name,
            'guest_avatar' => $reservation->guest?->avatar,
            'subtext' => '1 Room - 2 Guests - '.max(1, $reservation->check_in->diffInDays($reservation->check_out)).' Nights',
            'time' => $type === 'checkin' ? '12:00 AM' : '12:00 AM',
            'label' => $type === 'checkin' ? 'Check In Time' : 'Check Out Time',
            'check_in_date' => $reservation->check_in->format('j F Y'),
            'check_out_date' => $reservation->check_out->format('j F Y'),
            'price' => number_format((float) ($reservation->booking?->total_amount ?? 500), 0).' SAR',
            'payment_status' => 'Payment Confirmed',
            'room_number' => $reservation->unit?->number ?? '5005',
            'action' => $type === 'checkin' ? 'Check In' : 'Check Out',
            'type' => $type,
        ];
    }

    private function actionByStatus(?string $slug): string
    {
        return match ($slug) {
            'available' => 'Reserve Now',
            'booked' => 'Check IN',
            'busy' => 'Check Out',
            'housekeeping' => 'Ready To Move',
            default => 'Ready To Move',
        };
    }
}
