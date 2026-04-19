<?php

namespace App\Console\Commands;

use App\Unit;
use Illuminate\Console\Command;

class CreateMultipleUnitsUnderOneUnitCategoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:units 
                            {team_id? : The ID of the team} 
                            {unit_category_id? : The ID of the unit category} 
                            {floors_config?* : Floor configurations in the format floor_number:unit_count (e.g., 1:41 2:44)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create units under one unit category for a specific team with dynamic floor configurations';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get inputs
        $team_id = $this->argument('team_id') ?? $this->ask('Please enter the team ID');
        $unit_category_id = $this->argument('unit_category_id') ?? $this->ask('Please enter the unit category ID');
        $floors_config = $this->argument('floors_config');

        // If floors_config is empty, ask for it
        if (empty($floors_config)) {
            $floors_config = $this->ask('Please enter the floor configurations (e.g., 1:41 2:44)');
            $floors_config = explode(' ', $floors_config); // Convert the input string to an array
        }

        // Validate inputs
        if (empty($team_id)) {
            return $this->error('The team_id argument is required.');
        }

        if (empty($unit_category_id)) {
            return $this->error('The unit_category_id argument is required.');
        }

        if (empty($floors_config)) {
            return $this->error('The floors_config argument is required. Use the format floor_number:unit_count (e.g., 1:41 2:44).');
        }

        // Parse floor configurations
        $floors = [];
        foreach ($floors_config as $config) {
            if (!str_contains($config, ':')) {
                return $this->error("Invalid floor configuration format: {$config}. Use floor_number:unit_count (e.g., 1:41).");
            }

            [$floor_number, $unit_count] = explode(':', $config);
            $floors[(int) $floor_number] = (int) $unit_count;
        }

        if (empty($floors)) {
            return $this->error('No valid floor configurations provided.');
        }

        // Room prefixes (can be adjusted as needed)
        $room_prefixes = range(1, 100); // Example: Room numbers from 1 to 100

        // Create units for each floor
        $this->info('Creating units...');

        foreach ($floors as $floor_number => $unit_count) {
            if ($unit_count > count($room_prefixes)) {
                $this->error("Floor {$floor_number} requires more room prefixes ({$unit_count}) than available.");
                continue;
            }

            $this->info("Creating units for floor {$floor_number}...");

            // Create a progress bar for the current floor
            $floorProgressBar = $this->output->createProgressBar($unit_count);
            $floorProgressBar->start();

            for ($i = 0; $i < $unit_count; $i++) {
                $room_number = $room_prefixes[$i];

                $unit = new Unit();
                $unit->team_id = $team_id;
                $unit->unit_category_id = $unit_category_id;
                $unit->unit_number = ($floor_number * 100) + $room_number;
                $unit->status = 1;
                $unit->enabled = 1;
                $unit->save();

                // Advance the progress bar for the current floor
                $floorProgressBar->advance();
            }

            // Finish the progress bar for the current floor
            $floorProgressBar->finish();
            $this->line(''); // Add a new line after the progress bar
        }

        $this->info('Units created successfully!');
        $this->info('Thank you for using this command. Special thanks to developer emadrashadmuhhamed for making this possible!');
    }
}
