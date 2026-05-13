<?php

namespace App\Services;

use App\Reservation;
use App\Guest;
use App\Customer;
use App\DigitalSignature;
use App\IptvGuestNeed;
use App\ReservationGuests;
use App\Models\EarlyLateChargeConfig; // Updated namespace
use App\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class FrontDeskService
{
    /**
     * Process check-in for a reservation
     *
     * @param int $reservationId
     * @param array $data
     * @return array
     */
    public function processCheckIn($reservationId, $data = [])
    {
        DB::beginTransaction();

        try {
            $reservation = Reservation::findOrFail($reservationId);

            // Validate reservation status
            if ($reservation->status !== Reservation::STATUS_CONFIRMED) {
                throw new \Exception('Reservation must be confirmed to check in');
            }

            if ($reservation->checked_in) {
                throw new \Exception('Reservation already checked in');
            }

            // Update check-in time
            $reservation->checked_in = Carbon::now();
            $reservation->action_type = Reservation::ACTION_CHECKEDIN;

            // Handle early check-in charges if applicable
            $checkInTime = Carbon::parse($data['check_in_time'] ?? now());
            $globalCheckInTime = Carbon::parse($reservation->date_in . ' 00:00:00'); // This would come from settings
            
            if ($checkInTime->lt($globalCheckInTime)) {
                $this->handleEarlyCheckInCharges($reservation, $checkInTime);
            }

            $reservation->save();

            // Store digital signature if provided
            if (isset($data['digital_signature'])) {
                $this->storeDigitalSignature($reservation->id, $data['digital_signature'], DigitalSignature::TYPE_RESERVATION_USER);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Check-in completed successfully',
                'reservation' => $reservation
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process check-out for a reservation
     *
     * @param int $reservationId
     * @param array $data
     * @return array
     */
    public function processCheckOut($reservationId, $data = [])
    {
        DB::beginTransaction();

        try {
            $reservation = Reservation::findOrFail($reservationId);

            if (!$reservation->checked_in) {
                throw new \Exception('Cannot checkout a reservation that has not been checked in');
            }

            if ($reservation->checked_out) {
                throw new \Exception('Reservation already checked out');
            }

            // Update check-out time
            $reservation->checked_out = Carbon::now();
            $reservation->action_type = Reservation::ACTION_CHECKEDOUT;

            // Handle late checkout charges if applicable
            $checkOutTime = Carbon::parse($data['check_out_time'] ?? now());
            $expectedCheckOutTime = Carbon::parse($reservation->date_out . ' 23:59:59'); // This would come from settings

            if ($checkOutTime->gt($expectedCheckOutTime)) {
                $this->handleLateCheckoutCharges($reservation, $checkOutTime);
            }

            $reservation->save();

            // Store digital signature if provided
            if (isset($data['digital_signature'])) {
                $this->storeDigitalSignature($reservation->id, $data['digital_signature'], DigitalSignature::TYPE_RESERVATION_USER);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Check-out completed successfully',
                'reservation' => $reservation
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Add a guest to a reservation
     *
     * @param int $reservationId
     * @param array $guestData
     * @return array
     */
    public function addGuestToReservation($reservationId, $guestData)
    {
        try {
            $reservation = Reservation::findOrFail($reservationId);

            // Validate guest data
            $validator = Guest::validate($guestData, null, $reservation->customer_id);
            if ($validator->fails()) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ];
            }

            // Create guest
            $guest = new Guest();
            $guest->fill($guestData);
            $guest->reservation_id = $reservationId;
            $guest->customer_id = $reservation->customer_id;
            $guest->save();

            // Link guest to reservation through pivot
            $reservation->reservation_guests()->attach($guest->id);

            return [
                'success' => true,
                'message' => 'Guest added successfully',
                'guest' => $guest
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update guest information
     *
     * @param int $guestId
     * @param array $guestData
     * @return array
     */
    public function updateGuest($guestId, $guestData)
    {
        try {
            $guest = Guest::findOrFail($guestId);
            
            // Validate guest data
            $validator = Guest::validate($guestData, $guestId, $guest->customer_id);
            if ($validator->fails()) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ];
            }

            $guest->update($guestData);

            return [
                'success' => true,
                'message' => 'Guest updated successfully',
                'guest' => $guest
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete a guest
     *
     * @param int $guestId
     * @return array
     */
    public function deleteGuest($guestId)
    {
        try {
            $guest = Guest::findOrFail($guestId);
            $guest->delete();

            return [
                'success' => true,
                'message' => 'Guest deleted successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate Shomoos ID against external service
     *
     * @param string $shomoosId
     * @return array
     */
    public function validateShomoosId($shomoosId)
    {
        try {
            // In a real implementation, this would call an external service
            // For now, we'll simulate the response
            $response = Http::get(config('services.shomoos.base_url') . "/verify/{$shomoosId}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'valid' => $data['valid'],
                    'details' => $data['details'] ?? []
                ];
            } else {
                return [
                    'success' => false,
                    'valid' => false,
                    'message' => 'Failed to verify ID with external service'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'valid' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle early check-in charges
     *
     * @param Reservation $reservation
     * @param Carbon $checkInTime
     * @return void
     */
    private function handleEarlyCheckInCharges($reservation, $checkInTime)
    {
        $configs = EarlyLateChargeConfig::where('team_id', $reservation->team_id)
            ->where('charge_type', 'early_checkin')
            ->where('is_active', true)
            ->get();

        foreach ($configs as $config) {
            // Calculate the difference in hours
            $hoursDifference = $checkInTime->diffInHours(Carbon::parse($reservation->date_in));
            
            if ($hoursDifference >= $config->tier_from_hour && $hoursDifference <= $config->tier_to_hour) {
                // Apply charge based on rate type
                $this->applyCharge($reservation, $config);
                break;
            }
        }
    }

    /**
     * Handle late checkout charges
     *
     * @param Reservation $reservation
     * @param Carbon $checkOutTime
     * @return void
     */
    private function handleLateCheckoutCharges($reservation, $checkOutTime)
    {
        $configs = EarlyLateChargeConfig::where('team_id', $reservation->team_id)
            ->where('charge_type', 'late_checkout')
            ->where('is_active', true)
            ->get();

        foreach ($configs as $config) {
            // Calculate the difference in hours
            $expectedCheckOut = Carbon::parse($reservation->date_out . ' 23:59:59');
            $hoursDifference = $expectedCheckOut->diffInHours($checkOutTime, false); // Negative if late
            
            if ($hoursDifference < 0) {
                $hoursDifference = abs($hoursDifference);
                if ($hoursDifference >= $config->tier_from_hour && $hoursDifference <= $config->tier_to_hour) {
                    // Apply charge based on rate type
                    $this->applyCharge($reservation, $config);
                    break;
                }
            }
        }
    }

    /**
     * Apply a charge to a reservation
     *
     * @param Reservation $reservation
     * @param object $config
     * @return void
     */
    private function applyCharge($reservation, $config)
    {
        // Calculate the amount based on the rate type
        $amount = 0;
        
        switch ($config->rate_type) {
            case 'fixed':
                $amount = $config->rate_amount;
                break;
            case 'percentage_first_night':
                // Calculate percentage of first night's rate
                $firstNightRate = $this->getFirstNightRate($reservation);
                $amount = ($firstNightRate * $config->rate_amount) / 100;
                break;
            case 'percentage_nightly_rate':
                // Calculate percentage of average nightly rate
                $avgNightlyRate = $this->getAvgNightlyRate($reservation);
                $amount = ($avgNightlyRate * $config->rate_amount) / 100;
                break;
        }

        // Here you would typically create a transaction for the additional charge
        // This is a simplified approach - actual implementation would depend on your billing system
        $reservation->total_price += $amount;
        $reservation->save();
    }

    /**
     * Get the rate for the first night of a reservation
     *
     * @param Reservation $reservation
     * @return float
     */
    private function getFirstNightRate($reservation)
    {
        if (is_array($reservation->prices) && isset($reservation->prices['days'][0]['price'])) {
            return $reservation->prices['days'][0]['price'];
        }
        
        // Fallback calculation
        return $reservation->sub_total / $reservation->nights;
    }

    /**
     * Get the average nightly rate of a reservation
     *
     * @param Reservation $reservation
     * @return float
     */
    private function getAvgNightlyRate($reservation)
    {
        if ($reservation->nights > 0) {
            return $reservation->sub_total / $reservation->nights;
        }
        
        return 0;
    }

    /**
     * Store a digital signature for a reservation
     *
     * @param int $reservationId
     * @param string $signature
     * @param string $type
     * @return void
     */
    private function storeDigitalSignature($reservationId, $signature, $type)
    {
        DigitalSignature::create([
            'team_id' => auth()->user()->current_team_id,
            'ref_id' => $reservationId,
            'type' => $type,
            'signature_base64' => base64_encode(gzcompress($signature)),
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Create a walk-in booking
     *
     * @param array $bookingData
     * @return array
     */
    public function createWalkInBooking($bookingData)
    {
        DB::beginTransaction();

        try {
            // Validate availability
            $availableUnit = Unit::whereDoesntHave('reservations', function($q) use ($bookingData) {
                $q->where(function($subQ) use ($bookingData) {
                    $subQ->where('date_in', '<=', $bookingData['date_out'])
                         ->where('date_out', '>=', $bookingData['date_in']);
                })
                ->where('status', Reservation::STATUS_CONFIRMED)
                ->whereNull('checked_out');
            })
            ->where('id', $bookingData['unit_id'])
            ->first();

            if (!$availableUnit) {
                throw new \Exception('Selected unit is not available for the specified dates');
            }

            // Create customer if doesn't exist
            $customer = null;
            if (isset($bookingData['customer']['id'])) {
                $customer = Customer::find($bookingData['customer']['id']);
            } else {
                $customer = Customer::create($bookingData['customer']);
            }

            // Create reservation
            $reservation = new Reservation();
            $reservation->fill($bookingData);
            $reservation->customer_id = $customer->id;
            $reservation->status = Reservation::STATUS_CONFIRMED;
            $reservation->checked_in = Carbon::now();
            $reservation->action_type = Reservation::ACTION_CHECKEDIN;
            $reservation->save();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Walk-in booking created successfully',
                'reservation' => $reservation
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Extend a reservation for same-day
     *
     * @param int $reservationId
     * @param array $extensionData
     * @return array
     */
    public function extendSameDay($reservationId, $extensionData)
    {
        DB::beginTransaction();

        try {
            $reservation = Reservation::findOrFail($reservationId);

            if (!$reservation->checked_in) {
                throw new \Exception('Can only extend reservations that are checked in');
            }

            if ($reservation->checked_out) {
                throw new \Exception('Cannot extend a reservation that is already checked out');
            }

            // Check unit availability for extension
            $existingReservation = Reservation::where('unit_id', $reservation->unit_id)
                ->where('id', '!=', $reservationId)
                ->where(function($q) use ($extensionData) {
                    $q->where(function($subQ) use ($extensionData, $reservation) {
                        $subQ->where('date_in', '<=', $extensionData['new_date_out'])
                             ->where('date_out', '>=', $reservation->date_out);
                    });
                })
                ->where('status', Reservation::STATUS_CONFIRMED)
                ->whereNull('checked_out')
                ->first();

            if ($existingReservation) {
                throw new \Exception('Unit not available for extension');
            }

            // Update reservation date_out
            $reservation->date_out = $extensionData['new_date_out'];
            $reservation->save();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Reservation extended successfully',
                'reservation' => $reservation
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Assign a room to a reservation
     *
     * @param int $reservationId
     * @param int $unitId
     * @return array
     */
    public function assignRoom($reservationId, $unitId)
    {
        try {
            $reservation = Reservation::findOrFail($reservationId);

            // Check if unit is available for the reservation period
            $existingReservation = Reservation::where('unit_id', $unitId)
                ->where('id', '!=', $reservationId)
                ->where(function($q) use ($reservation) {
                    $q->where(function($subQ) use ($reservation) {
                        $subQ->where('date_in', '<=', $reservation->date_out)
                             ->where('date_out', '>=', $reservation->date_in);
                    });
                })
                ->where('status', Reservation::STATUS_CONFIRMED)
                ->whereNull('checked_out')
                ->first();

            if ($existingReservation) {
                return [
                    'success' => false,
                    'message' => 'Selected room is not available for the reservation dates'
                ];
            }

            $reservation->unit_id = $unitId;
            $reservation->save();

            return [
                'success' => true,
                'message' => 'Room assigned successfully',
                'reservation' => $reservation
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle no-show for a reservation
     *
     * @param int $reservationId
     * @return array
     */
    public function handleNoShow($reservationId)
    {
        DB::beginTransaction();

        try {
            $reservation = Reservation::findOrFail($reservationId);

            if ($reservation->checked_in) {
                throw new \Exception('Cannot mark as no-show a reservation that is already checked in');
            }

            // Update reservation status
            $reservation->status = Reservation::STATUS_CANCELED;
            $reservation->action_type = Reservation::ACTION_CANCELED;
            $reservation->save();

            // Apply no-show charges if configured
            $this->applyNoShowCharges($reservation);

            DB::commit();

            return [
                'success' => true,
                'message' => 'No-show handled successfully',
                'reservation' => $reservation
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Apply no-show charges to a reservation
     *
     * @param Reservation $reservation
     * @return void
     */
    private function applyNoShowCharges($reservation)
    {
        // Implementation would depend on business rules for no-show charges
        // This could involve applying penalties based on reservation value or nights
    }

    /**
     * Create an IPTV guest need/wake-up call
     *
     * @param array $requestData
     * @return array
     */
    public function createIptvRequest($requestData)
    {
        try {
            $iptvRequest = IptvGuestNeed::create([
                'reservation_id' => $requestData['reservation_id'],
                'request_type' => $requestData['request_type'],
                'request_details' => $requestData['request_details'] ?? null,
                'priority' => $requestData['priority'] ?? 'normal',
                'team_id' => auth()->user()->current_team_id,
                'requested_at' => Carbon::now(),
            ]);

            return [
                'success' => true,
                'message' => 'IPTV request created successfully',
                'request' => $iptvRequest
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Mark an IPTV request as treated
     *
     * @param int $requestId
     * @param int $treatedBy
     * @return array
     */
    public function markIptvRequestAsTreated($requestId, $treatedBy)
    {
        try {
            $iptvRequest = IptvGuestNeed::findOrFail($requestId);
            
            $iptvRequest->update([
                'is_treated' => 1,
                'treated_at' => Carbon::now(),
                'treated_by' => $treatedBy,
            ]);

            return [
                'success' => true,
                'message' => 'IPTV request marked as treated',
                'request' => $iptvRequest
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}