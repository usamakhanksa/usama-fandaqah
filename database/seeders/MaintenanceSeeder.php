<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Unit;
use App\MaintenanceRequest;
use Carbon\Carbon;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the demo team
        $team = Team::where('slug', 'demo-hotel')->first();
        
        if (!$team) {
            $this->command->error('Demo team not found. Please run TeamSeeder first.');
            return;
        }

        // Get units
        $units = Unit::where('team_id', $team->id)->get();
        
        if ($units->count() === 0) {
            $this->command->error('No units found. Please run UnitSeeder first.');
            return;
        }

        // Create maintenance requests
        $maintenanceRequests = [
            [
                'team_id' => $team->id,
                'unit_id' => $units->random()->id,
                'reported_by' => rand(1, 5), // Random user ID
                'report_date' => Carbon::now()->subDays(rand(1, 10)),
                'priority' => 'high',
                'category' => 'plumbing',
                'title' => 'Leaky faucet in bathroom',
                'description' => 'The bathroom faucet is leaking water continuously. Needs immediate attention.',
                'status' => 'completed',
                'assigned_to' => rand(1, 3), // Maintenance staff
                'scheduled_date' => Carbon::now()->subDays(rand(1, 3)),
                'completed_date' => Carbon::now()->subDays(rand(0, 1)),
                'cost' => 75.00,
                'notes' => 'Replaced faucet washer and tightened connections',
            ],
            [
                'team_id' => $team->id,
                'unit_id' => $units->random()->id,
                'reported_by' => rand(1, 5), // Random user ID
                'report_date' => Carbon::now()->subDays(rand(1, 5)),
                'priority' => 'medium',
                'category' => 'electrical',
                'title' => 'Faulty light switch',
                'description' => 'The bedroom light switch is not functioning properly. Sometimes turns off by itself.',
                'status' => 'in_progress',
                'assigned_to' => rand(1, 3), // Maintenance staff
                'scheduled_date' => Carbon::now()->addDays(rand(0, 2)),
                'completed_date' => null,
                'cost' => 45.00,
                'notes' => 'Electrician scheduled for tomorrow',
            ],
            [
                'team_id' => $team->id,
                'unit_id' => $units->random()->id,
                'reported_by' => rand(1, 5), // Random user ID
                'report_date' => Carbon::now()->subDays(rand(1, 3)),
                'priority' => 'low',
                'category' => 'furniture',
                'title' => 'Loose chair leg',
                'description' => 'The dining chair has a loose leg that needs tightening.',
                'status' => 'pending',
                'assigned_to' => null, // Not assigned yet
                'scheduled_date' => null,
                'completed_date' => null,
                'cost' => 25.00,
                'notes' => 'Needs to be checked during routine inspection',
            ],
            [
                'team_id' => $team->id,
                'unit_id' => $units->random()->id,
                'reported_by' => rand(1, 5), // Random user ID
                'report_date' => Carbon::now()->subDays(rand(1, 7)),
                'priority' => 'high',
                'category' => 'ac',
                'title' => 'Air conditioning not cooling',
                'description' => 'The air conditioning unit is running but not cooling the room effectively.',
                'status' => 'completed',
                'assigned_to' => rand(1, 3), // Maintenance staff
                'scheduled_date' => Carbon::now()->subDays(rand(1, 2)),
                'completed_date' => Carbon::now()->subDays(1),
                'cost' => 120.00,
                'notes' => 'Recharged refrigerant gas and cleaned filters',
            ],
            [
                'team_id' => $team->id,
                'unit_id' => $units->random()->id,
                'reported_by' => rand(1, 5), // Random user ID
                'report_date' => Carbon::now(),
                'priority' => 'high',
                'category' => 'plumbing',
                'title' => 'Clogged toilet',
                'description' => 'The toilet in the main bathroom is completely clogged and not flushing properly.',
                'status' => 'reported',
                'assigned_to' => null, // Not assigned yet
                'scheduled_date' => null,
                'completed_date' => null,
                'cost' => 60.00,
                'notes' => 'Requires immediate attention as unit is occupied',
            ],
            [
                'team_id' => $team->id,
                'unit_id' => $units->random()->id,
                'reported_by' => rand(1, 5), // Random user ID
                'report_date' => Carbon::now()->subDays(rand(1, 14)),
                'priority' => 'medium',
                'category' => 'electrical',
                'title' => 'Power outlet not working',
                'description' => 'The power outlet near the desk is not providing electricity.',
                'status' => 'completed',
                'assigned_to' => rand(1, 3), // Maintenance staff
                'scheduled_date' => Carbon::now()->subDays(rand(3, 5)),
                'completed_date' => Carbon::now()->subDays(rand(1, 2)),
                'cost' => 55.00,
                'notes' => 'Replaced faulty outlet and checked circuit',
            ],
            [
                'team_id' => $team->id,
                'unit_id' => $units->random()->id,
                'reported_by' => rand(1, 5), // Random user ID
                'report_date' => Carbon::now()->subDays(rand(1, 4)),
                'priority' => 'low',
                'category' => 'cleaning',
                'title' => 'Stained carpet',
                'description' => 'There is a noticeable stain on the bedroom carpet that needs professional cleaning.',
                'status' => 'in_progress',
                'assigned_to' => rand(1, 3), // Maintenance staff
                'scheduled_date' => Carbon::now()->addDays(rand(1, 3)),
                'completed_date' => null,
                'cost' => 35.00,
                'notes' => 'Professional carpet cleaner scheduled',
            ],
            [
                'team_id' => $team->id,
                'unit_id' => $units->random()->id,
                'reported_by' => rand(1, 5), // Random user ID
                'report_date' => Carbon::now()->subDays(rand(1, 6)),
                'priority' => 'medium',
                'category' => 'security',
                'title' => 'Door lock malfunction',
                'description' => 'The door lock is difficult to operate and sometimes sticks when locking/unlocking.',
                'status' => 'pending',
                'assigned_to' => null, // Not assigned yet
                'scheduled_date' => null,
                'completed_date' => null,
                'cost' => 90.00,
                'notes' => 'Needs lock mechanism replacement',
            ],
            [
                'team_id' => $team->id,
                'unit_id' => $units->random()->id,
                'reported_by' => rand(1, 5), // Random user ID
                'report_date' => Carbon::now()->subDays(rand(1, 9)),
                'priority' => 'high',
                'category' => 'ac',
                'title' => 'Air conditioner making noise',
                'description' => 'The air conditioner is making loud rattling noises when running.',
                'status' => 'completed',
                'assigned_to' => rand(1, 3), // Maintenance staff
                'scheduled_date' => Carbon::now()->subDays(rand(2, 4)),
                'completed_date' => Carbon::now()->subDays(rand(1, 2)),
                'cost' => 110.00,
                'notes' => 'Fixed loose components and lubricated moving parts',
            ],
            [
                'team_id' => $team->id,
                'unit_id' => $units->random()->id,
                'reported_by' => rand(1, 5), // Random user ID
                'report_date' => Carbon::now()->subDays(rand(1, 3)),
                'priority' => 'medium',
                'category' => 'furniture',
                'title' => 'Broken window blind',
                'description' => 'The window blind cord is broken and the blind won\'t go up or down properly.',
                'status' => 'in_progress',
                'assigned_to' => rand(1, 3), // Maintenance staff
                'scheduled_date' => Carbon::now()->addDays(rand(1, 2)),
                'completed_date' => null,
                'cost' => 40.00,
                'notes' => 'Replacement parts ordered, installation scheduled',
            ],
        ];

        foreach ($maintenanceRequests as $request) {
            MaintenanceRequest::firstOrCreate(
                [
                    'unit_id' => $request['unit_id'],
                    'report_date' => $request['report_date'],
                    'title' => $request['title'],
                ],
                $request
            );
        }

        // Create preventive maintenance schedules
        for ($i = 0; $i < 8; $i++) {
            MaintenanceRequest::create([
                'team_id' => $team->id,
                'unit_id' => $units->random()->id,
                'reported_by' => 1, // System or manager
                'report_date' => Carbon::now()->subDays(rand(30, 60)),
                'priority' => 'medium',
                'category' => 'preventive',
                'title' => 'Preventive maintenance check',
                'description' => 'Regular preventive maintenance check for HVAC, plumbing, electrical systems.',
                'status' => 'completed',
                'assigned_to' => rand(1, 3), // Maintenance staff
                'scheduled_date' => Carbon::now()->subDays(rand(5, 15)),
                'completed_date' => Carbon::now()->subDays(rand(1, 4)),
                'cost' => 150.00,
                'notes' => 'Full system inspection completed, no issues found',
                'is_preventive' => true,
            ]);
        }

        // Create some urgent maintenance requests for current date
        for ($i = 0; $i < 3; $i++) {
            MaintenanceRequest::create([
                'team_id' => $team->id,
                'unit_id' => $units->random()->id,
                'reported_by' => rand(1, 5), // Random user ID
                'report_date' => Carbon::now(),
                'priority' => 'critical',
                'category' => $i === 0 ? 'electrical' : ($i === 1 ? 'plumbing' : 'security'),
                'title' => $i === 0 ? 'Electrical sparks' : ($i === 1 ? 'Water leak' : 'Security breach'),
                'description' => $i === 0 ? 'Seeing sparks from electrical panel' : 
                                ($i === 1 ? 'Major water leak from pipe burst' : 'Unauthorized access detected'),
                'status' => 'reported',
                'assigned_to' => null,
                'scheduled_date' => null,
                'completed_date' => null,
                'cost' => 0.00, // Cost determined after assessment
                'notes' => 'Immediate attention required',
            ]);
        }
    }
}