<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Reservation;
use App\Models\Source;
use App\Models\Unit;
use App\Models\UnitStatus;
use App\Models\UnitMaintenance;
use App\Models\CommissionPayment;
use App\Models\CommissionPaymentDetail;
use App\Models\CheckOutRecord;
use App\Models\Guest;
use App\Models\ReservationStatus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportSpecializedSeeder extends Seeder
{
    /**
     * Seed specialized report data for all reports:
     * - No-Show reservations
     * - Cancellation data
     * - Commission payments
     * - Housekeeping discrepancies
     * - Forecast data (future reservations)
     */
    public function run(): void
    {
        $teams = Team::all();
        if ($teams->isEmpty()) {
            $this->command->info('No teams found. Skipping report data seeding.');
            return;
        }

        foreach ($teams as $team) {
            $this->command->info("Seeding specialized report data for team: {$team->name}");
            $this->seedForTeam($team);
        }
    }

    private function seedForTeam($team)
    {
        $startDate = Carbon::today()->subDays(60);
        $endDate = Carbon::today()->addDays(60);

        // Get or create default reservation status
        $defaultStatus = ReservationStatus::first();
        if (!$defaultStatus) {
            $defaultStatus = ReservationStatus::create(['name' => 'Default']);
        }

        // Seed sources with commission rates (for commission report)
        $this->seedSources($team);

        // Seed no-show reservations
        $this->seedNoShowReservations($team, $startDate, $endDate, $defaultStatus->id);

        // Seed cancellation data
        $this->seedCancellations($team, $startDate, $endDate, $defaultStatus->id);

        // Seed commission payments
        $this->seedCommissionPayments($team, $startDate, $endDate);

        // Seed housekeeping discrepancies
        $this->seedHousekeepingDiscrepancies($team);

        // Seed future reservations for forecast
        $this->seedFutureReservations($team, $endDate, $defaultStatus->id);

        // Seed paid-outs
        $this->seedPaidOuts($team);

        // Seed turnaway logs
        $this->seedTurnaways($team);

        // Seed source performance data is already covered by previous seeds (sources and reservations)

        // Seed company AR data
        $this->seedCompanyAr($team);

        // Seed trial balance (transactions)
        $this->seedTrialBalanceData($team);
    }

    private function seedSources($team)
    {
        $sources = [
            ['name' => 'Booking.com', 'commission_rate' => 15.00, 'is_travel_agent' => true],
            ['name' => 'Expedia', 'commission_rate' => 12.50, 'is_travel_agent' => true],
            ['name' => 'Direct Booking', 'commission_rate' => 0.00, 'is_travel_agent' => false],
            ['name' => 'Travel Agency XYZ', 'commission_rate' => 20.00, 'is_travel_agent' => true],
            ['name' => 'Airline Partners', 'commission_rate' => 18.00, 'is_travel_agent' => true],
        ];

        foreach ($sources as $sourceData) {
            Source::updateOrCreate(
                ['name' => $sourceData['name'], 'team_id' => $team->id],
                array_merge($sourceData, ['team_id' => $team->id])
            );
        }
    }

    private function seedNoShowReservations($team, $startDate, $endDate, $statusId)
    {
        $units = Unit::where('team_id', $team->id)->whereNotNull('room_id')->take(20)->get();
        if ($units->isEmpty()) return;

        $sources = Source::where('team_id', $team->id)->get();

        $noShowDates = collect();
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            if (rand(1, 20) === 1) {
                $noShowDates->push($current->copy());
            }
            $current->addDay();
        }
        $noShowDates = $noShowDates->take(15);

        foreach ($noShowDates as $date) {
            $unit = $units->random();
            $source = $sources->random();
            $guest = $this->getOrCreateGuest($team);

            $checkIn = $date->copy();
            $checkOut = $date->copy()->addDays(rand(1, 5));
            $totalAmount = rand(300, 1500);
            $noShowCharge = rand(0, (int)($totalAmount * 0.5));

            Reservation::create([
                'team_id' => $team->id,
                'code' => 'NOSHOW-' . strtoupper(bin2hex(random_bytes(3))),
                'guest_id' => $guest->id,
                'unit_id' => $unit->id,
                'room_id' => $unit->room_id,
                'reservation_status_id' => $statusId,
                'status' => 'no-show',
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'source_id' => $source->id,
                'total_amount' => $totalAmount,
                'room_revenue' => $totalAmount,
                'no_show_charge' => $noShowCharge,
                'noshow_flag' => true,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }
    }

    private function seedCancellations($team, $startDate, $endDate, $statusId)
    {
        $units = Unit::where('team_id', $team->id)->whereNotNull('room_id')->take(20)->get();
        if ($units->isEmpty()) return;

        $sources = Source::where('team_id', $team->id)->get();
        $reasons = ['Change of plans', 'Found better deal', 'Health issues', 'Weather concerns', 'Visa issues', 'Business conflict'];

        $cancelDates = collect();
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            if (rand(1, 10) === 1) {
                $cancelDates->push($current->copy());
            }
            $current->addDay();
        }
        $cancelDates = $cancelDates->take(20);

        foreach ($cancelDates as $createdDate) {
            $unit = $units->random();
            $source = $sources->random();
            $guest = $this->getOrCreateGuest($team);

            $checkIn = $createdDate->copy()->addDays(rand(3, 30));
            $checkOut = $checkIn->copy()->addDays(rand(1, 5));
            $totalAmount = rand(400, 2000);
            $refundAmount = rand(0, $totalAmount);

            Reservation::create([
                'team_id' => $team->id,
                'code' => 'CANCEL-' . strtoupper(bin2hex(random_bytes(3))),
                'guest_id' => $guest->id,
                'unit_id' => $unit->id,
                'room_id' => $unit->room_id,
                'reservation_status_id' => $statusId,
                'status' => 'cancelled',
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'source_id' => $source->id,
                'total_amount' => $totalAmount,
                'room_revenue' => $totalAmount,
                'refund_amount' => $refundAmount,
                'cancellation_reason' => $reasons[array_rand($reasons)],
                'cancelled_at' => $createdDate,
                'updated_at' => $createdDate,
                'created_at' => $createdDate->subDays(rand(5, 15)),
            ]);
        }
    }

    private function seedCommissionPayments($team, $startDate, $endDate)
    {
        $agents = Source::where('team_id', $team->id)->where('is_travel_agent', true)->take(5)->get();
        if ($agents->isEmpty()) return;

        $periods = [
            ['from' => Carbon::today()->startOfMonth(), 'to' => Carbon::today()->endOfMonth()],
            ['from' => Carbon::today()->subMonth()->startOfMonth(), 'to' => Carbon::today()->subMonth()->endOfMonth()],
        ];

        foreach ($periods as $period) {
            foreach ($agents as $agent) {
                // Find or create a Company for the travel agent
                $company = \App\Models\Company::firstOrCreate(
                    ['name' => $agent->name, 'team_id' => $team->id],
                    [
                        'entity_type' => 'travel_agent',
                        'phone' => '0000000000',
                        'email' => 'agent@example.com',
                    ]
                );

                $payment = CommissionPayment::create([
                    'team_id' => $team->id,
                    'travel_agent_id' => $company->id,
                    'commission_period_from' => $period['from'],
                    'commission_period_to' => $period['to'],
                    'payment_number' => 'PAY-' . strtoupper(bin2hex(random_bytes(4))),
                    'total_commission' => rand(1000, 5000),
                    'total_paid' => rand(0, 5000),
                    'payment_method' => collect(['bank_transfer', 'cash', 'cheque'])->random(),
                    'status' => collect(['pending', 'partial', 'paid'])->random(),
                    'created_by' => \App\Models\User::first()->id ?? 1,
                ]);

                $reservations = Reservation::where('team_id', $team->id)
                    ->where('source_id', $agent->id)
                    ->whereDate('check_in', '>=', $period['from'])
                    ->whereDate('check_in', '<=', $period['to'])
                    ->take(5)
                    ->get();

                foreach ($reservations as $res) {
                    CommissionPaymentDetail::create([
                        'commission_payment_id' => $payment->id,
                        'reservation_id' => $res->id,
                        'commission_rate' => $agent->commission_rate,
                        'commission_amount' => rand(50, 200),
                        'room_revenue' => $res->total_amount ?? 300,
                        'fb_revenue' => 0,
                        'other_revenue' => 0,
                    ]);
                }
            }
        }
    }

    private function seedHousekeepingDiscrepancies($team)
    {
        $units = Unit::where('team_id', $team->id)->whereNotNull('room_id')->take(30)->get();
        if ($units->isEmpty()) return;

        $statuses = ['clean', 'dirty', 'cleaning', 'occupied', 'vacant'];
        foreach ($statuses as $status) {
            UnitStatus::firstOrCreate(['name' => $status]);
        }

        $maintenanceCount = min(10, $units->count());
        $maintenanceUnits = $units->random($maintenanceCount);

        foreach ($maintenanceUnits as $unit) {
            UnitMaintenance::create([
                'team_id' => $team->id,
                'unit_id' => $unit->id,
                'start_at' => Carbon::today()->subDays(rand(1, 10)),
                'expected_at' => Carbon::today()->addDays(rand(5, 20)),
                'note' => collect(['AC not working', 'Plumbing issue', 'Furniture repair', 'Deep cleaning needed'])->random(),
                'created_by' => 1,
            ]);
        }

        $checkOutCount = min(15, $units->count());
        $checkoutUnits = $units->random($checkOutCount);

        foreach ($checkoutUnits as $unit) {
            CheckOutRecord::create([
                'team_id' => $team->id,
                'unit_id' => $unit->id,
                'reservation_id' => null,
                'created_at' => Carbon::today()->subHours(rand(5, 48)),
                'updated_at' => Carbon::today()->subHours(rand(5, 48)),
            ]);
        }

        $mismatchedUnits = $units->random(min(10, $units->count()));
        $dirtyStatus = UnitStatus::where('name', 'dirty')->first();
        if ($dirtyStatus) {
            foreach ($mismatchedUnits as $unit) {
                $unit->unit_status_id = $dirtyStatus->id;
                $unit->status = 'occupied';
                $unit->save();
            }
        }
    }

    private function seedFutureReservations($team, $endDate, $statusId)
    {
        $units = Unit::where('team_id', $team->id)->whereNotNull('room_id')->get();
        if ($units->isEmpty()) return;

        $sources = Source::where('team_id', $team->id)->get();

        $futureStart = Carbon::today()->addDays(1);
        $futureEnd = $endDate->copy()->addDays(90);

        $dates = collect();
        $current = $futureStart->copy();
        while ($current->lte($futureEnd)) {
            $dates->push($current->copy());
            $current->addDay();
        }

        $reservationsToCreate = min(100, $dates->count() * 2);

        for ($i = 0; $i < $reservationsToCreate; $i++) {
            $date = $dates->random();
            $unit = $units->random();
            $source = $sources->random();
            $guest = $this->getOrCreateGuest($team);

            $checkIn = $date->copy();
            $checkOut = $date->copy()->addDays(rand(1, 7));
            $totalAmount = rand(300, 2000);

            Reservation::create([
                'team_id' => $team->id,
                'code' => 'FCST-' . strtoupper(bin2hex(random_bytes(3))),
                'guest_id' => $guest->id,
                'unit_id' => $unit->id,
                'room_id' => $unit->room_id,
                'reservation_status_id' => $statusId,
                'status' => 'confirmed',
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'source_id' => $source->id,
                'total_amount' => $totalAmount,
                'room_revenue' => $totalAmount,
                'created_at' => Carbon::today()->subDays(rand(1, 30)),
                'updated_at' => Carbon::today(),
            ]);
        }
    }

    private function getOrCreateGuest($team)
    {
        $guest = Guest::where('team_id', $team->id)->first();
        if (!$guest) {
            $guest = Guest::create([
                'team_id' => $team->id,
                'name' => 'Test Guest',
                'email' => 'guest@test.com',
                'phone' => '1234567890',
                'nationality' => 'SA',
            ]);
        }
        return $guest;
    }
}
