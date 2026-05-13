<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use App\Models\Team;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserRolePermissionSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        $roles = [
            'Super Admin', 'Hotel Owner', 'General Manager', 'Front Desk Manager',
            'Front Desk Agent', 'Housekeeping Supervisor', 'Housekeeper',
            'Maintenance User', 'Accountant', 'Cashier', 'Revenue Manager',
            'Marketing Manager', 'Auditor', 'Integration Admin', 'Read-only Viewer'
        ];

        foreach ($roles as $roleName) {
            $slug = strtolower(str_replace(' ', '-', $roleName)) . '-' . $team->id;
            DB::table('roles')->updateOrInsert(
                ['slug' => $slug, 'team_id' => $team->id],
                ['name' => $roleName, 'team_id' => $team->id, 'created_at' => now()]
            );
        }

        // Create Demo Users
        $userConfigs = [
            ['email' => 'admin@fandaqah-palace.com', 'name' => 'Fandaqah Admin', 'role' => 'Super Admin'],
            ['email' => 'owner@fandaqah-palace.com', 'name' => 'Hotel Owner', 'role' => 'Hotel Owner'],
            ['email' => 'gm@fandaqah-palace.com', 'name' => 'General Manager', 'role' => 'General Manager'],
            ['email' => 'fd@fandaqah-palace.com', 'name' => 'Front Desk Agent', 'role' => 'Front Desk Agent'],
        ];

        foreach ($userConfigs as $config) {
            $user = User::updateOrCreate(
                ['email' => $config['email']],
                [
                    'name' => $config['name'],
                    'password' => Hash::make('password'),
                    'current_team_id' => $team->id,
                    'created_at' => now(),
                ]
            );

            $role = DB::table('roles')->where('name', $config['role'])->where('team_id', $team->id)->first();
            if ($role) {
                DB::table('role_user')->updateOrInsert(
                    ['user_id' => $user->id, 'role_id' => $role->id],
                    ['user_id' => $user->id, 'role_id' => $role->id]
                );
            }

            // Ensure user is in the team
            DB::table('team_users')->updateOrInsert(
                ['team_id' => $team->id, 'user_id' => $user->id],
                ['team_id' => $team->id, 'user_id' => $user->id, 'role' => strtolower($config['role'])]
            );
        }
    }
}