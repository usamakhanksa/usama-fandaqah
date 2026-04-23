<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GuestCheckin;
use App\Models\WorkspaceTask;

class FrontDeskSeeder extends Seeder {
    public function run(): void {
        // Arrivals
        GuestCheckin::create([
            'booking_reference' => 'BKG-SAU401',
            'guest_name' => 'Mohammed Al-Dosari',
            'room_number' => '301',
            'expected_arrival_date' => today(),
            'expected_departure_date' => today()->addDays(3),
            'status' => 'arrival',
            'id_verified' => false,
            'deposit_collected' => 0,
        ]);

        GuestCheckin::create([
            'booking_reference' => 'BKG-SAU402',
            'guest_name' => 'Sarah Al-Ghamdi',
            'room_number' => '405',
            'expected_arrival_date' => today(),
            'expected_departure_date' => today()->addDays(2),
            'status' => 'arrival',
            'id_verified' => false,
            'deposit_collected' => 0,
        ]);
        
        // In-House
        GuestCheckin::create([
            'booking_reference' => 'BKG-SAU901',
            'guest_name' => 'Khalid Al-Faisal',
            'room_number' => 'PH-01',
            'expected_arrival_date' => today()->subDays(2),
            'expected_departure_date' => today()->addDays(2),
            'status' => 'in_house',
            'id_verified' => true,
            'deposit_collected' => 2500,
            'actual_arrival_time' => now()->subDays(2),
        ]);
        
        // Departures
        GuestCheckin::create([
            'booking_reference' => 'BKG-SAU110',
            'guest_name' => 'Reem Al-Otaibi',
            'room_number' => '210',
            'expected_arrival_date' => today()->subDays(3),
            'expected_departure_date' => today(),
            'status' => 'departure',
            'actual_arrival_time' => now()->subDays(3),
        ]);

        // Workspace Tasks
        WorkspaceTask::create([
            'title' => 'Deliver Welcome Qahwa & Dates',
            'description' => 'VIP setup for Royal Suite arrival at 4 PM.',
            'room_number' => 'PH-01',
            'priority' => 'high',
            'status' => 'pending',
            'due_at' => now()->addHours(2)
        ]);

        WorkspaceTask::create([
            'title' => 'Request Prayer Mat',
            'description' => 'Guest requested an extra prayer mat and Quran.',
            'room_number' => '301',
            'priority' => 'medium',
            'status' => 'in_progress',
        ]);
    }
}
