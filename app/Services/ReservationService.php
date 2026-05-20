<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Guest;
use App\Models\Room;
use App\Models\Unit;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\FinancialRecord;
use App\Models\Invoice;
use App\Models\Payment;
use App\GroupReservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReservationService
{
    /**
     * Get reservations with filters and pagination.
     */
    public function getReservations(array $filters = [], int $perPage = 15)
    {
        $teamId = auth()->user()->current_team_id;
        $query = Reservation::with(['guest', 'unit', 'source'])
            ->where('team_id', $teamId);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('guest', fn($gq) => $gq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"));
            });
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['date_in'])) {
            $query->whereDate('check_in', '>=', $filters['date_in']);
        }
        if (!empty($filters['date_out'])) {
            $query->whereDate('check_out', '<=', $filters['date_out']);
        }
        if (!empty($filters['source_id'])) {
            $query->where('source_id', $filters['source_id']);
        }
        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        $sortMap = ['check_in' => 'check_in', 'check_out' => 'check_out', 'guest_name' => 'created_at'];
        $sortField = $sortMap[$filters['sort_by'] ?? ''] ?? ($filters['sort_by'] ?? 'created_at');
        $query->orderBy($sortField, $filters['sort_order'] ?? 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Create a new reservation.
     */
    public function createReservation(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Handle Guest
            if (!empty($data['guest_id'])) {
                $guest = Guest::findOrFail($data['guest_id']);
            } else {
                $guest = Guest::create($data['guest_data']);
            }

            // Create Reservation
            $reservation = Reservation::create([
                'team_id' => auth()->user()->current_team_id,
                'code' => $this->generateCode(),
                'guest_id' => $guest->id,
                'room_id' => $data['room_id'],
                'unit_id' => $data['unit_id'] ?? null,
                'status' => 'confirmed',
                'reservation_category_type' => $data['category'] ?? 'Normal',
                'special_request' => $data['special_request'] ?? null,
                'company_id' => $data['company_id'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'created_by' => auth()->id(),
            ]);

            // Create Booking if confirmed
            $this->createBookingForReservation($reservation, $data);

            return $reservation;
        });
    }

    /**
     * Update an existing reservation.
     */
    public function updateReservation($id, array $data)
    {
        $reservation = Reservation::findOrFail($id);

        if (in_array($reservation->status, ['checked-in', 'checked-out']) && !auth()->user()->isSuperAdmin()) {
            throw new \Exception('Cannot edit reservation after check-in/out.');
        }

        return DB::transaction(function () use ($reservation, $data) {
            $reservation->update($data);
            
            if ($reservation->booking) {
                $reservation->booking->update([
                    'check_in' => $reservation->check_in,
                    'check_out' => $reservation->check_out,
                ]);
            }

            return $reservation;
        });
    }

    /**
     * Check-in a reservation.
     */
    public function checkIn($id)
    {
        $reservation = Reservation::findOrFail($id);
        return DB::transaction(function () use ($reservation) {
            $reservation->update(['check_in' => now()]);
            if ($reservation->unit) {
                $reservation->unit->update(['status' => 5]); // occupied
            }
            return $reservation;
        });
    }

    public function checkOut($id)
    {
        $reservation = Reservation::findOrFail($id);
        return DB::transaction(function () use ($reservation) {
            $reservation->update(['check_out' => now()]);
            if ($reservation->unit) {
                $reservation->unit->update(['status' => 2]); // dirty
            }
            return $reservation;
        });
    }

    public function cancel($id, string $reason = null)
    {
        $reservation = Reservation::findOrFail($id);
        return DB::transaction(function () use ($reservation, $reason) {
            $reservation->update(['status' => 'canceled', 'cancellation_reason' => $reason]);
            if ($reservation->unit) {
                $reservation->unit->update(['status' => 1]); // available
            }
            return $reservation;
        });
    }

    /**
     * Quick create a reservation with auto-room assignment.
     */
    public function quickCreateReservation(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Find first available room of type
            $room = Room::where('room_type_id', $data['room_type_id'])
                ->where('status', 'available') // Assuming status exists
                ->first();

            if (!$room) {
                // Fallback to any available room if type is not available? 
                // Or throw exception.
                throw new \Exception('No rooms of this type available.');
            }

            // Handle Guest
            $guest = Guest::firstOrCreate(
                ['phone' => $data['phone']],
                ['name' => $data['guest_name'], 'email' => $data['email'] ?? null]
            );

            // Create Reservation
            $reservation = Reservation::create([
                'team_id' => auth()->user()->current_team_id,
                'code' => $this->generateCode(),
                'guest_id' => $guest->id,
                'room_id' => $room->id,
                'status' => 'confirmed',
                'source_id' => $data['source_id'] ?? null,
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'created_by' => auth()->id(),
            ]);

            $this->createBookingForReservation($reservation, []);

            return $reservation;
        });
    }

    /**
     * Get calendar grid data (rooms and reservations).
     */
    public function getCalendarGrid(array $filters)
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->endOfMonth()->toDateString();
        $teamId = auth()->user()->current_team_id;

        $units = \App\Unit::where('team_id', $teamId)->with('unit_category')->get();

        $reservations = Reservation::where('team_id', $teamId)
            ->where(fn($q) => $q->whereBetween('check_in', [$startDate, $endDate])
                ->orWhereBetween('check_out', [$startDate, $endDate])
                ->orWhere(fn($q2) => $q2->where('check_in', '<=', $startDate)->where('check_out', '>=', $endDate)))
            ->whereIn('status', ['confirmed', 'canceled'])
            ->whereNull('deleted_at')
            ->get();

        return [
            'rooms' => $units,
            'reservations' => $reservations->groupBy('unit_id'),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * Get reservations arriving today.
     */
    public function getArrivals(array $filters)
    {
        $date = $filters['date'] ?? now()->toDateString();
        $teamId = auth()->user()->current_team_id;
        $query = Reservation::with(['guest', 'unit', 'source'])
            ->where('team_id', $teamId)
            ->whereDate('check_in', $date)
            ->where('status', 'confirmed');
        if (!empty($filters['source_id'])) $query->where('source_id', $filters['source_id']);
        return $query->orderBy('check_in')->paginate($filters['per_page'] ?? 25);
    }

    public function getDepartures(array $filters)
    {
        $date = $filters['date'] ?? now()->toDateString();
        $teamId = auth()->user()->current_team_id;
        $query = Reservation::with(['guest', 'unit', 'source'])
            ->where('team_id', $teamId)
            ->whereDate('check_out', $date)
            ->whereNotNull('check_in')
            ->whereNull('check_out');
        if (!empty($filters['source_id'])) $query->where('source_id', $filters['source_id']);
        return $query->orderBy('check_out')->paginate($filters['per_page'] ?? 25);
    }

    public function getInHouseGuests(array $filters)
    {
        $teamId = auth()->user()->current_team_id;
        $query = Reservation::with(['guest', 'unit', 'source'])
            ->where('team_id', $teamId)
            ->whereNotNull('check_in')
            ->whereNull('check_out');
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(fn($q) => $q->where('code', 'like', "%$s%")
                ->orWhereHas('guest', fn($gq) => $gq->where('name', 'like', "%$s%")));
        }
        return $query->orderBy('check_in', 'desc')->paginate($filters['per_page'] ?? 25);
    }

    public function getOnlineReservations(array $filters)
    {
        $teamId = auth()->user()->current_team_id;
        $query = Reservation::with(['guest', 'unit', 'source'])
            ->where('team_id', $teamId)
            ->where('is_online', 1);
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['date'])) $query->whereDate('check_in', $filters['date']);
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(fn($q) => $q->where('code', 'like', "%$s%")
                ->orWhereHas('guest', fn($gq) => $gq->where('name', 'like', "%$s%")));
        }
        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 25);
    }

    /**
     * Confirm a pending reservation.
     */
    public function confirm($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->update(['status' => 'confirmed']);
        return $reservation;
    }

    public function reject($id, string $reason = null)
    {
        return $this->cancel($id, $reason ?: 'Rejected by staff');
    }

    /**
     * Generate unique reservation code.
     */
    protected function generateCode()
    {
        return 'RSV-' . strtoupper(Str::random(8));
    }

    /**
     * Helper to create booking and related records.
     */
    protected function createBookingForReservation(Reservation $reservation, array $data)
    {
        $nights = Carbon::parse($reservation->check_in)->diffInDays(Carbon::parse($reservation->check_out)) ?: 1;
        $roomPrice = $reservation->room->base_price ?? 0;
        $total = $roomPrice * $nights;

        $booking = Booking::create([
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->guest_id,
            'room_id' => $reservation->room_id,
            'check_in' => $reservation->check_in,
            'check_out' => $reservation->check_out,
            'total_amount' => $total,
        ]);

        FinancialRecord::create([
            'booking_id' => $booking->id,
            'label' => 'Initial Room Charge',
            'amount' => $total,
            'type' => 'debit',
        ]);
    }

    /**
     * Get reservations from OTA channels (Booking.com, Expedia, etc.)
     */
    public function getOTAReservations(array $filters = [])
    {
        $teamId = auth()->user()->current_team_id;
        $query = Reservation::with(['guest', 'unit', 'source'])
            ->where('team_id', $teamId)
            ->whereHas('source', fn($q) => $q->where('is_travel_agent', true));
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['date'])) $query->whereDate('check_in', $filters['date']);
        if (!empty($filters['source_id'])) $query->where('source_id', $filters['source_id']);
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $query->where(fn($q) => $q->where('code', 'like', "%$s%")
                ->orWhereHas('guest', fn($gq) => $gq->where('name', 'like', "%$s%")));
        }
        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 25);
    }

    /**
     * Sync status with the OTA channel.
     */
    public function syncOTAStatus($id)
    {
        $reservation = Reservation::findOrFail($id);
        
        // This is a placeholder for actual channel manager integration
        $reservation->touch();

        return $reservation;
    }

    /**
     * Get group reservations with filters.
     */
    public function getGroupReservations(array $filters)
    {
        $teamId = auth()->user()->current_team_id;
        $query = GroupReservation::with(['company'])
            ->where('team_id', $teamId);
        if (!empty($filters['search'])) {
            $query->where('data', 'like', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }
        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 25);
    }

    /**
     * Create a group reservation with multiple rooms.
     */
    public function createGroupReservation(array $data)
    {
        return DB::transaction(function () use ($data) {
            $group = GroupReservation::create([
                'team_id' => auth()->user()->current_team_id,
                'company_id' => $data['company_id'] ?? null,
                'data' => [
                    'name' => $data['name'],
                    'status' => 'confirmed',
                    'rate' => $data['rate'] ?? 0,
                    'instructions' => $data['instructions'] ?? '',
                ]
            ]);

            foreach ($data['room_ids'] as $roomId) {
                $reservation = Reservation::create([
                    'team_id' => auth()->user()->current_team_id,
                    'group_reservation_id' => $group->id,
                    'code' => $this->generateCode(),
                    'room_id' => $roomId,
                    'guest_id' => $data['guest_id'] ?? null, // Default guest or handled separately
                    'check_in' => $data['check_in'],
                    'check_out' => $data['check_out'],
                    'status' => 'confirmed',
                    'created_by' => auth()->id(),
                ]);

                $this->createBookingForReservation($reservation, []);
            }

            return $group;
        });
    }

    /**
     * Cancel a group reservation and all its rooms.
     */
    public function cancelGroupReservation($id, string $reason = null)
    {
        $group = GroupReservation::findOrFail($id);
        
        return DB::transaction(function () use ($group, $reason) {
            $group->update(['data->status' => 'cancelled']);
            
            foreach ($group->reservations as $reservation) {
                $this->cancel($reservation->id, $reason ?: 'Group cancellation');
            }

            return $group;
        });
    }

    /**
     * Get all guests linked to a reservation.
     */
    public function getReservationGuests($reservationId)
    {
        return DB::table('reservation_guests')
            ->join('guests', 'reservation_guests.guest_id', '=', 'guests.id')
            ->where('reservation_id', $reservationId)
            ->select('guests.*', 'reservation_guests.is_primary', 'reservation_guests.relation')
            ->get();
    }

    /**
     * Add a guest to a reservation.
     */
    public function addGuestToReservation($reservationId, $guestId, $data = [])
    {
        return DB::table('reservation_guests')->insert([
            'reservation_id' => $reservationId,
            'guest_id' => $guestId,
            'is_primary' => $data['is_primary'] ?? false,
            'relation' => $data['relation'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Remove a guest from a reservation.
     */
    public function removeGuestFromReservation($reservationId, $guestId)
    {
        return DB::table('reservation_guests')
            ->where('reservation_id', $reservationId)
            ->where('guest_id', $guestId)
            ->delete();
    }

    /**
     * Get all rooms assigned to a reservation (via group).
     */
    public function getReservationRooms($reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);
        
        if (!$reservation->group_reservation_id) {
            return Reservation::with(['room.roomType', 'guest'])->where('id', $reservationId)->get();
        }

        return Reservation::with(['room.roomType', 'guest'])
            ->where('group_reservation_id', $reservation->group_reservation_id)
            ->get();
    }

    /**
     * Add a room to a reservation (converts it to a group if needed).
     */
    public function addRoomToReservation($reservationId, $roomId, $data = [])
    {
        return DB::transaction(function () use ($reservationId, $roomId, $data) {
            $parent = Reservation::findOrFail($reservationId);

            // 1. Ensure we have a GroupReservation
            if (!$parent->group_reservation_id) {
                $group = GroupReservation::create([
                    'team_id' => $parent->team_id,
                    'company_id' => $parent->company_id,
                    'data' => [
                        'name' => 'Group for ' . $parent->code,
                        'status' => 'confirmed'
                    ]
                ]);
                $parent->update(['group_reservation_id' => $group->id]);
            }

            // 2. Create the new reservation for the additional room
            return Reservation::create([
                'team_id' => $parent->team_id,
                'group_reservation_id' => $parent->group_reservation_id,
                'code' => $this->generateCode(),
                'room_id' => $roomId,
                'guest_id' => $parent->guest_id, // Default to parent guest
                'check_in' => $parent->check_in,
                'check_out' => $parent->check_out,
                'status' => 'confirmed',
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Remove a room from a reservation.
     */
    public function removeRoomFromReservation($reservationId, $subReservationId)
    {
        $sub = Reservation::findOrFail($subReservationId);
        
        // Don't allow deleting the only room if needed, or just delete it
        if ($sub->id == $reservationId) {
             throw new \Exception("Cannot remove the primary reservation room from itself.");
        }

        return $sub->delete();
    }
}
