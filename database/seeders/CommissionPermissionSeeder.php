<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommissionPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'Manage Commissions',
                'slug' => 'commission.manage',
                'group' => 'Finance'
            ],
        ];

        foreach ($permissions as $p) {
            \App\Models\Permission::updateOrCreate(
                ['slug' => $p['slug']],
                ['name' => $p['name'], 'group' => $p['group']]
            );
        }
    }
}
