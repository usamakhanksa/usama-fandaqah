<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use App\Models\Unit;
use App\Models\HousekeepingTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HousekeepingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->team = Team::factory()->create();
        $this->user = User::factory()->create(['current_team_id' => $this->team->id]);
        $this->team->users()->attach($this->user);
    }

    public function test_can_view_housekeeping_board()
    {
        Unit::factory()->count(5)->create(['team_id' => $this->team->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/housekeeping/board');

        $response->assertStatus(200);
    }

    public function test_can_complete_cleaning_task()
    {
        $unit = Unit::factory()->create(['team_id' => $this->team->id, 'status' => 'dirty']);
        $task = HousekeepingTask::create([
            'team_id' => $this->team->id,
            'unit_id' => $unit->id,
            'status' => 'pending',
            'type' => 'routine'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/housekeeping/tasks/{$task->id}/complete", ['notes' => 'Cleaned well']);

        $response->assertStatus(200);
        $this->assertEquals('completed', $task->fresh()->status);
        $this->assertEquals('clean', $unit->fresh()->status);
    }
}
