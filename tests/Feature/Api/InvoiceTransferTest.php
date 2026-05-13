<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\User;
use App\Models\Reservation;
use App\Company;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceTransferTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = factory(User::class)->create();
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin', 'team_id' => 1]);
        $permission = Permission::create(['name' => 'AR Transfer', 'slug' => 'ar.invoice_transfer']);
        
        $role->permissions()->attach($permission->id, ['enabled' => true]);
        $this->user->roles()->attach($role->id);
    }

    /** @test */
    public function it_can_transfer_invoice_to_ar()
    {
        $reservation = factory(Reservation::class)->create(['team_id' => 1, 'stay_type' => 'checkin']);
        $company = factory(\App\Company::class)->create(['team_id' => 1]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/ar/invoice-transfer', [
                'reservation_id' => $reservation->id,
                'company_id' => $company->id,
                'amount' => 500,
                'notes' => 'Test transfer'
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Invoice successfully transferred to AR');

        $this->assertDatabaseHas('invoice_transfers', [
            'reservation_id' => $reservation->id,
            'transfer_amount' => 500
        ]);
    }

    /** @test */
    public function it_denies_transfer_without_permission()
    {
        $unauthorizedUser = factory(User::class)->create();
        $reservation = factory(Reservation::class)->create();

        $response = $this->actingAs($unauthorizedUser, 'api')
            ->postJson('/api/ar/invoice-transfer', [
                'reservation_id' => $reservation->id,
                'amount' => 500
            ]);

        $response->assertStatus(403);
    }
}
