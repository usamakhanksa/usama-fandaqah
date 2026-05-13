<?php

namespace Tests\Feature;

use App\DigitalSignature;
use App\Models\ReservationContract;
use App\Models\ReservationStayExtension;
use App\Models\Team;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReservationExtensionsContractsSignaturesTest extends TestCase
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

    protected function actingAsWithTeam()
    {
        $user = \Mockery::mock($this->user)->makePartial();
        $user->shouldReceive('currentTeam')->andReturn($this->team);
        return $this->actingAs($user);
    }

    // 2.13 Extension History

    public function test_extension_history_displays_correctly()
    {
        ReservationStayExtension::create([
            'team_id' => $this->team->id,
            'reservation_id' => 1,
            'old_check_out' => now()->addDays(3),
            'new_check_out' => now()->addDays(5),
            'extension_days' => 2,
            'reason' => 'Guest request',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAsWithTeam()
            ->getJson('/api/reservations/extensions');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_extension_history_filters_by_date()
    {
        ReservationStayExtension::create([
            'team_id' => $this->team->id,
            'reservation_id' => 1,
            'old_check_out' => now()->addDays(3),
            'new_check_out' => now()->addDays(5),
            'extension_days' => 2,
            'reason' => 'Test',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAsWithTeam()
            ->getJson('/api/reservations/extensions?date=' . now()->toDateString());

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_extension_show_returns_details()
    {
        $ext = ReservationStayExtension::create([
            'team_id' => $this->team->id,
            'reservation_id' => 1,
            'old_check_out' => now()->addDays(3),
            'new_check_out' => now()->addDays(6),
            'extension_days' => 3,
            'reason' => 'Business trip extended',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAsWithTeam()
            ->getJson("/api/reservations/extensions/{$ext->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $ext->id);
    }

    // 2.14 Digital Contracts

    public function test_contracts_list_displays_correctly()
    {
        ReservationContract::create([
            'team_id' => $this->team->id,
            'reservation_id' => 1,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'contract_number' => 'CNT-2026-000001',
            'status' => 'draft',
            'generated_at' => now(),
            'generated_by' => $this->user->id,
            'version' => 1,
        ]);

        $response = $this->actingAsWithTeam()
            ->getJson('/api/reservations/contracts');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_contracts_filter_by_status()
    {
        ReservationContract::create([
            'team_id' => $this->team->id,
            'reservation_id' => 1,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'status' => 'signed',
            'generated_at' => now(),
            'signed_at' => now(),
            'version' => 1,
        ]);

        $response = $this->actingAsWithTeam()
            ->getJson('/api/reservations/contracts?status=signed');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_contract_generation_requires_valid_reservation()
    {
        $response = $this->actingAsWithTeam()
            ->postJson('/api/reservations/contracts', [
                'reservation_id' => 99999,
                'status' => 'draft',
            ]);

        // Should fail validation since reservation doesn't exist
        $response->assertStatus(422);
    }

    // 2.15 Digital Signatures

    public function test_signature_records_display()
    {
        DigitalSignature::create([
            'team_id' => $this->team->id,
            'ref_id' => 1,
            'type' => 'contract',
            'user_id' => $this->user->id,
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAsWithTeam()
            ->getJson('/api/reservations/signatures');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_signatures_filter_by_type()
    {
        DigitalSignature::create([
            'team_id' => $this->team->id,
            'ref_id' => 1,
            'type' => 'registration',
            'user_id' => $this->user->id,
            'ip_address' => '192.168.1.1',
        ]);

        $response = $this->actingAsWithTeam()
            ->getJson('/api/reservations/signatures?type=registration');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_signature_show_returns_record()
    {
        $sig = DigitalSignature::create([
            'team_id' => $this->team->id,
            'ref_id' => 1,
            'type' => 'contract',
            'user_id' => $this->user->id,
            'ip_address' => '10.0.0.1',
        ]);

        $response = $this->actingAsWithTeam()
            ->getJson("/api/reservations/signatures/{$sig->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $sig->id)
            ->assertJsonPath('data.type', 'contract');
    }
}
