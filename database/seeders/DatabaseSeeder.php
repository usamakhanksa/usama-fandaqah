<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\CompanyProfile;
use App\Models\Country;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Room;
use App\Models\RoomFloor;
use App\Models\Unit;
use App\Models\UnitType;
use App\Models\UnitStatus;
use App\Models\Lead;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Countries & Cities
        $scountry = [
            'name' => 'Saudi Arabia',
            'title' => json_encode(['en' => 'Saudi Arabia', 'ar' => 'المملكة العربية السعودية']),
            'code' => 'SA',
            'phone_code' => '966',
            'iso2' => 'SA',
            'updated_at' => now(),
            'created_at' => now(),
        ];
        DB::table('countries')->updateOrInsert(['iso2' => 'SA'], $scountry);
        $sa = DB::table('countries')->where('iso2', 'SA')->first();

        foreach ([['Qatar', 'QA', '974'], ['United Arab Emirates', 'AE', '971']] as $c) {
            DB::table('countries')->updateOrInsert(['iso2' => $c[1]], [
                'name' => $c[0],
                'title' => json_encode(['en' => $c[0], 'ar' => $c[0]]),
                'code' => $c[1],
                'phone_code' => $c[2],
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }

        foreach (['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam', 'Khobar', 'Ad-Dilam'] as $cityName) {
            DB::table('cities')->updateOrInsert(['name' => $cityName, 'country_id' => $sa->id], [
                'title' => json_encode(['en' => $cityName, 'ar' => $cityName]),
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }

        // 2. Permissions
        $modules = ['Dashboard', 'Rooms', 'Guests', 'Reservations', 'Finances', 'Reports', 'Settings', 'Users', 'POS'];
        $actions = ['view', 'create', 'edit', 'delete', 'export'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                DB::table('permissions')->updateOrInsert(
                    ['slug' => Str::slug($module . ' ' . $action)],
                    [
                        'name' => $module . ' ' . ucfirst($action), 
                        'group' => $module, 
                        'module' => $module,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        // 3. Roles
        $roleMap = [
            'super-admin' => 'Super Admin',
            'manager' => 'Manager',
            'receptionist' => 'Receptionist'
        ];

        foreach ($roleMap as $slug => $name) {
            DB::table('roles')->updateOrInsert(['slug' => $slug], ['name' => $name, 'updated_at' => now(), 'created_at' => now()]);
        }

        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $allPermissions = Permission::all();
        
        $pivotData = [];
        foreach ($allPermissions as $p) {
            $pivotData[$p->id] = [
                'enabled' => true, 'anyone' => true, 'can_view' => true, 'can_create' => true, 'can_edit' => true, 'can_remove' => true
            ];
        }
        $superAdminRole->permissions()->sync($pivotData);

        // 4. Users
        $admin = User::updateOrCreate(['email' => 'admin@fandaqah.com'], [
            'name' => 'Aya Ahmed Abdullah',
            'role_id' => $superAdminRole->id,
            'password' => Hash::make('password'),
            'avatar' => '/assets/avatars/admin.svg',
        ]);
        $admin->roles()->sync([$superAdminRole->id]);

        // 5. Room Floors & Rooms
        DB::table('room_types')->updateOrInsert(['id' => 1], ['name' => 'General', 'base_price' => 100, 'created_at' => now(), 'updated_at' => now()]);
        
        for ($i = 1; $i <= 5; $i++) {
            $floorId = DB::table('room_floors')->updateOrInsert(['level' => $i], ['name' => "Floor $i", 'created_at' => now(), 'updated_at' => now()]);
            $floor = DB::table('room_floors')->where('level', $i)->first();
            
            for ($j = 1; $j <= 5; $j++) {
                $roomNum = ($i * 100) + $j;
                DB::table('rooms')->updateOrInsert(['number' => (string)$roomNum], [
                    'room_type_id' => 1,
                    'floor' => (string)$i,
                    'price_per_day' => rand(200, 600),
                    'status' => 'available',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 6. Unit Types & Statuses
        foreach (['Single', 'Double', 'Suite', 'Deluxe'] as $t) {
            DB::table('unit_types')->updateOrInsert(['name' => $t], ['created_at' => now(), 'updated_at' => now()]);
        }

        $statuses = [
            'available' => ['name' => 'Available', 'color' => '#10b981'],
            'occupied' => ['name' => 'Occupied', 'color' => '#ef4444'],
            'dirty' => ['name' => 'Dirty', 'color' => '#f59e0b'],
            'maintenance' => ['name' => 'Maintenance', 'color' => '#6b7280'],
        ];
        foreach ($statuses as $slug => $data) {
            DB::table('unit_statuses')->updateOrInsert(['slug' => $slug], [
                'name' => $data['name'], 
                'color' => $data['color'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 7. Units
        $rooms = DB::table('rooms')->get();
        $floors = DB::table('room_floors')->get();
        $types = DB::table('unit_types')->get();
        $availableStatus = DB::table('unit_statuses')->where('slug', 'available')->first();

        for ($i = 1; $i <= 20; $i++) {
            $room = $rooms->random();
            $floor = $floors->random();
            DB::table('units')->updateOrInsert(['id' => $i], [
                'name' => 'Unit ' . (5000 + $i),
                'unit_number' => (string)(5000 + $i),
                'number' => (string)(5000 + $i),
                'room_id' => $room->id,
                'room_floor_id' => $floor->id,
                'unit_type_id' => $types->random()->id,
                'unit_status_id' => $availableStatus->id,
                'status' => 'active',
                'capacity' => rand(1, 4),
                'beds' => rand(1, 2),
                'baths' => rand(1, 1),
                'thumbnail' => '/assets/avatars/guest1.svg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 8. Guests
        for ($i = 1; $i <= 10; $i++) {
            DB::table('guests')->updateOrInsert(['id' => $i], [
                'name' => fake()->name(),
                'email' => fake()->email(),
                'phone' => '+966' . rand(500000000, 599999999),
                'avatar' => '/assets/avatars/guest1.svg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 9. Leads
        for ($i = 1; $i <= 10; $i++) {
            DB::table('leads')->updateOrInsert(['email' => "lead{$i}@example.com"], [
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'phone' => '+966' . rand(500000000, 599999999),
                'status' => 'new',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 10. Service Categories
        $categories = [
            ['name' => 'food', 'status' => 1, 'order' => 1],
            ['name' => 'brekfast', 'status' => 1, 'order' => 0],
            ['name' => 'laundry', 'status' => 0, 'order' => 0],
            ['name' => 'Dinner', 'status' => 1, 'order' => 0],
            ['name' => 'Lunch', 'status' => 0, 'order' => 0],
            ['name' => 'test', 'status' => 1, 'order' => 0],
            ['name' => 'Breakfast', 'status' => 1, 'order' => 0],
            ['name' => 'Outlet', 'status' => 1, 'order' => 0],
            ['name' => 'other items', 'status' => 1, 'order' => 0],
            ['name' => 'Rest1', 'status' => 1, 'order' => 4],
            ['name' => 'Laundry', 'status' => 1, 'order' => 0],
            ['name' => 'Banquet', 'status' => 1, 'order' => 0],
            ['name' => 'Cafe', 'status' => 1, 'order' => 0],
        ];
        foreach ($categories as $cat) {
            DB::table('service_categories')->updateOrInsert(['name' => json_encode(['en' => $cat['name'], 'ar' => $cat['name']])], [
                'status' => $cat['status'],
                'order' => $cat['order'],
                'show_in_reservation' => 1,
                'show_in_pos' => 1,
                'team_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 11. Reservation Statuses
        foreach (['Confirmed', 'Checked In', 'Checked Out', 'Cancelled', 'No Show'] as $status) {
            DB::table('reservation_statuses')->updateOrInsert(['name' => $status], ['created_at' => now(), 'updated_at' => now()]);
        }

        // 12. Reservations (Bypassing Virtual Columns if any, but using date_in/date_out safely)
        $r_status = DB::table('reservation_statuses')->first();
        for ($i = 1; $i <= 10; $i++) {
          $in = now()->addDays(rand(-5, 5))->toDateString();
          $out = now()->addDays(rand(6, 10))->toDateString();
          DB::table('reservations')->insertOrIgnore([
              'code' => 'RSV' . Str::random(8),
              'guest_id' => rand(1, 10),
              'room_id' => rand(1, 20),
              'unit_id' => rand(1, 20),
              'reservation_status_id' => $r_status?->id ?? 1,
              'status' => 'CheckedIn',
              'check_in' => $in,
              'check_out' => $out,
              'stay_type' => 'checkin',
              'created_at' => now(),
              'updated_at' => now()
          ]);
        }
    }
}
