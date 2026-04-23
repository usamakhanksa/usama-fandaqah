<?php

namespace Database\Seeders;

use App\Models\DashboardNotice;
use Illuminate\Database\Seeder;

class DashboardNoticeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $notices = [
            [
                'title' => 'King Abdulaziz Road Maintenance',
                'content' => 'Elevator maintenance scheduled for West Wing between 10 AM and 2 PM today. Please notify guests in floors 5-10.',
                'type' => 'warning',
                'is_active' => true,
            ],
            [
                'title' => 'New Hajj Reservation Protocol',
                'content' => 'Updated check-in documents required for group bookings during the Hajj season. See internal KB for the full checklist.',
                'type' => 'info',
                'is_active' => true,
            ],
            [
                'title' => 'URGENT: Jeddah Branch Water Supply',
                'content' => 'Building C is experiencing temporary water supply issues. Engineering team is on-site. Estimated resolution: 6 PM.',
                'type' => 'urgent',
                'is_active' => true,
                'expires_at' => now()->addHours(8),
            ],
            [
                'title' => 'Riyadh Season Special Offers',
                'content' => 'New seasonal packages for direct bookings are now active in the system. Apply discount code RYD2026.',
                'type' => 'info',
                'is_active' => true,
            ],
            [
                'title' => 'Staff Training: Fire Safety',
                'content' => 'Mandatory fire safety drill for all morning shift staff tomorrow at 9:00 AM in the main lobby.',
                'type' => 'warning',
                'is_active' => true,
            ],
        ];

        foreach ($notices as $notice) {
            DashboardNotice::create($notice);
        }
    }
}
