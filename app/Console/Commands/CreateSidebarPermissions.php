<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateSidebarPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:sidebar-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create missing sidebar permissions in the system';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get all permissions from the sidebar configuration
        $sidebarConfig = config('sidebar', []);
        $permissions = collect();
        
        foreach ($sidebarConfig as $item) {
            // Add top-level permission if exists
            if (!empty($item['permission'])) {
                $permissions->push($item['permission']);
            }

            // Add child permissions if exist
            if (!empty($item['children'])) {
                foreach ($item['children'] as $child) {
                    if (!empty($child['permission'])) {
                        $permissions->push($child['permission']);
                    }
                }
            }
        }

        // Remove duplicates
        $permissions = $permissions->unique();

        $this->info("Found {$permissions->count()} unique permissions in sidebar config");

        // Get existing permissions from the database
        $existingPermissions = \DB::table('permissions')->pluck('name')->toArray();
        $missingPermissions = $permissions->diff($existingPermissions);

        if ($missingPermissions->isEmpty()) {
            $this->info('All permissions already exist in the system.');
            return 0;
        }

        $this->info("Creating {$missingPermissions->count()} missing permissions...");

        $createdCount = 0;
        foreach ($missingPermissions as $permission) {
            try {
                \DB::table('permissions')->insert([
                    'name' => $permission,
                    'slug' => Str::slug($permission),
                    'group' => 'general', // Default group
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->info("Created permission: {$permission}");
                $createdCount++;
            } catch (\Exception $e) {
                $this->error("Failed to create permission: {$permission} - " . $e->getMessage());
            }
        }

        $this->info("Successfully created {$createdCount} permissions.");

        return 0;
    }
}