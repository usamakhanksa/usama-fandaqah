<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\PaymentCorrectionLog;
use App\Models\Permission;
use App\Models\Role;
use App\Team;
use App\Transaction;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentCorrectionTest extends TestCase
{
    use RefreshDatabase;

    private User  $user;
    private Team  $team;

    protected function setUp(): void
    {
        parent::setUp();

        // Create team and user
        $this->team = Team::factory()->create(['business_date' => now()->toDateString()]);
        $this->user = User::factory()->create(['current_team_id' => $this->team->id]);

        // Attach the finance.payment_correction permission
        $permission = Permission::firstOrCreate(
            ['slug' => 'finance.payment_correction'],
            ['name' => 'finance.payment_correction', 'group' => 'finance']
        );
        $role = Role::firstOrCreate(['slug' => 'test-corrector'], ['name' => 'Test Corrector']);
        $role->permissions()->syncWithoutDetaching([
            $permission->id => [
                'enabled' => true, 'anyone' => true,
                'can_create' => true, 'can_edit' => true,
                'can_view' => true, 'can_remove' => true,
            ],
        ]);
        $role->users()->attach($this->user->id);
    }

    // ─── Helper ─────────────────────────────────────────────────────────────────

    private function makeFrozenTx(float $amount = 500.00, string $method = 'cash'): Transaction
    {
        $id = DB::table('transactions')->insertGetId([
            'payable_type'   => Team::class,
            'payable_id'     => $this->team->id,
            'type'           => 'deposit',
            'amount'         => (int) ($amount * 100),
            'amount_without_tax' => (int) ($amount * 100),
            'confirmed'      => 1,
            'is_public'      => 1,
            'is_freezed'     => 1,
            'uuid'           => (string) Str::uuid(),
            'team_id'        => $this->team->id,
            'meta'           => json_encode(['payment_type' => $method]),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        return Transaction::findOrFail($id);
    }

    // ─── Tests ───────────────────────────────────────────────────────────────────

    /** @test */
    public function it_rejects_unauthenticated_requests()
    {
        $this->postJson('/api/finance/payment-correction', [])
            ->assertStatus(401);
    }

    /** @test */
    public function it_rejects_users_without_permission()
    {
        $userNoPerms = User::factory()->create(['current_team_id' => $this->team->id]);
        $tx = $this->makeFrozenTx();

        $this->actingAs($userNoPerms, 'sanctum')
            ->postJson('/api/finance/payment-correction', [
                'frozen_transaction_id' => $tx->id,
                'correct_payment_type'  => 'mada',
                'correct_amount'        => 500.00,
            ])
            ->assertStatus(403);
    }

    /** @test */
    public function it_rejects_non_frozen_transactions()
    {
        $txId = DB::table('transactions')->insertGetId([
            'payable_type'       => Team::class,
            'payable_id'         => $this->team->id,
            'type'               => 'deposit',
            'amount'             => 50000,
            'amount_without_tax' => 50000,
            'confirmed'          => 1,
            'is_public'          => 1,
            'is_freezed'         => 0,     // ← NOT frozen
            'uuid'               => (string) Str::uuid(),
            'team_id'            => $this->team->id,
            'meta'               => json_encode(['payment_type' => 'cash']),
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/finance/payment-correction', [
                'frozen_transaction_id' => $txId,
                'correct_payment_type'  => 'mada',
                'correct_amount'        => 500.00,
            ])
            ->assertStatus(422)
            ->assertJson(['message' => 'Only frozen transactions can be corrected.']);
    }

    /** @test */
    public function it_corrects_wrong_payment_method_by_creating_withdraw_and_deposit()
    {
        $tx = $this->makeFrozenTx(500.00, 'cash');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/finance/payment-correction', [
                'frozen_transaction_id' => $tx->id,
                'correct_payment_type'  => 'mada',    // different method
                'correct_amount'        => 500.00,    // same amount
                'reason'                => 'Customer paid by Mada, not cash.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.correction_type', 'wrong_payment_method');

        $data = $response->json('data');

        $this->assertNotNull($data['correction_withdraw_id']);
        $this->assertNotNull($data['correction_deposit_id']);
        $this->assertNull(null); // original is untouched

        // Verify original unchanged
        $tx->refresh();
        $this->assertTrue((bool) $tx->is_freezed, 'Original transaction must remain frozen');

        // Verify correction transactions link back
        $this->assertDatabaseHas('transactions', [
            'id'                          => $data['correction_withdraw_id'],
            'correction_of_transaction_id' => $tx->id,
            'type'                        => 'withdraw',
        ]);
        $this->assertDatabaseHas('transactions', [
            'id'                          => $data['correction_deposit_id'],
            'correction_of_transaction_id' => $tx->id,
            'type'                        => 'deposit',
        ]);
    }

    /** @test */
    public function it_corrects_overcharge_by_creating_only_a_withdraw()
    {
        $tx = $this->makeFrozenTx(600.00, 'mada');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/finance/payment-correction', [
                'frozen_transaction_id' => $tx->id,
                'correct_payment_type'  => 'mada',  // same method
                'correct_amount'        => 500.00,  // less → overcharge
                'reason'                => 'Guest was overcharged by 100 SAR.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.correction_type', 'overcharge');

        $data = $response->json('data');
        $this->assertNotNull($data['correction_withdraw_id']);
        $this->assertNull($data['correction_deposit_id']);

        // Withdraw should be for the 100 SAR difference
        $withdraw = DB::table('transactions')->find($data['correction_withdraw_id']);
        $this->assertEquals(-10000, $withdraw->amount); // 100.00 * 100 = 10000 in negative
    }

    /** @test */
    public function it_corrects_undercharge_by_creating_only_a_deposit()
    {
        $tx = $this->makeFrozenTx(400.00, 'cash');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/finance/payment-correction', [
                'frozen_transaction_id' => $tx->id,
                'correct_payment_type'  => 'cash',  // same method
                'correct_amount'        => 500.00,  // more → undercharge
                'reason'                => 'Guest owed 100 SAR more.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.correction_type', 'undercharge');

        $data = $response->json('data');
        $this->assertNull($data['correction_withdraw_id']);
        $this->assertNotNull($data['correction_deposit_id']);

        $deposit = DB::table('transactions')->find($data['correction_deposit_id']);
        $this->assertEquals(10000, $deposit->amount); // 100.00 * 100
    }

    /** @test */
    public function it_corrects_both_wrong_method_and_amount()
    {
        $tx = $this->makeFrozenTx(300.00, 'cash');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/finance/payment-correction', [
                'frozen_transaction_id' => $tx->id,
                'correct_payment_type'  => 'credit',
                'correct_amount'        => 450.00,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.correction_type', 'wrong_method_and_amount');

        $data = $response->json('data');
        $this->assertNotNull($data['correction_withdraw_id']);
        $this->assertNotNull($data['correction_deposit_id']);
    }

    /** @test */
    public function it_creates_a_correction_log_and_activity_log()
    {
        $tx = $this->makeFrozenTx(200.00, 'cash');

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/finance/payment-correction', [
                'frozen_transaction_id' => $tx->id,
                'correct_payment_type'  => 'mada',
                'correct_amount'        => 200.00,
                'reason'                => 'Test reason',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('payment_correction_logs', [
            'frozen_transaction_id' => $tx->id,
            'created_by'            => $this->user->id,
            'correction_type'       => 'wrong_payment_method',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'log_name'  => 'finance',
            'causer_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_rejects_transactions_from_another_team()
    {
        $otherTeam = Team::factory()->create(['business_date' => now()->toDateString()]);
        $txId = DB::table('transactions')->insertGetId([
            'payable_type'       => Team::class,
            'payable_id'         => $otherTeam->id,
            'type'               => 'deposit',
            'amount'             => 50000,
            'amount_without_tax' => 50000,
            'confirmed'          => 1,
            'is_public'          => 1,
            'is_freezed'         => 1,
            'uuid'               => (string) Str::uuid(),
            'team_id'            => $otherTeam->id,
            'meta'               => json_encode(['payment_type' => 'cash']),
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/finance/payment-correction', [
                'frozen_transaction_id' => $txId,
                'correct_payment_type'  => 'mada',
                'correct_amount'        => 500.00,
            ])
            ->assertStatus(403);
    }

    /** @test */
    public function it_returns_correction_history_for_the_team()
    {
        $tx = $this->makeFrozenTx(100.00, 'cash');

        // Seed one log
        PaymentCorrectionLog::create([
            'team_id'               => $this->team->id,
            'frozen_transaction_id' => $tx->id,
            'created_by'            => $this->user->id,
            'original_payment_type' => 'cash',
            'original_amount'       => 100.00,
            'correct_payment_type'  => 'mada',
            'correct_amount'        => 100.00,
            'correction_type'       => 'wrong_payment_method',
            'posted_business_date'  => now()->toDateString(),
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/finance/payment-corrections')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
