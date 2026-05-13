<?php

namespace App\Services\Reservation;

use App\Models\Reservation;
use App\Models\Team;
use App\Models\Unit;
use App\Models\Customer;
use App\Repositories\ReservationRepository;
use App\Events\ReservationCreated;
use App\Events\ReservationUpdated;
use App\Events\ReservationCancelled;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class ReservationService
{
    protected ReservationRepository $repository;

    public function __construct(ReservationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getReservations(Team $team, array $filters = []): Collection
    {
        return $this->repository->getReservationsQuery($team, $filters)
            ->with(['customer', 'unit', 'guests'])
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getCreateData(Team $team): array
    {
        return [
            'units' => $this->repository->getAvailableUnits($team),
            'customers' => $this->repository->getActiveCustomers($team),
            'sources' => $this->repository->getActiveSources($team),
            'rate_plans' => $this->repository->getRatePlans($team),
        ];
    }

    public function createReservation(Team $team, array $data): Reservation
    {
        $reservation = $this->repository->create([
            'team_id' => $team->id,
            'number' => $this->generateReservationNumber($team),
            'customer_id' => $data['customer_id'],
            'unit_id' => $data['unit_id'] ?? null,
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'status' => $data['status'] ?? 'confirmed',
            'source_id' => $data['source_id'] ?? null,
            'adults' => $data['adults'] ?? 1,
            'children' => $data['children'] ?? 0,
            'special_requests' => $data['special_requests'] ?? null,
            'rate_code' => $data['rate_code'] ?? null,
            'total_amount' => $this->calculateTotalAmount($data),
            'created_by' => auth()->id(),
        ]);

        // Create guests if provided
        if (!empty($data['guests'])) {
            $this->syncGuests($reservation, $data['guests']);
        }

        event(new ReservationCreated($reservation));

        return $reservation;
    }

    public function getReservationDetails(Reservation $reservation): array
    {
        return [
            'reservation' => $reservation->load([
                'customer',
                'unit',
                'guests',
                'transactions',
                'services',
                'invoices',
                'comments',
            ]),
            'timeline' => $this->repository->getReservationTimeline($reservation),
            'balance' => $this->calculateBalance($reservation),
        ];
    }

    public function getEditData(Reservation $reservation): array
    {
        return [
            'reservation' => $reservation->load(['customer', 'unit', 'guests']),
            'availableUnits' => $this->repository->getAvailableUnitsForEdit($reservation),
            'sources' => $this->repository->getActiveSources($reservation->team),
            'ratePlans' => $this->repository->getRatePlans($reservation->team),
        ];
    }

    public function updateReservation(Reservation $reservation, array $data): void
    {
        $originalUnit = $reservation->unit_id;
        
        $reservation->update($data);

        if (isset($data['guests'])) {
            $this->syncGuests($reservation, $data['guests']);
        }

        // Handle unit change
        if (isset($data['unit_id']) && $data['unit_id'] !== $originalUnit) {
            $this->handleUnitChange($reservation, $originalUnit);
        }

        event(new ReservationUpdated($reservation));
    }

    public function deleteReservation(Reservation $reservation): void
    {
        $reservation->delete();
    }

    public function restoreReservation(Reservation $reservation): void
    {
        $reservation->restore();
    }

    public function checkIn(Reservation $reservation, array $data): void
    {
        $reservation->update([
            'checked_in' => Carbon::now(),
            'status' => 'checked_in',
            'actual_check_in' => $data['actual_check_in'] ?? Carbon::now(),
        ]);

        // Update unit status
        if ($reservation->unit) {
            $reservation->unit->update(['status' => 'occupied']);
        }

        // Create check-in record
        $this->repository->createCheckInRecord($reservation, $data);
    }

    public function checkOut(Reservation $reservation, array $data): void
    {
        $reservation->update([
            'checked_out' => Carbon::now(),
            'status' => 'checked_out',
            'actual_check_out' => $data['actual_check_out'] ?? Carbon::now(),
        ]);

        // Update unit status
        if ($reservation->unit) {
            $reservation->unit->update(['status' => 'dirty']);
        }

        // Create check-out record
        $this->repository->createCheckOutRecord($reservation, $data);
    }

    public function cancelReservation(Reservation $reservation): void
    {
        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => Carbon::now(),
            'cancelled_by' => auth()->id(),
        ]);

        event(new ReservationCancelled($reservation));
    }

    public function markNoShow(Reservation $reservation): void
    {
        $reservation->update([
            'status' => 'no_show',
            'no_show_at' => Carbon::now(),
        ]);

        // Apply no-show charges if configured
        $this->applyNoShowCharges($reservation);
    }

    public function extendReservation(Reservation $reservation, int $extensionDays): void
    {
        $newCheckOut = Carbon::parse($reservation->check_out)->addDays($extensionDays);
        
        $reservation->update([
            'check_out' => $newCheckOut,
            'extended' => true,
        ]);

        // Create extension record
        $this->repository->createExtensionRecord($reservation, $extensionDays);
    }

    public function transferReservation(Reservation $reservation, int $newUnitId): void
    {
        $oldUnitId = $reservation->unit_id;
        
        $reservation->update(['unit_id' => $newUnitId]);

        // Create transfer record
        $this->repository->createTransferRecord($reservation, $oldUnitId, $newUnitId);

        // Update unit statuses
        $this->handleUnitTransfer($oldUnitId, $newUnitId);
    }

    public function getArrivals(Team $team, array $filters): Collection
    {
        return $this->repository->getArrivalsQuery($team, $filters)
            ->with(['customer', 'unit'])
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getDepartures(Team $team, array $filters): Collection
    {
        return $this->repository->getDeparturesQuery($team, $filters)
            ->with(['customer', 'unit'])
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getInHouseGuests(Team $team, array $filters): Collection
    {
        return $this->repository->getInHouseQuery($team, $filters)
            ->with(['customer', 'unit', 'guests'])
            ->paginate($filters['per_page'] ?? 20);
    }

    public function bulkActions(array $ids, string $action): void
    {
        $reservations = Reservation::whereIn('id', $ids)->get();

        foreach ($reservations as $reservation) {
            switch ($action) {
                case 'cancel':
                    $this->cancelReservation($reservation);
                    break;
                case 'no_show':
                    $this->markNoShow($reservation);
                    break;
                case 'confirm':
                    $reservation->update(['status' => 'confirmed']);
                    break;
            }
        }
    }

    protected function generateReservationNumber(Team $team): string
    {
        return $this->repository->generateReservationNumber($team);
    }

    protected function calculateTotalAmount(array $data): float
    {
        // Calculate based on rate code and nights
        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);
        $nights = $checkOut->diffInDays($checkIn);
        
        $ratePerNight = $data['rate_per_night'] ?? 0;
        
        return $ratePerNight * $nights;
    }

    protected function syncGuests(Reservation $reservation, array $guests): void
    {
        $this->repository->syncGuests($reservation, $guests);
    }

    protected function handleUnitChange(Reservation $reservation, $oldUnitId): void
    {
        // Update old unit status
        if ($oldUnitId) {
            Unit::where('id', $oldUnitId)->update(['status' => 'dirty']);
        }
    }

    protected function handleUnitTransfer($oldUnitId, $newUnitId): void
    {
        Unit::where('id', $oldUnitId)->update(['status' => 'dirty']);
        Unit::where('id', $newUnitId)->update(['status' => 'occupied']);
    }

    protected function applyNoShowCharges(Reservation $reservation): void
    {
        // Apply charges based on team configuration
        $this->repository->createNoShowCharge($reservation);
    }

    protected function calculateBalance(Reservation $reservation): array
    {
        return $this->repository->calculateBalance($reservation);
    }
}
