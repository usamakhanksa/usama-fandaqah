<?php

namespace Tests\Feature;

use App\Models\ReservationMessageLog;
use App\Models\ReservationAuditLock;
use App\Models\Team;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReservationMessagesAuditLocksTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->user = User::create([
            'name' => 'Manager',
            'email' => 'manager' . rand(1, 9999) . '@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->team = Team::create([
            'name' => 'Test Hotel',
            'owner_id' => $this->user->id,
        ]);

        $this->user->update(['current_team_id' => $this->team->id]);
    }

    // ── 2.18 Messages ──────────────────────────────────────────

    public function test_message_history_displays()
    {
        ReservationMessageLog::create([
            'team_id'        => $this->team->id,
            'reservation_id' => 1,
            'type'           => 'sms',
            'message'        => 'Your reservation is confirmed.',
            'status'         => 'sent',
            'sent_at'        => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/messages');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'type', 'status', 'message']]]);
    }

    public function test_messages_filter_by_type()
    {
        ReservationMessageLog::create(['team_id' => $this->team->id, 'reservation_id' => 1, 'type' => 'sms', 'message' => 'SMS msg', 'status' => 'sent']);
        ReservationMessageLog::create(['team_id' => $this->team->id, 'reservation_id' => 1, 'type' => 'email', 'message' => 'Email msg', 'status' => 'sent']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/messages?type=email');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('email', $response->json('data.0.type'));
    }

    public function test_messages_filter_by_status()
    {
        ReservationMessageLog::create(['team_id' => $this->team->id, 'reservation_id' => 1, 'type' => 'sms', 'message' => 'msg1', 'status' => 'sent']);
        ReservationMessageLog::create(['team_id' => $this->team->id, 'reservation_id' => 1, 'type' => 'sms', 'message' => 'msg2', 'status' => 'failed']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/messages?status=failed');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_message_show_returns_record()
    {
        $msg = ReservationMessageLog::create([
            'team_id'        => $this->team->id,
            'reservation_id' => 1,
            'type'           => 'whatsapp',
            'message'        => 'Hello guest!',
            'status'         => 'sent',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/reservations/messages/{$msg->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $msg->id)
            ->assertJsonPath('data.type', 'whatsapp');
    }

    public function test_send_message_creates_log()
    {
        $resId = DB::table('reservations')->insertGetId([
            'team_id'    => $this->team->id,
            'code'       => 'RES-MSG-001',
            'check_in'   => now(),
            'check_out'  => now()->addDays(2),
            'status'     => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/reservations/messages', [
                'reservation_id' => $resId,
                'type'           => 'sms',
                'message'        => 'Your check-in is tomorrow.',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Message sent');

        $this->assertDatabaseHas('reservation_message_logs', [
            'reservation_id' => $resId,
            'type'           => 'sms',
            'status'         => 'sent',
        ]);
    }

    public function test_send_message_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/reservations/messages', []);

        $response->assertStatus(422);
    }

    // ── 2.19 Audit Locks ───────────────────────────────────────

    public function test_audit_locks_display()
    {
        DB::table('reservation_audit_locks')->insert([
            'reservation_id'  => 1,
            'locked_from_date' => now()->toDateString(),
            'locked_by_audit' => 1,
            'team_id'         => $this->team->id,
            'created_at'      => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/audit-locks');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_audit_locks_filter_by_date()
    {
        DB::table('reservation_audit_locks')->insert([
            'reservation_id'  => 1,
            'locked_from_date' => now()->toDateString(),
            'locked_by_audit' => 1,
            'team_id'         => $this->team->id,
            'created_at'      => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/audit-locks?date=' . now()->toDateString());

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_audit_lock_show_returns_record()
    {
        DB::table('reservation_audit_locks')->insert([
            'reservation_id'  => 5,
            'locked_from_date' => now()->toDateString(),
            'locked_by_audit' => 1,
            'team_id'         => $this->team->id,
            'created_at'      => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/reservations/audit-locks/5');

        $response->assertStatus(200)
            ->assertJsonPath('data.reservation_id', 5);
    }

    public function test_messages_unauthenticated_returns_401()
    {
        $this->getJson('/api/reservations/messages')->assertStatus(401);
    }

    public function test_audit_locks_unauthenticated_returns_401()
    {
        $this->getJson('/api/reservations/audit-locks')->assertStatus(401);
    }
}
