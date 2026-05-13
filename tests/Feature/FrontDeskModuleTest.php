<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Unit;
use App\Models\Team;
use App\Models\WakeUpCall;
use App\Models\IptvGuestNeed;
use App\Models\StayChargeConfig;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FrontDeskModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Team $team;
    protected Reservation $reservation;
    protected Unit $unit;

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

        $this->unit = Unit::create([
            'team_id' => $this->team->id,
            'unit_number' => '101',
            'name' => 'Room 101',
            'status' => 1,
            'is_active' => true,
        ]);

        $this->reservation = Reservation::create([
            'team_id'   => $this->team->id,
            'code'      => 'RES-FD-001',
            'check_in'  => now()->toDateString(),
            'check_out' => now()->addDays(2)->toDateString(),
            'status'    => 'confirmed',
        ]);
    }

    // ── 3.1 Check-in ─────────────────────────────────────────────

    public function test_arrivals_list_returns_confirmed_reservations()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/front-desk/arrivals?date=' . now()->toDateString());

        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_checkin_workflow_completes()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/front-desk/check-in/{$this->reservation->id}", [
                'unit_id'        => $this->unit->id,
                'signature_data' => 'test-sig',
                'deposit_amount' => 100,
                'payment_method' => 'cash',
            ]);

        $response->assertStatus(200)->assertJsonPath('message', 'Check-in completed');
        $this->assertDatabaseHas('reservations', ['id' => $this->reservation->id, 'status' => 'checked_in']);
    }

    // ── 3.2 Check-out ────────────────────────────────────────────

    public function test_departures_list_returns_checked_in_reservations()
    {
        $this->reservation->update(['status' => 'checked_in']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/front-desk/departures?date=' . now()->toDateString());

        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_checkout_workflow_completes()
    {
        $this->reservation->update(['status' => 'checked_in', 'unit_id' => $this->unit->id]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/front-desk/check-out/{$this->reservation->id}", [
                'balance_action' => 'waived',
                'balance_amount' => 50,
            ]);

        $response->assertStatus(200)->assertJsonPath('message', 'Check-out completed');
        $this->assertDatabaseHas('reservations', ['id' => $this->reservation->id, 'status' => 'checked_out']);
        $this->assertDatabaseHas('checkout_balance_transfers', ['reservation_id' => $this->reservation->id, 'transfer_type' => 'waived']);
    }

    public function test_folio_returns_reservation_financials()
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/front-desk/folio/{$this->reservation->id}");

        $response->assertStatus(200)->assertJsonStructure(['data' => ['reservation', 'charges', 'payments', 'balance']]);
    }

    // ── 3.3 Walk-in ──────────────────────────────────────────────

    public function test_walkin_creates_reservation()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/front-desk/walk-in', [
                'guest_name'  => 'Walk-in Guest',
                'guest_phone' => '+966500000000',
                'unit_id'     => $this->unit->id,
                'check_in'    => now()->toDateString(),
                'check_out'   => now()->addDay()->toDateString(),
                'total_price' => 300,
            ]);

        $response->assertStatus(201)->assertJsonPath('message', 'Walk-in created');
        $this->assertDatabaseHas('reservations', ['code' => $response->json('data.code'), 'team_id' => $this->team->id]);
    }

    public function test_walkin_with_direct_checkin_sets_status()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/front-desk/walk-in', [
                'guest_name'     => 'Direct Guest',
                'unit_id'        => $this->unit->id,
                'check_in'       => now()->toDateString(),
                'check_out'      => now()->addDay()->toDateString(),
                'direct_checkin' => true,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('reservations', ['status' => 'checked_in', 'unit_id' => $this->unit->id]);
    }

    // ── 3.4 Guest Registration ───────────────────────────────────

    public function test_registration_saves_correctly()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/front-desk/registration/{$this->reservation->id}", [
                'full_name'  => 'Ahmed Al-Rashid',
                'id_type'    => 'national_id',
                'id_number'  => '1234567890',
                'nationality'=> 'Saudi',
                'gender'     => 'male',
            ]);

        $response->assertStatus(200)->assertJsonPath('message', 'Registration saved');
        $this->assertDatabaseHas('guests', ['id_number' => '1234567890', 'name' => 'Ahmed Al-Rashid']);
    }

    // ── 3.5 Room Assignment ──────────────────────────────────────

    public function test_unassigned_reservations_list()
    {
        $this->reservation->update(['unit_id' => null]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/front-desk/unassigned-reservations');

        $response->assertStatus(200)->assertJsonStructure(['data']);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_room_assignment_works()
    {
        $this->reservation->update(['unit_id' => null]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/front-desk/room-assignment', [
                'reservation_id' => $this->reservation->id,
                'unit_id'        => $this->unit->id,
            ]);

        $response->assertStatus(200)->assertJsonPath('message', 'Room assigned');
        $this->assertDatabaseHas('reservations', ['id' => $this->reservation->id, 'unit_id' => $this->unit->id]);
    }

    // ── 3.6 Room Swap ────────────────────────────────────────────

    public function test_room_swap_completes()
    {
        $this->reservation->update(['status' => 'checked_in', 'unit_id' => $this->unit->id]);

        $newUnit = Unit::create(['team_id' => $this->team->id, 'unit_number' => '102', 'name' => 'Room 102', 'status' => 1, 'is_active' => true]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/front-desk/room-swap', [
                'reservation_id' => $this->reservation->id,
                'new_unit_id'    => $newUnit->id,
                'reason'         => 'Guest request',
            ]);

        $response->assertStatus(200)->assertJsonPath('message', 'Room swapped');
        $this->assertDatabaseHas('reservations', ['id' => $this->reservation->id, 'unit_id' => $newUnit->id]);
    }

    // ── 3.7 Early Check-in ───────────────────────────────────────

    public function test_early_checkin_charge_calculates()
    {
        StayChargeConfig::create([
            'team_id'       => $this->team->id,
            'charge_type'   => 'early_checkin',
            'tier_from_hour'=> 0,
            'tier_to_hour'  => 14,
            'rate_type'     => 'fixed',
            'rate_amount'   => 50,
            'applies_to'    => 'all',
            'is_active'     => true,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/front-desk/early-check-in/charge?reservation_id={$this->reservation->id}&actual_time=10:00");

        $response->assertStatus(200)->assertJsonStructure(['data' => ['charge_amount']]);
    }

    public function test_early_checkin_charge_applies_correctly()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/front-desk/early-check-in', [
                'reservation_id' => $this->reservation->id,
                'actual_time'    => '10:00',
                'charge_amount'  => 50,
            ]);

        $response->assertStatus(200)->assertJsonPath('message', 'Early check-in charge applied');
    }

    // ── 3.8 Late Checkout ────────────────────────────────────────

    public function test_late_checkout_charge_applies_correctly()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/front-desk/late-checkout', [
                'reservation_id' => $this->reservation->id,
                'actual_time'    => '14:00',
                'charge_amount'  => 75,
            ]);

        $response->assertStatus(200)->assertJsonPath('message', 'Late checkout charge applied');
    }

    // ── 3.9 No-Show ──────────────────────────────────────────────

    public function test_noshow_marks_reservation_correctly()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/front-desk/no-show/{$this->reservation->id}", [
                'reason' => 'Guest did not arrive',
                'cancel' => true,
            ]);

        $response->assertStatus(200)->assertJsonPath('message', 'Reservation marked as no-show');
        $this->assertDatabaseHas('reservations', ['id' => $this->reservation->id, 'status' => 'no_show', 'noshow_flag' => 1]);
    }

    // ── 3.10 Wake-up Calls ───────────────────────────────────────

    public function test_wakeup_call_crud_works()
    {
        // Create
        $response = $this->actingAs($this->user)
            ->postJson('/api/front-desk/wake-up-calls', [
                'reservation_id' => $this->reservation->id,
                'unit_number'    => '101',
                'call_time'      => '07:00',
                'call_date'      => now()->toDateString(),
                'repeat'         => 'once',
            ]);
        $response->assertStatus(201);
        $id = $response->json('data.id');

        // List
        $this->actingAs($this->user)->getJson('/api/front-desk/wake-up-calls')->assertStatus(200);

        // Complete
        $this->actingAs($this->user)->postJson("/api/front-desk/wake-up-calls/{$id}/complete")->assertStatus(200);
        $this->assertDatabaseHas('wake_up_calls', ['id' => $id, 'status' => 'completed']);

        // Delete
        $this->actingAs($this->user)->deleteJson("/api/front-desk/wake-up-calls/{$id}")->assertStatus(200);
        $this->assertDatabaseMissing('wake_up_calls', ['id' => $id]);
    }

    // ── 3.11 IPTV Guest Needs ────────────────────────────────────

    public function test_iptv_needs_crud_works()
    {
        // Create
        $response = $this->actingAs($this->user)
            ->postJson('/api/front-desk/iptv-needs', [
                'reservation_id'  => $this->reservation->id,
                'request_type'    => 'room_service',
                'request_details' => 'Extra towels',
            ]);
        $response->assertStatus(201);
        $id = $response->json('data.id');

        // List
        $this->actingAs($this->user)->getJson('/api/front-desk/iptv-needs')->assertStatus(200);

        // Update status
        $this->actingAs($this->user)->putJson("/api/front-desk/iptv-needs/{$id}", ['status' => 'completed'])->assertStatus(200);
        $this->assertDatabaseHas('iptv_guest_needs', ['id' => $id, 'status' => 'completed']);

        // Delete
        $this->actingAs($this->user)->deleteJson("/api/front-desk/iptv-needs/{$id}")->assertStatus(200);
        $this->assertDatabaseMissing('iptv_guest_needs', ['id' => $id]);
    }

    // ── 3.12 Registration Cards ──────────────────────────────────

    public function test_registration_card_prints_correctly()
    {
        $this->reservation->update(['status' => 'checked_in']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/front-desk/registration-cards');

        $response->assertStatus(200)->assertJsonStructure(['data']);

        $detail = $this->actingAs($this->user)
            ->getJson("/api/front-desk/registration-cards/{$this->reservation->id}");

        $detail->assertStatus(200)->assertJsonStructure(['data' => ['reservation']]);
    }

    // ── 3.13 Balance Transfer ────────────────────────────────────

    public function test_balance_transfer_creates_correct_record()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/front-desk/balance-transfer', [
                'reservation_id' => $this->reservation->id,
                'transfer_type'  => 'waived',
                'amount'         => 150,
                'notes'          => 'Goodwill waiver',
            ]);

        $response->assertStatus(201)->assertJsonPath('message', 'Balance transfer recorded');
        $this->assertDatabaseHas('checkout_balance_transfers', [
            'reservation_id' => $this->reservation->id,
            'transfer_type'  => 'waived',
            'amount'         => 150,
        ]);
    }

    public function test_unauthenticated_front_desk_returns_401()
    {
        $this->getJson('/api/front-desk/arrivals')->assertStatus(401);
        $this->getJson('/api/front-desk/wake-up-calls')->assertStatus(401);
        $this->getJson('/api/front-desk/iptv-needs')->assertStatus(401);
    }
}
