<?php

namespace Database\Seeders;

use App\User;
use App\Reservation;
use App\Models\Guest;
use App\Models\Room;
use App\Models\DashboardKpi;
use App\Models\QuickStat;
use App\Models\RecentActivity;
use App\Models\DailyReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ProductionParitySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Ensure Admin User
        $user = User::firstOrCreate(
            ['email' => 'admin@fandaqah.pms'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role_id' => 1,
            ]
        );

        // 2. Populate Dashboard
        $this->seedDashboard();

        // 3. Populate Guests
        $this->seedGuests();

        // 4. Populate Inventory
        $this->seedInventory();

        // 5. Populate Reservations
        $this->seedReservations();

        Schema::enableForeignKeyConstraints();
    }

    private function seedDashboard()
    {
        DashboardKpi::truncate();
        $kpis = [
            ['key' => 'total_revenue', 'label' => 'Total Revenue', 'value' => '245,000 SR', 'trend' => '+15%', 'icon' => 'DollarSign', 'color' => '#e95a54'],
            ['key' => 'occupancy', 'label' => 'Occupancy', 'value' => '78%', 'trend' => '+4%', 'icon' => 'Bed', 'color' => '#2a273c'],
            ['key' => 'adr', 'label' => 'ADR', 'value' => '420 SR', 'trend' => '-2%', 'icon' => 'TrendingUp', 'color' => '#8f9793'],
            ['key' => 'revpar', 'label' => 'RevPAR', 'value' => '328 SR', 'trend' => '+10%', 'icon' => 'Activity', 'color' => '#fbcdab'],
        ];
        foreach ($kpis as $k) DashboardKpi::create($k);

        QuickStat::truncate();
        $stats = [
            ['label' => 'Arriving', 'value' => '14', 'icon' => 'LogIn'],
            ['label' => 'In House', 'value' => '52', 'icon' => 'Home'],
            ['label' => 'Departing', 'value' => '9', 'icon' => 'LogOut'],
            ['label' => 'Dirty Rooms', 'value' => '5', 'icon' => 'Trash2'],
        ];
        foreach ($stats as $s) QuickStat::create($s);

        RecentActivity::truncate();
        $activities = [
            ['type' => 'reservation', 'description' => 'New reservation #RES-4412 created by System'],
            ['type' => 'checkin', 'description' => 'Guest Sample Guest 5 checked in to Room 104'],
            ['type' => 'payment', 'description' => 'Payment of 1,200 SR received for #RES-4401'],
            ['type' => 'checkout', 'description' => 'Guest Sample Guest 2 checked out from Room 201'],
        ];
        foreach ($activities as $a) RecentActivity::create($a);

        DailyReport::truncate();
        for ($i = 14; $i >= 0; $i--) {
            DailyReport::create([
                'report_date' => Carbon::today()->subDays($i),
                'total_revenue' => rand(5000, 15000),
                'occupied_rooms' => rand(10, 20),
                'adr' => rand(300, 600),
                'revpar' => rand(200, 500),
            ]);
        }
    }

    private function seedGuests()
    {
        Guest::truncate();
        for ($i = 1; $i <= 30; $i++) {
            Guest::create([
                'name' => "Sample Guest $i",
                'email' => "guest$i@example.com",
                'phone' => "+9665000000$i",
            ]);
        }
    }

    private function seedInventory()
    {
        Room::truncate();
        for ($i = 101; $i <= 110; $i++) {
            Room::create([
                'number' => (string)$i,
                'floor' => '1',
                'status' => 'available',
                'room_type_id' => 1,
            ]);
        }
    }

    private function seedReservations()
    {
        Reservation::truncate();
        $guests = Guest::pluck('id')->toArray();
        $rooms = Room::pluck('id')->toArray();

        for ($i = 1; $i <= 30; $i++) {
            Reservation::create([
                'code' => 'RES-' . rand(1000, 9999),
                'guest_id' => $guests[array_rand($guests)],
                'room_id' => $rooms[array_rand($rooms)],
                'check_in' => Carbon::today()->addDays(rand(-5, 5)),
                'check_out' => Carbon::today()->addDays(rand(6, 15)),
                'status' => 'confirmed',
                'total_price' => rand(1000, 5000),
                'stay_type' => 'checkin',
                'team_id' => 1
            ]);
        }
    }
}
