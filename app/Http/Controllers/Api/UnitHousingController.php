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
use Illuminate\Http\Request;

class UnitHousingController extends Controller
{
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

    public function checkIn(Request $request)
    {
        $data = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'date' => ['required', 'date'],
            'time' => ['required', 'string'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $record = CheckInRecord::query()->create($data);
        $reservation = Reservation::findOrFail($data['reservation_id']);
        $reservation->update(['stay_type' => 'checkin']);

        $checkedInStatus = UnitStatus::query()->where('slug', 'booked')->first();
        Unit::whereKey($data['unit_id'])->update(['unit_status_id' => $checkedInStatus?->id]);

        ActivityLog::query()->create([
            'user_id' => $request->user()?->id,
            'action' => 'check_in',
            'meta' => $data,
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
        ]);

        $record = CheckOutRecord::query()->create([
            ...$data,
            'final_charges' => $data['final_charges'] ?? 0,
        ]);

        $reservation = Reservation::findOrFail($data['reservation_id']);
        $reservation->update(['stay_type' => 'checkout']);

        $availableStatus = UnitStatus::query()->where('slug', 'available')->first();
        Unit::whereKey($data['unit_id'])->update(['unit_status_id' => $availableStatus?->id]);

        ActivityLog::query()->create([
            'user_id' => $request->user()?->id,
            'action' => 'check_out',
            'meta' => $data,
        ]);

        return response()->json($record, 201);
    }

    public function updateStatus(Request $request, Unit $unit)
    {
        $data = $request->validate([
            'status_id' => ['required', 'exists:unit_statuses,id'],
        ]);

        $unit->update(['unit_status_id' => $data['status_id']]);

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
