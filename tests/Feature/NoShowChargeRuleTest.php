<?php

namespace Tests\Feature;

use App\Models\NoShowChargeRule;
use App\Models\NightAuditLog;
use App\Team;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NoShowChargeRuleTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => 'NoShow Admin',
            'email' => 'noshow_'.rand().'@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->team = Team::create([
            'name' => 'NoShow Hotel',
            'slug' => 'noshow-hotel-' . rand(),
            'owner_id' => $this->user->id,
            'business_date' => '2026-05-10',
        ]);
        
        $this->user->update(['current_team_id' => $this->team->id]);
    }

    public function test_can_create_no_show_rule()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/no-show-rules', [
                'name' => 'Standard No-Show',
                'start_date' => '2026-05-01',
                'end_date' => '2026-05-31',
                'charge_type' => 'fixed',
                'charge_amount' => 100,
                'applies_to' => 'all',
                'is_active' => true
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('no_show_charge_rules', ['name' => 'Standard No-Show']);
    }

    public function test_cannot_create_overlapping_rules()
    {
        NoShowChargeRule::create([
            'team_id' => $this->team->id,
            'name' => 'Rule 1',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-15',
            'charge_type' => 'fixed',
            'charge_amount' => 50,
            'applies_to' => 'all',
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/no-show-rules', [
                'name' => 'Rule 2 Overlap',
                'start_date' => '2026-05-10',
                'end_date' => '2026-05-20',
                'charge_type' => 'fixed',
                'charge_amount' => 100,
                'applies_to' => 'all',
                'is_active' => true
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'This rule overlaps with an existing active rule.']);
    }

    public function test_cannot_edit_rule_after_night_audit_ran()
    {
        $rule = NoShowChargeRule::create([
            'team_id' => $this->team->id,
            'name' => 'Past Rule',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-05',
            'charge_type' => 'fixed',
            'charge_amount' => 50,
            'applies_to' => 'all',
            'is_active' => true
        ]);

        // Create a completed audit log for one of the dates
        NightAuditLog::create([
            'team_id' => $this->team->id,
            'business_date' => '2026-05-02',
            'status' => 'completed',
            'triggered_by' => 'manual'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/no-show-rules/{$rule->id}", [
                'name' => 'Updated Past Rule',
                'start_date' => '2026-05-01',
                'end_date' => '2026-05-05',
                'charge_type' => 'fixed',
                'charge_amount' => 100,
                'applies_to' => 'all',
                'is_active' => true
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Cannot edit a rule for which Night Audit has already been processed.']);
    }

    public function test_soft_delete_rule()
    {
        $rule = NoShowChargeRule::create([
            'team_id' => $this->team->id,
            'name' => 'Delete Me',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-15',
            'charge_type' => 'fixed',
            'charge_amount' => 50,
            'applies_to' => 'all',
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/no-show-rules/{$rule->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('no_show_charge_rules', ['id' => $rule->id]);
    }
}
