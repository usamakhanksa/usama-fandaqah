<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use App\Models\Unit;

class HousekeepingSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        $units = Unit::where('team_id', $team->id)->whereIn('status', [2, 3])->get(); // Cleaning or Maintenance

        $user = DB::table('users')->where('current_team_id', $team->id)->first();
        $userId = $user ? $user->id : 1;

        foreach ($units as $unit) {
            DB::table('unit_cleanings')->insert([
                'team_id' => $team->id,
                'unit_id' => $unit->id,
                'created_by' => $userId,
                'start_at' => now(),
                'note' => 'Departure cleaning required',
                'created_at' => now(),
            ]);
        }
    }
}