<?php

namespace Tests\Feature;

use App\Models\NightAuditLog;
use App\Reservation;
use App\Team;
use App\Transaction;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuditEnforcementTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => 'Audit Admin',
            'email' => 'audit_'.rand().'@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->team = Team::create([
            'name' => 'Audit Hotel',
            'slug' => 'audit-hotel-' . rand(),
            'owner_id' => $this->user->id,
            'business_date' => '2026-05-10',
        ]);
        
        $this->user->update(['current_team_id' => $this->team->id]);

        // Close 2026-05-09
        NightAuditLog::create([
            'team_id' => $this->team->id,
            'business_date' => '2026-05-09',
            'status' => 'completed',
            'triggered_by' => 'manual'
        ]);
    }

    /** @test */
    public function it_blocks_creating_transaction_on_closed_date()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Cannot create transactions for a closed business date.');

        Transaction::create([
            'team_id' => $this->team->id,
            'amount' => 100,
            'meta' => ['date' => '2026-05-09'],
            'type' => 'deposit',
            'is_public' => true,
            'payable_type' => 'App\Team',
            'payable_id' => $this->team->id
        ]);
    }

    /** @test */
    public function it_blocks_updating_frozen_transaction()
    {
        $transaction = Transaction::create([
            'team_id' => $this->team->id,
            'amount' => 1000,
            'is_freezed' => false,
            'payable_type' => 'App\Team',
            'payable_id' => $this->team->id,
            'type' => 'deposit',
            'is_public' => true,
            'meta' => ['date' => '2026-05-10']
        ]);

        $transaction->update(['is_freezed' => true]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('This transaction is frozen by Night Audit and cannot be modified.');

        $transaction->update(['amount' => 2000]);
    }

    /** @test */
    public function it_blocks_creating_reservation_on_closed_date()
    {
        $guest = \App\Guest::create(['name' => 'Test Guest', 'team_id' => $this->team->id]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Cannot create reservations for a closed business date.');

        Reservation::create([
            'team_id' => $this->team->id,
            'date_in' => '2026-05-09',
            'date_out' => '2026-05-11',
            'guest_id' => $guest->id,
            'unit_id' => 1,
            'status' => 'confirmed'
        ]);
    }
}
