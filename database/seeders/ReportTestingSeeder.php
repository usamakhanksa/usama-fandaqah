<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Unit;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\Transaction;
use App\Models\Guest;
use App\Models\Source;
use App\Models\ReservationStatus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportTestingSeeder extends Seeder
{
    public function run()
    {
        $teams = Team::all();
        if ($teams->isEmpty()) {
            $this->command->info('No teams found. Please seed teams first.');
            return;
        }

        foreach ($teams as $team) {
            $this->seedTeamData($team);
        }
    }

    private function seedTeamData(Team $team)
    {
        $this->command->info("Seeding report data for team: {$team->name}");

        $units = Unit::where('team_id', $team->id)->whereNotNull('room_id')->get();
        if ($units->isEmpty()) return;

        $sources = Source::all();
        if ($sources->isEmpty()) {
            $sources = collect([
                Source::create(['name' => 'Direct', 'team_id' => $team->id]),
                Source::create(['name' => 'OTA', 'team_id' => $team->id]),
            ]);
        }

        $status = ReservationStatus::first();
        $statusId = $status ? $status->id : null;

        $startDate = Carbon::today()->subDays(90);
        $endDate = Carbon::today();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $occupiedCount = rand(min(1, floor($units->count() * 0.2)), floor($units->count() * 0.5));
            if ($occupiedCount == 0) continue;
            
            $selectedUnits = $units->random($occupiedCount);

            foreach ($selectedUnits as $unit) {
                try {
                    $guest = Guest::where('team_id', $team->id)->inRandomOrder()->first() ?? Guest::factory()->create(['team_id' => $team->id]);

                    $checkIn = $date->copy();
                    $checkOut = $date->copy()->addDays(rand(1, 5));

                    $res = DB::table('reservations')->insertGetId([
                        'team_id' => $team->id,
                        'code' => 'TEST-' . strtoupper(bin2hex(random_bytes(3))),
                        'guest_id' => $guest->id,
                        'unit_id' => $unit->id,
                        'room_id' => $unit->room_id,
                        'status' => 'checked-in',
                        'reservation_status_id' => $statusId,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'source_id' => $sources->random()->id,
                        'reservation_category_type' => collect(['walk-in', 'corporate', 'leisure'])->random(),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    // Seed revenue
                    $amount = rand(200, 1000);
                    Transaction::create([
                        'team_id' => $team->id,
                        'payable_id' => $res,
                        'payable_type' => Reservation::class,
                        'kind' => 'payment',
                        'type' => 'deposit',
                        'amount' => $amount,
                        'created_at' => $date,
                        'meta' => [
                            'payment_type' => collect(['cash', 'card'])->random(),
                            'date' => $date->toDateString(),
                            'category' => 'reservation'
                        ]
                    ]);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
    }
}
