<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Unit;
use App\UnitMaintenance;
use App\Models\MaintenanceCategory;
use App\Models\MaintenanceTicket;
use App\User;
use App\ActionType;
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
        $team = Team::where('slug', 'fandaqah-palace')->first() ?: Team::where('slug', 'demo-hotel')->first();
        
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

        $user = User::first() ?? User::factory()->create();

        // 1. Seed Maintenance Categories
        $categories = [
            'Plumbing',
            'Electrical',
            'Air Conditioning (HVAC)',
            'Furniture & Carpentry',
            'Appliances & Electronics',
            'General Cleaning & Carpets',
            'Lock & Key Security',
        ];

        $categoryModels = [];
        foreach ($categories as $catName) {
            $categoryModels[] = MaintenanceCategory::firstOrCreate(['name' => $catName]);
        }

        // 2. Seed Maintenance Tickets (modern screen)
        $subjects = [
            'Leaky faucet in bathroom sink',
            'Flickering light bulbs in main area',
            'AC unit making rattling noise',
            'Loose dining table leg',
            'Smart TV remote control unresponsive',
            'Deep wine stain on living room carpet',
            'Electronic lock card scanner unresponsive',
        ];

        $descriptions = [
            'The bathroom sink faucet is dripping constantly, causing water pooling. Needs washer replacement.',
            'Two light fixtures in the ceiling of the main bedroom are flickering intermittently. Might be loose wires or failing bulbs.',
            'The indoor split AC unit makes a persistent rattling sound whenever fan speed increases. Cooling works but noise is loud.',
            'The corner dining chair has a loose leg pivot joint. Very shaky to sit on and needs carpentry tightening.',
            'The Samsung smart TV is unable to pair with the remote control or respond to keystrokes. Checked batteries, still down.',
            'A large reddish wine stain is visible on the center carpet. Requires specialized chemical deep wash.',
            'The keycard lock system reader flashes red continuously and fails to read valid customer check-in cards.',
        ];

        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];

        for ($i = 0; $i < 15; $i++) {
            $unit = $units->random();
            $category = collect($categoryModels)->random();
            $subjectIndex = $i % count($subjects);

            MaintenanceTicket::create([
                'unit_id' => $unit->id,
                'maintenance_category_id' => $category->id,
                'subject' => $subjects[$subjectIndex],
                'description' => $descriptions[$subjectIndex],
                'status' => collect($statuses)->random(),
            ]);
        }

        // 3. Seed Unit Maintenances (legacy / operational screen)
        // Ensure ActionType or similar exists
        $actionType = ActionType::first();
        $actionId = $actionType ? $actionType->id : null;

        for ($i = 0; $i < 10; $i++) {
            $unit = $units->random();
            $startAt = Carbon::now()->subDays(rand(1, 15));
            $completedAt = rand(0, 1) === 1 ? $startAt->copy()->addHours(rand(2, 24)) : null;

            UnitMaintenance::create([
                'unit_id' => $unit->id,
                'created_by' => $user->id,
                'start_at' => $startAt,
                'completed_at' => $completedAt,
                'note' => collect([
                    'Replaced AC compressor filter and refilled coolant gas.',
                    'Unclogged vanity sink drain pipes and sealed minor joints.',
                    'Repaired structural frame of king-size bed and tightened headboard.',
                    'Inspected electrical breaker circuits and reset room safety triggers.',
                    'Conducted complete room deep cleaning, upholstery scrub, and disinfection.',
                ])->random(),
                'team_id' => $team->id,
                'completed_by' => $completedAt ? $user->id : null,
                'action_id' => $actionId,
                'expected_at' => $startAt->copy()->addDays(2),
            ]);
        }
    }
}