<?php

namespace Tests\Feature;

use App\Customer;
use App\CompanyNote;
use App\Models\Highlight;
use App\Models\CustomerNote;
use App\Models\BlockedGuest;
use App\Models\TurnawayReason;
use App\Models\TurnawayLog;
use App\Models\Comment;
use App\Models\CompanyGroup;
use App\Models\Team;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GuestsCompaniesModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->user = User::create([
            'name' => 'Manager',
            'email' => 'mgr' . rand(1, 9999) . '@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->team = Team::create(['name' => 'Test Hotel', 'owner_id' => $this->user->id]);
        $this->user->update(['current_team_id' => $this->team->id]);

        $this->customer = Customer::create([
            'team_id' => $this->team->id,
            'name'    => 'Ahmed Al-Rashid',
            'phone'   => '+966500000001',
            'email'   => 'ahmed@test.com',
        ]);
    }

    // ── 5.1 Guest Directory ──────────────────────────────────────

    public function test_guest_directory_displays()
    {
        DB::table('guests')->insert(['team_id' => $this->team->id, 'name' => 'Test Guest', 'created_at' => now(), 'updated_at' => now()]);

        $response = $this->actingAs($this->user)->getJson('/api/guests-module/guests');
        $response->assertStatus(200)->assertJsonStructure(['data']);
        $this->assertCount(1, $response->json('data'));
    }

    // ── 5.2 Customer Profiles ────────────────────────────────────

    public function test_full_customer_crud_works()
    {
        // List
        $this->actingAs($this->user)->getJson('/api/guests-module/customers')->assertStatus(200);

        // Create
        $res = $this->actingAs($this->user)->postJson('/api/guests-module/customers', [
            'name' => 'New Customer', 'phone' => '+966500000002', 'id_type' => 1,
        ]);
        $res->assertStatus(201)->assertJsonPath('message', 'Customer created');
        $id = $res->json('data.id');

        // Show
        $this->actingAs($this->user)->getJson("/api/guests-module/customers/{$id}")->assertStatus(200);

        // Update
        $this->actingAs($this->user)->putJson("/api/guests-module/customers/{$id}", ['name' => 'Updated Name'])->assertStatus(200);
        $this->assertDatabaseHas('customers', ['id' => $id, 'name' => 'Updated Name']);

        // Delete
        $this->actingAs($this->user)->deleteJson("/api/guests-module/customers/{$id}")->assertStatus(200);
        $this->assertSoftDeleted('customers', ['id' => $id]);

        // Restore
        $this->actingAs($this->user)->postJson("/api/guests-module/customers/{$id}/restore")->assertStatus(200);
    }

    // ── 5.3 Companies ────────────────────────────────────────────

    public function test_companies_crud_works()
    {
        $res = $this->actingAs($this->user)->postJson('/api/guests-module/companies', [
            'name' => 'Test Corp', 'tax_number' => '300000000000003', 'credit_limit' => 5000,
        ]);
        $res->assertStatus(201)->assertJsonPath('message', 'Company created');
        $id = $res->json('data.id');

        $this->actingAs($this->user)->getJson('/api/guests-module/companies')->assertStatus(200);
        $this->actingAs($this->user)->getJson("/api/guests-module/companies/{$id}")->assertStatus(200);
        $this->actingAs($this->user)->putJson("/api/guests-module/companies/{$id}", ['name' => 'Updated Corp'])->assertStatus(200);
        $this->actingAs($this->user)->deleteJson("/api/guests-module/companies/{$id}")->assertStatus(200);
    }

    // ── 5.4 Company Groups ───────────────────────────────────────

    public function test_company_groups_crud_works()
    {
        $res = $this->actingAs($this->user)->postJson('/api/guests-module/company-groups', [
            'name' => 'Test Group', 'credit_limit' => 10000,
        ]);
        $res->assertStatus(201)->assertJsonPath('message', 'Group created');
        $id = $res->json('data.id');

        $this->actingAs($this->user)->getJson('/api/guests-module/company-groups')->assertStatus(200);
        $this->actingAs($this->user)->putJson("/api/guests-module/company-groups/{$id}", ['name' => 'Updated Group'])->assertStatus(200);

        // Exposure
        $exp = $this->actingAs($this->user)->getJson("/api/guests-module/company-groups/{$id}/exposure");
        $exp->assertStatus(200)->assertJsonStructure(['data' => ['total_credit_limit', 'total_exposure', 'utilization_pct']]);

        $this->actingAs($this->user)->deleteJson("/api/guests-module/company-groups/{$id}")->assertStatus(200);
    }

    // ── 5.5 Company Notes ────────────────────────────────────────

    public function test_company_notes_crud_works()
    {
        $companyId = DB::table('companies')->insertGetId(['team_id' => $this->team->id, 'name' => 'Test Co', 'created_at' => now(), 'updated_at' => now()]);

        $res = $this->actingAs($this->user)->postJson("/api/guests-module/companies/{$companyId}/notes", ['body' => 'Test note']);
        $res->assertStatus(201)->assertJsonPath('message', 'Note added');
        $noteId = $res->json('data.id');

        $this->actingAs($this->user)->getJson("/api/guests-module/companies/{$companyId}/notes")->assertStatus(200);
        $this->actingAs($this->user)->deleteJson("/api/guests-module/companies/{$companyId}/notes/{$noteId}")->assertStatus(200);
    }

    // ── 5.6 Blocked Guests ───────────────────────────────────────

    public function test_block_unblock_works()
    {
        $res = $this->actingAs($this->user)->postJson('/api/guests-module/blocked-guests', [
            'guest_name' => 'Bad Guest', 'reason' => 'Damage to property', 'block_type' => 'permanent',
        ]);
        $res->assertStatus(201)->assertJsonPath('message', 'Guest blocked');
        $id = $res->json('data.id');

        $this->actingAs($this->user)->getJson('/api/guests-module/blocked-guests')->assertStatus(200);
        $this->actingAs($this->user)->deleteJson("/api/guests-module/blocked-guests/{$id}")->assertStatus(200);
        $this->assertDatabaseMissing('blocked_guests', ['id' => $id]);
    }

    // ── 5.7 Turnaway Logs ────────────────────────────────────────

    public function test_turnaway_log_creates()
    {
        $res = $this->actingAs($this->user)->postJson('/api/guests-module/turnaway-logs', [
            'date' => now()->toDateString(), 'guest_name' => 'Walk-in Guest', 'room_type_requested' => 'Deluxe',
        ]);
        $res->assertStatus(201)->assertJsonPath('message', 'Turnaway log created');
        $this->assertDatabaseHas('turnaway_logs', ['guest_name' => 'Walk-in Guest', 'team_id' => $this->team->id]);
    }

    // ── 5.8 Turnaway Reasons ─────────────────────────────────────

    public function test_turnaway_reason_crud_works()
    {
        $res = $this->actingAs($this->user)->postJson('/api/guests-module/turnaway-reasons', ['name' => 'No Availability', 'status' => 1]);
        $res->assertStatus(201);
        $id = $res->json('data.id');

        $this->actingAs($this->user)->getJson('/api/guests-module/turnaway-reasons')->assertStatus(200);
        $this->actingAs($this->user)->putJson("/api/guests-module/turnaway-reasons/{$id}", ['name' => 'Fully Booked'])->assertStatus(200);
        $this->actingAs($this->user)->deleteJson("/api/guests-module/turnaway-reasons/{$id}")->assertStatus(200);
    }

    // ── 5.9 Customer Notes ───────────────────────────────────────

    public function test_customer_notes_crud_works()
    {
        $res = $this->actingAs($this->user)->postJson("/api/guests-module/customers/{$this->customer->id}/notes", [
            'body' => 'Prefers quiet room', 'type' => 'preference',
        ]);
        $res->assertStatus(201)->assertJsonPath('message', 'Note added');
        $noteId = $res->json('data.id');

        $this->actingAs($this->user)->getJson("/api/guests-module/customers/{$this->customer->id}/notes")->assertStatus(200);
        $this->actingAs($this->user)->deleteJson("/api/guests-module/customers/{$this->customer->id}/notes/{$noteId}")->assertStatus(200);
    }

    // ── 5.10 Comments ────────────────────────────────────────────

    public function test_comments_work()
    {
        $res = $this->actingAs($this->user)->postJson('/api/guests-module/comments', [
            'commentable_type' => 'App\\Customer',
            'commentable_id'   => $this->customer->id,
            'body'             => 'Great guest!',
        ]);
        $res->assertStatus(201)->assertJsonPath('message', 'Comment added');
        $id = $res->json('data.id');

        $this->actingAs($this->user)->getJson('/api/guests-module/comments')->assertStatus(200);
        $this->actingAs($this->user)->deleteJson("/api/guests-module/comments/{$id}")->assertStatus(200);
    }

    // ── 5.11 Highlights ──────────────────────────────────────────

    public function test_highlights_crud_works()
    {
        $res = $this->actingAs($this->user)->postJson('/api/guests-module/highlights', ['name' => 'VIP', 'color' => '#FF0000']);
        $res->assertStatus(201)->assertJsonPath('message', 'Highlight created');
        $id = $res->json('data.id');

        $this->actingAs($this->user)->getJson('/api/guests-module/highlights')->assertStatus(200);
        $this->actingAs($this->user)->putJson("/api/guests-module/highlights/{$id}", ['color' => '#00FF00'])->assertStatus(200);
        $this->actingAs($this->user)->deleteJson("/api/guests-module/highlights/{$id}")->assertStatus(200);
    }

    // ── 5.12 Duplicate Merge ─────────────────────────────────────

    public function test_merge_combines_records_correctly()
    {
        $duplicate = Customer::create([
            'team_id' => $this->team->id,
            'name'    => 'Ahmed Al-Rashid (Duplicate)',
            'phone'   => '+966500000099',
        ]);

        // Create a reservation linked to duplicate
        DB::table('reservations')->insert([
            'team_id'    => $this->team->id,
            'code'       => 'RES-MERGE',
            'guest_id'   => $duplicate->id,
            'check_in'   => now(),
            'check_out'  => now()->addDays(2),
            'status'     => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/guests-module/customers/merge', [
            'primary_id'   => $this->customer->id,
            'duplicate_id' => $duplicate->id,
        ]);

        $response->assertStatus(200)->assertJsonPath('message', 'Customers merged successfully');
        $this->assertSoftDeleted('customers', ['id' => $duplicate->id]);
        $this->assertDatabaseHas('merge_logs', ['primary_customer_id' => $this->customer->id, 'merged_customer_id' => $duplicate->id]);
    }

    public function test_unauthenticated_returns_401()
    {
        $this->getJson('/api/guests-module/guests')->assertStatus(401);
        $this->getJson('/api/guests-module/customers')->assertStatus(401);
        $this->getJson('/api/guests-module/companies')->assertStatus(401);
    }
}
