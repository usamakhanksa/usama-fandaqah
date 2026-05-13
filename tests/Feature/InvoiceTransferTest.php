<?php

namespace Tests\Feature;

use App\Models\InvoiceTransfer;
use App\Promissory;
use App\Models\Reservation;
use App\Company;
use App\Team;
use App\User;
use App\Services\InvoiceTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvoiceTransferTest extends TestCase
{
    public function test_invoice_can_be_transferred_to_ar()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $teamId = 1;
        $user = User::withoutEvents(fn() => User::create([
            'name' => 'Admin', 
            'email' => uniqid().'@test.com', 
            'password' => 'password', 
            'role_id' => 1,
            'current_team_id' => $teamId
        ]));
        
        $company = Company::create([
            'name' => 'Test Corp',
            'team_id' => $teamId
        ]);

        $reservation = Reservation::withoutEvents(fn() => Reservation::create([
            'total_price' => 1000,
            'check_in' => now(),
            'check_out' => now()->addDays(2),
            'stay_type' => 'checkin',
            'code' => 'RSV-'.uniqid()
        ]));

        $service = new InvoiceTransferService();
        $transfer = $service->transferToAR([
            'reservation_id' => $reservation->id,
            'company_id' => $company->id,
            'amount' => 1000,
            'notes' => 'Test transfer'
        ], $user->id);

        $this->assertEquals(1000, $transfer->transfer_amount);
        $this->assertDatabaseHas('invoice_transfers', [
            'reservation_id' => $reservation->id,
            'company_id' => $company->id,
            'transfer_amount' => 1000
        ]);
        
        $this->assertDatabaseHas('promissories', [
            'reservation_id' => $reservation->id,
            'company_id' => $company->id,
            'total_amount' => 1000
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
