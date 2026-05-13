<?php

namespace App\Repositories;

use App\Models\Reservation;
use App\Models\Team;
use App\Models\Unit;
use App\Models\Customer;
use App\Models\ReservationRoomTransfer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class ReservationRepository
{
    public function getReservationsQuery(Team $team, array $filters = []): Builder
    {
        $query = Reservation::where('team_id', $team->id);

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('number', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('customer', function($qc) use ($filters) {
                      $qc->where('name', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date'])) {
            $query->whereDate('check_in', '<=', $filters['date'])
                  ->whereDate('check_out', '>=', $filters['date']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function create(array $data): Reservation
    {
        return Reservation::create($data);
    }

    public function getAvailableUnits(Team $team): array
    {
        return Unit::where('team_id', $team->id)
            ->where('status', 'clean')
            ->get()
            ->toArray();
    }

    public function getActiveCustomers(Team $team): array
    {
        return Customer::where('team_id', $team->id)
            ->where('is_active', true)
            ->get()
            ->toArray();
    }

    public function getActiveSources(Team $team): array
    {
        return DB::table('sources')
            ->where('team_id', $team->id)
            ->where('is_active', true)
            ->get()
            ->toArray();
    }

    public function getRatePlans(Team $team): array
    {
        return DB::table('rate_plans')
            ->where('team_id', $team->id)
            ->where('is_active', true)
            ->get()
            ->toArray();
    }

    public function getReservationTimeline(Reservation $reservation): array
    {
        // Placeholder for timeline logic
        return [];
    }

    public function calculateBalance(Reservation $reservation): array
    {
        $totalPaid = DB::table('transactions')
            ->where('payable_id', $reservation->id)
            ->where('payable_type', 'App\Models\Reservation')
            ->where('kind', 'payment')
            ->sum('amount');

        return [
            'total_amount' => $reservation->total_amount,
            'paid' => $totalPaid,
            'balance' => $reservation->total_amount - $totalPaid,
        ];
    }

    public function syncGuests(Reservation $reservation, array $guests): void
    {
        $reservation->guests()->sync($guests);
    }

    public function createCheckInRecord(Reservation $reservation, array $data): void
    {
        // Placeholder
    }

    public function createCheckOutRecord(Reservation $reservation, array $data): void
    {
        // Placeholder
    }

    public function createNoShowCharge(Reservation $reservation): void
    {
        // Placeholder
    }

    public function createExtensionRecord(Reservation $reservation, int $days, $reason = 'Guest requested stay extension'): void
    {
        $oldCheckOut = $reservation->getOriginal('check_out');
        
        \App\Models\ReservationStayExtension::create([
            'team_id' => $reservation->team_id,
            'reservation_id' => $reservation->id,
            'old_check_out' => $oldCheckOut,
            'new_check_out' => $reservation->check_out,
            'extension_days' => $days,
            'reason' => $reason,
            'created_by' => auth()->id() ?? 1,
        ]);
    }

    public function createTransferRecord(Reservation $reservation, $oldUnitId, $newUnitId, $reason = 'Room Transfer'): void
    {
        ReservationRoomTransfer::create([
            'team_id' => $reservation->team_id,
            'reservation_id' => $reservation->id,
            'from_unit_id' => $oldUnitId,
            'to_unit_id' => $newUnitId,
            'reason' => $reason,
            'created_by' => auth()->id() ?? 1,
        ]);
    }

    public function getArrivalsQuery(Team $team, array $filters): Builder
    {
        return Reservation::where('team_id', $team->id)
            ->whereDate('check_in', $filters['date'] ?? Carbon::today())
            ->whereIn('status', ['confirmed', 'booked']);
    }

    public function getDeparturesQuery(Team $team, array $filters): Builder
    {
        return Reservation::where('team_id', $team->id)
            ->whereDate('check_out', $filters['date'] ?? Carbon::today())
            ->where('status', 'checked_in');
    }

    public function getInHouseQuery(Team $team, array $filters): Builder
    {
        return Reservation::where('team_id', $team->id)
            ->where('status', 'checked_in');
    }

    public function generateReservationNumber(Team $team): string
    {
        $prefix = strtoupper(substr($team->name, 0, 3));
        $count = Reservation::where('team_id', $team->id)->count() + 1;
        return $prefix . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    public function getTransferHistory(Team $team, array $filters = []): Builder
    {
        $query = ReservationRoomTransfer::where('team_id', $team->id)
            ->with(['reservation.customer', 'fromUnit', 'toUnit', 'creator']);

        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        if (!empty($filters['reservation_id'])) {
            $query->where('reservation_id', $filters['reservation_id']);
        }

        if (!empty($filters['unit_id'])) {
            $query->where(function($q) use ($filters) {
                $q->where('from_unit_id', $filters['unit_id'])
                  ->orWhere('to_unit_id', $filters['unit_id']);
            });
        }

        return $query->orderBy('created_at', 'desc');
    }
    public function getExtensionHistory(Team $team, array $filters = []): Builder
    {
        $query = \App\Models\ReservationStayExtension::where('team_id', $team->id)
            ->with(['reservation.customer', 'creator']);

        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        if (!empty($filters['reservation_id'])) {
            $query->where('reservation_id', $filters['reservation_id']);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function getContractHistory(Team $team, array $filters = []): Builder
    {
        $query = \App\Models\ReservationContract::where('team_id', $team->id)
            ->with(['reservation.customer', 'generatedBy', 'signedBy']);

        if (!empty($filters['date'])) {
            $query->whereDate('generated_at', $filters['date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['reservation_id'])) {
            $query->where('reservation_id', $filters['reservation_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('contract_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('reservation', function($qr) use ($filters) {
                      $qr->where('code', 'like', '%' . $filters['search'] . '%')
                         ->orWhereHas('customer', function($qc) use ($filters) {
                             $qc->where('name', 'like', '%' . $filters['search'] . '%');
                         });
                  });
            });
        }

        return $query->orderBy('generated_at', 'desc');
    }

    public function generateContractNumber(Team $team): string
    {
        $prefix = 'CNT';
        $year = date('Y');
        $count = \App\Models\ReservationContract::where('team_id', $team->id)
            ->whereYear('created_at', date('Y'))
            ->count() + 1;
        return $prefix . '-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }
}
