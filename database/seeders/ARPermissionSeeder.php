<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ARPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'Invoice Transfer to AR',
                'slug' => 'ar.invoice_transfer',
                'group' => 'Accounts Receivable'
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
