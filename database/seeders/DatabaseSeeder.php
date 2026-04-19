<?php

namespace Database\Seeders;

use App\Models\CompanyProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['super-admin', 'manager', 'receptionist', 'accountant', 'service-staff'];
        foreach ($roles as $r) {
            DB::table('roles')->insert(['name' => ucwords(str_replace('-', ' ', $r)), 'slug' => $r, 'created_at' => now(), 'updated_at' => now()]);
        }

        DB::table('users')->insert(['role_id' => 1, 'name' => 'Aya Ahmed Abdullah', 'email' => 'aya@hotel.test', 'password' => Hash::make('password'), 'avatar' => '/assets/avatars/admin.svg', 'last_seen_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        for ($u = 2; $u <= 22; $u++) {
            DB::table('users')->insert(['role_id' => rand(1, 5), 'name' => fake()->name(), 'email' => "user{$u}@hotel.test", 'password' => Hash::make('password'), 'avatar' => '/assets/avatars/guest'.(($u % 8) + 1).'.svg', 'last_seen_at' => now()->subHours(rand(0, 30)), 'created_at' => now(), 'updated_at' => now()]);
        }


        foreach ([
            'Reservations Management',
            'Room Inventory',
            'Financial Reports',
            'Bills Processing',
            'Receipts Approval',
            'Expense Review',
            'POS Transactions',
            'Services Management',
            'User Assignment',
            'Dashboard Analytics',
            'Guest Profiles',
            'Company Profiles',
            'Unit Status Control',
            'Check-In Operations',
            'Check-Out Operations',
            'Booking Files',
            'Credit Notes',
            'Fund Movement Reports',
            'Audit Logs',
            'Managerial Reports',
        ] as $permissionName) {
            DB::table('permissions')->insert([
                'name' => $permissionName,
                'slug' => str($permissionName)->slug(),
                'group' => 'operations',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $roleNames = ['Super Admin','Manager','Receptionist','Accountant','Service Staff','Fandaqah Manager','Hotel Manager','Parking Man','Porter','Concierge','Spa Manager','Housekeeper','Cleaning Manager','Maintenance Supervisor','Event Planner','Hotel Receptionist'];
        foreach ($roleNames as $roleName) {
            DB::table('roles')->updateOrInsert(['slug' => str($roleName)->slug()], ['name' => $roleName, 'created_at' => now(), 'updated_at' => now()]);
        }

        $allRoleIds = DB::table('roles')->pluck('id');
        foreach (DB::table('users')->get() as $user) {
            $assigned = collect([$user->role_id, $allRoleIds->random()])->unique();
            foreach ($assigned as $roleId) {
                DB::table('role_user')->updateOrInsert(
                    ['role_id' => $roleId, 'user_id' => $user->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        $permissionIds = DB::table('permissions')->pluck('id');
        foreach (DB::table('roles')->get() as $role) {
            foreach ($permissionIds as $permissionId) {
                $enabled = $role->slug === 'super-admin' ? true : (bool) rand(0, 1);
                DB::table('permission_role')->updateOrInsert(
                    ['role_id' => $role->id, 'permission_id' => $permissionId],
                    [
                        'enabled' => $enabled,
                        'anyone' => $enabled ? (bool) rand(0, 1) : false,
                        'can_create' => $enabled ? (bool) rand(0, 1) : false,
                        'can_edit' => $enabled ? (bool) rand(0, 1) : false,
                        'can_view' => $enabled ? true : (bool) rand(0, 1),
                        'can_remove' => $enabled ? (bool) rand(0, 1) : false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $countryData = [
            ['name' => 'Saudi Arabia', 'iso2' => 'SA', 'phone_code' => '+966'],
            ['name' => 'United Arab Emirates', 'iso2' => 'AE', 'phone_code' => '+971'],
            ['name' => 'Egypt', 'iso2' => 'EG', 'phone_code' => '+20'],
        ];
        foreach ($countryData as $country) {
            DB::table('countries')->insert([...$country, 'created_at' => now(), 'updated_at' => now()]);
        }

        $cities = ['Riyadh', 'Jeddah', 'Dammam', 'Dubai', 'Abu Dhabi', 'Cairo', 'Alexandria'];
        foreach ($cities as $idx => $city) {
            DB::table('cities')->insert(['country_id' => $idx < 3 ? 1 : ($idx < 5 ? 2 : 3), 'name' => $city, 'created_at' => now(), 'updated_at' => now()]);
        }

        for ($f = 1; $f <= 6; $f++) {
            DB::table('room_floors')->insert(['name' => "Floor {$f}", 'level' => $f, 'created_at' => now(), 'updated_at' => now()]);
        }
        for ($i = 1; $i <= 6; $i++) {
            DB::table('room_types')->insert(['name' => ['Twin Room', 'Deluxe', 'Suite', 'Family', 'Studio', 'Executive'][$i - 1], 'base_price' => rand(80, 350), 'created_at' => now(), 'updated_at' => now()]);
        }


        foreach ([['Suite'], ['Deluxe'], ['Studio'], ['Family']] as $type) {
            DB::table('unit_types')->insert(['name' => $type[0], 'created_at' => now(), 'updated_at' => now()]);
        }

        foreach ([
            ['name' => 'Available', 'slug' => 'available', 'color' => '#22c55e'],
            ['name' => 'Booked', 'slug' => 'booked', 'color' => '#3b82f6'],
            ['name' => 'Busy', 'slug' => 'busy', 'color' => '#ef4444'],
            ['name' => 'Housekeeping', 'slug' => 'housekeeping', 'color' => '#f97316'],
            ['name' => 'Maintenance', 'slug' => 'maintenance', 'color' => '#9ca3af'],
        ] as $status) {
            DB::table('unit_statuses')->insert([...$status, 'created_at' => now(), 'updated_at' => now()]);
        }

        $statuses = ['available', 'occupied', 'reserved', 'maintenance', 'cleaning', 'not_ready'];
        for ($i = 1; $i <= 68; $i++) {
            $st = $statuses[array_rand($statuses)];
            DB::table('rooms')->insert(['room_type_id' => rand(1, 6), 'room_floor_id' => rand(1, 6), 'number' => str_pad((string) $i, 3, '0', STR_PAD_LEFT), 'name' => fake()->randomElement(['Elite Room', 'Premium Room', 'Ocean View', 'Classic Room']), 'floor' => rand(1, 6), 'price_per_day' => rand(200, 900), 'status' => $st, 'gender' => fake()->randomElement(['all', 'male', 'female']), 'thumbnail' => '/assets/avatars/guest'.(($i % 8) + 1).'.svg', 'created_at' => now(), 'updated_at' => now()]);
        }

        for ($i = 1; $i <= 120; $i++) {
            $floorId = (($i - 1) % 3) + 1;
            DB::table('units')->insert([
                'room_id' => rand(1, 68),
                'room_floor_id' => $floorId,
                'unit_type_id' => rand(1, 4),
                'unit_status_id' => rand(1, 5),
                'name' => 'Room Name',
                'number' => (string) (200 + $i),
                'status' => 'active',
                'capacity' => rand(2, 5),
                'beds' => rand(1, 3),
                'baths' => rand(1, 2),
                'thumbnail' => '/assets/avatars/guest'.(($i % 8) + 1).'.svg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        for ($i = 1; $i <= 36; $i++) {
            CompanyProfile::query()->create([
                'company_name' => fake()->company(),
                'country_id' => rand(1, 3),
                'city_id' => rand(1, 7),
                'mobile_number' => '+966 '.rand(500000000, 599999999),
                'responsible_person_name' => fake()->name(),
                'responsible_mobile_number' => '+966 '.rand(500000000, 599999999),
                'id_type' => fake()->randomElement(['National ID', 'CR', 'Passport']),
                'id_number' => (string) rand(1000000000, 9999999999),
                'email' => fake()->companyEmail(),
                'tax_number' => (string) rand(100000000000000, 999999999999999),
                'address' => fake()->address(),
            ]);
        }

        for ($i = 1; $i <= 130; $i++) {
            DB::table('guests')->insert([
                'company_profile_id' => $i <= 35 ? rand(1, 36) : null,
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'phone' => '+966 '.rand(500000000, 599999999),
                'type' => fake()->randomElement(['vip', 'normal', 'company']),
                'gender' => fake()->randomElement(['male', 'female']),
                'card_id' => (string) rand(1000000000, 9999999999),
                'date_of_birth' => fake()->date(),
                'drop_down_civn' => fake()->randomElement(['Drop Down', 'Option 1', 'Option 2']),
                'address' => fake()->address(),
                'read_only_field' => 'Read Only',
                'avatar' => '/assets/avatars/guest'.(($i % 8) + 1).'.svg',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (['Pending', 'Confirmed', 'Checked In', 'Checked Out', 'Cancelled'] as $s) {
            DB::table('reservation_statuses')->insert(['name' => $s, 'created_at' => now(), 'updated_at' => now()]);
        }

        for ($i = 1; $i <= 260; $i++) {
            $checkIn = now()->subDays(rand(1, 15))->addDays(rand(0, 30));
            $checkOut = (clone $checkIn)->addDays(rand(1, 5));
            DB::table('reservations')->insert(['code' => 'RSV'.str_pad((string) $i, 5, '0', STR_PAD_LEFT), 'guest_id' => rand(1, 130), 'room_id' => rand(1, 68), 'unit_id' => rand(1, 120), 'reservation_status_id' => rand(1, 5), 'check_in' => $checkIn, 'check_out' => $checkOut, 'stay_type' => rand(0, 1) ? 'checkin' : 'checkout', 'created_at' => now(), 'updated_at' => now()]);
        }

        for ($i = 1; $i <= 80; $i++) {
            DB::table('check_in_records')->insert(['reservation_id' => $i, 'unit_id' => rand(1, 120), 'date' => now()->toDateString(), 'time' => fake()->randomElement(['10:00 AM', '11:00 AM', '12:00 PM']), 'note' => 'Early check-in', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('check_out_records')->insert(['reservation_id' => $i, 'unit_id' => rand(1, 120), 'date' => now()->toDateString(), 'time' => fake()->randomElement(['01:00 PM', '02:00 PM', '03:00 PM']), 'note' => 'Standard checkout', 'final_charges' => rand(0, 250), 'created_at' => now(), 'updated_at' => now()]);
        }

        for ($i = 1; $i <= 165; $i++) {
            DB::table('bookings')->insert(['reservation_id' => $i, 'guest_id' => rand(1, 130), 'room_id' => rand(1, 68), 'check_in' => now()->addDays(rand(0, 20)), 'check_out' => now()->addDays(rand(21, 30)), 'total_amount' => rand(200, 1800), 'created_at' => now(), 'updated_at' => now()]);
        }
        for ($i = 1; $i <= 165; $i++) {
            DB::table('payments')->insert(['booking_id' => $i, 'amount' => rand(100, 1800), 'method' => ['cash', 'card', 'transfer'][array_rand([0, 1, 2])], 'created_at' => now(), 'updated_at' => now()]);
            DB::table('invoices')->insert(['booking_id' => $i, 'number' => 'INV'.str_pad((string) $i, 5, '0', STR_PAD_LEFT), 'amount' => rand(100, 1800), 'status' => fake()->randomElement(['paid', 'pending', 'unpaid', 'failed', 'refund']), 'created_at' => now(), 'updated_at' => now()]);
        }

        for ($i = 1; $i <= 15; $i++) {
            DB::table('services')->insert(['name' => 'Service '.$i, 'price' => rand(10, 150), 'created_at' => now(), 'updated_at' => now()]);
        }
        for ($i = 1; $i <= 30; $i++) {
            DB::table('products')->insert(['name' => 'Product '.$i, 'price' => rand(5, 70), 'stock' => rand(30, 200), 'created_at' => now(), 'updated_at' => now()]);
        }
        for ($i = 1; $i <= 45; $i++) {
            DB::table('service_orders')->insert(['service_id' => rand(1, 15), 'guest_id' => rand(1, 130), 'qty' => rand(1, 5), 'created_at' => now(), 'updated_at' => now()]);
            DB::table('product_orders')->insert(['product_id' => rand(1, 30), 'guest_id' => rand(1, 130), 'qty' => rand(1, 5), 'created_at' => now(), 'updated_at' => now()]);
        }

        for ($i = 1; $i <= 60; $i++) {
            DB::table('p_o_s_orders')->insert(['guest_id' => rand(1, 130), 'amount' => rand(20, 400), 'status' => fake()->randomElement(['completed', 'pending', 'cancelled']), 'created_at' => now(), 'updated_at' => now()]);
        }

        for ($i = 1; $i <= 12; $i++) {
            DB::table('notifications')->insert(['user_id' => 1, 'title' => 'Notification '.$i, 'body' => 'Operational update '.$i, 'is_read' => $i % 3 === 0, 'created_at' => now(), 'updated_at' => now()]);
        }

        for ($i = 0; $i < 95; $i++) {
            $d = now()->subDays(94 - $i)->toDateString();
            DB::table('customer_metrics')->insert(['metric_date' => $d, 'period' => 'monthly', 'new_customers' => rand(20, 90), 'current_customers' => rand(120, 280), 'created_at' => now(), 'updated_at' => now()]);
            DB::table('revenue_metrics')->insert(['metric_date' => $d, 'amount' => rand(12000, 50000), 'created_at' => now(), 'updated_at' => now()]);
            DB::table('unit_status_metrics')->insert(['metric_date' => $d, 'occupied' => rand(80, 180), 'available' => rand(40, 120), 'created_at' => now(), 'updated_at' => now()]);
            DB::table('room_metrics')->insert(['metric_date' => $d, 'total_rooms' => 68, 'available_rooms' => rand(24, 55), 'not_ready_rooms' => rand(2, 18), 'booked_rooms' => rand(10, 30), 'created_at' => now(), 'updated_at' => now()]);
            DB::table('occupancy_metrics')->insert(['metric_date' => $d, 'unit_occupancy' => rand(40, 95), 'total_guests' => rand(120, 350), 'created_at' => now(), 'updated_at' => now()]);
        }



        for ($i = 1; $i <= 8; $i++) {
            DB::table('reservation_drafts')->insert([
                'user_id' => 1,
                'reference' => 'DRF'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'current_step' => rand(1, 3),
                'details_payload' => json_encode(['booking_type' => 'single', 'check_in' => now()->addDays($i)->toDateString(), 'check_out' => now()->addDays($i + 2)->toDateString(), 'nights' => 2]),
                'visitor_payload' => json_encode(['visitor_name' => fake()->name(), 'nationality' => 'Saudi Arabia', 'gender' => fake()->randomElement(['male', 'female'])]),
                'payment_payload' => json_encode(['payment_method' => fake()->randomElement(['cash', 'pos', 'on_arrival']), 'discount' => rand(0, 20)]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }



        for ($i = 1; $i <= 120; $i++) {
            DB::table('receipts')->insert([
                'receipt_number' => 'REC'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'receipt_type' => fake()->randomElement(['Rent', 'Insurance', 'Service']),
                'receipt_code' => (string) rand(1000000000, 9999999999),
                'reason' => fake()->randomElement(['Rent', 'Late checkout', 'Service']),
                'amount' => rand(200, 1500),
                'employee_id' => rand(1, 22),
                'date' => now()->subDays(rand(0, 90))->toDateString(),
                'payment_method' => fake()->randomElement(['Cash', 'Credit Card', 'Bank Transfer', 'Mada', 'Agal']),
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('expenses')->insert([
                'expense_number' => 'EXP'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'expense_type' => fake()->randomElement(['Operations', 'Supplies', 'Maintenance']),
                'expense_code' => (string) rand(1000000000, 9999999999),
                'reason' => fake()->randomElement(['Rent', 'Utilities', 'Cleaning']),
                'amount' => rand(150, 1200),
                'employee_id' => rand(1, 22),
                'date' => now()->subDays(rand(0, 90))->toDateString(),
                'payment_method' => fake()->randomElement(['Cash', 'Credit Card', 'Bank Transfer', 'Mada', 'Agal']),
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        for ($i = 1; $i <= 70; $i++) {
            DB::table('bills')->insert([
                'bill_number' => 'BLL'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'creation_date' => now()->subDays(rand(5, 120))->toDateString(),
                'reason' => fake()->randomElement(['Rent', 'Room Damage', 'Services']),
                'amount' => rand(200, 1800),
                'employee_id' => rand(1, 22),
                'collection_date' => now()->subDays(rand(0, 90))->toDateString(),
                'status' => fake()->randomElement(['collected', 'not_done']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        for ($i = 1; $i <= 60; $i++) {
            DB::table('credit_notes')->insert([
                'note_number' => 'CRN'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                'invoice_number' => 'INV'.str_pad((string) rand(1, 165), 5, '0', STR_PAD_LEFT),
                'booking_number' => 'BK'.str_pad((string) rand(1, 165), 5, '0', STR_PAD_LEFT),
                'amount' => rand(100, 800),
                'employee_id' => rand(1, 22),
                'creation_date' => now()->subDays(rand(0, 90))->toDateString(),
                'visitor_name' => fake()->name(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (DB::table('receipts')->limit(120)->get() as $receipt) {
            DB::table('fund_movements')->insert([
                'movement_type' => 'receipt',
                'reference_number' => $receipt->receipt_number,
                'reason' => $receipt->reason,
                'amount' => $receipt->amount,
                'employee_id' => $receipt->employee_id,
                'date' => $receipt->date,
                'payment_method' => $receipt->payment_method,
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (DB::table('expenses')->limit(120)->get() as $expense) {
            DB::table('fund_movements')->insert([
                'movement_type' => 'bills_of_exchange',
                'reference_number' => $expense->expense_number,
                'reason' => $expense->reason,
                'amount' => $expense->amount,
                'employee_id' => $expense->employee_id,
                'date' => $expense->date,
                'payment_method' => $expense->payment_method,
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ([
            ['WELCOME10', 'percent', 10, 75, true],
            ['SAR50', 'fixed', 50, null, true],
            ['VIP20', 'percent', 20, 120, true],
        ] as $promo) {
            DB::table('promo_codes')->insert([
                'code' => $promo[0],
                'type' => $promo[1],
                'value' => $promo[2],
                'max_discount' => $promo[3],
                'is_active' => $promo[4],
                'expires_at' => now()->addMonths(6),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        foreach ([['Welcome Back', 'This slider for any update', '/assets/banners/banner1.svg'], ['Premium Stays Await', 'Discover seasonal offers', '/assets/banners/banner2.svg'], ['Operational Insights', 'Track reservations in real-time', '/assets/banners/banner3.svg']] as $b) {
            DB::table('dashboard_banners')->insert(['title' => $b[0], 'subtitle' => $b[1], 'image_path' => $b[2], 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}
