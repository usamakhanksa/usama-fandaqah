<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Reservation;
use App\Customer;
use App\Unit;
use App\Guest;
use App\IptvGuestNeed;

class FrontDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_in_process()
    {
        $customer = Customer::factory()->create();
        $unit = Unit::factory()->create();
        $reservation = Reservation::factory()->create([
            'customer_id' => $customer->id,
            'unit_id' => $unit->id,
            'status' => Reservation::STATUS_CONFIRMED,
        ]);

        $response = $this->postJson("/front-desk/check-in/{$reservation->id}", [
            'check_in_time' => now()->toISOString(),
            'digital_signature' => 'test-signature-data'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'reservation'
                 ]);
    }

    public function test_check_out_process()
    {
        $customer = Customer::factory()->create();
        $unit = Unit::factory()->create();
        $reservation = Reservation::factory()->create([
            'customer_id' => $customer->id,
            'unit_id' => $unit->id,
            'status' => Reservation::STATUS_CONFIRMED,
            'checked_in' => now(),
        ]);

        $response = $this->postJson("/front-desk/check-out/{$reservation->id}", [
            'check_out_time' => now()->toISOString(),
            'digital_signature' => 'test-signature-data'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'reservation'
                 ]);
    }

    public function test_add_guest_to_reservation()
    {
        $customer = Customer::factory()->create();
        $unit = Unit::factory()->create();
        $reservation = Reservation::factory()->create([
            'customer_id' => $customer->id,
            'unit_id' => $unit->id,
        ]);

        $guestData = [
            'name' => 'John Doe',
            'id_number' => 'ID123456',
            'id_type' => 1,
            'customer_type' => 3,
            'relation_type' => 1
        ];

        $response = $this->postJson("/front-desk/reservation/{$reservation->id}/guest", $guestData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'guest'
                 ]);

        $this->assertDatabaseHas('guests', [
            'name' => 'John Doe',
            'id_number' => 'ID123456',
            'reservation_id' => $reservation->id
        ]);
    }

    public function test_update_guest_information()
    {
        $customer = Customer::factory()->create();
        $unit = Unit::factory()->create();
        $reservation = Reservation::factory()->create([
            'customer_id' => $customer->id,
            'unit_id' => $unit->id,
        ]);
        
        $guest = Guest::factory()->create([
            'reservation_id' => $reservation->id,
            'customer_id' => $customer->id
        ]);

        $updateData = [
            'name' => 'Jane Smith',
            'id_number' => 'ID123456',
            'id_type' => 1,
            'customer_type' => 3,
            'relation_type' => 1
        ];

        $response = $this->putJson("/front-desk/guest/{$guest->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'guest'
                 ]);

        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'name' => 'Jane Smith'
        ]);
    }

    public function test_assign_room_to_reservation()
    {
        $customer = Customer::factory()->create();
        $unit = Unit::factory()->create();
        $reservation = Reservation::factory()->create([
            'customer_id' => $customer->id,
            'status' => Reservation::STATUS_PENDING
        ]);

        $response = $this->postJson('/front-desk/assign-room', [
            'reservation_id' => $reservation->id,
            'unit_id' => $unit->id
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'reservation'
                 ]);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'unit_id' => $unit->id
        ]);
    }

    public function test_create_iptv_request()
    {
        $customer = Customer::factory()->create();
        $unit = Unit::factory()->create();
        $reservation = Reservation::factory()->create([
            'customer_id' => $customer->id,
            'unit_id' => $unit->id,
        ]);

        $request_data = [
            'reservation_id' => $reservation->id,
            'request_type' => 'wake_up_call',
            'request_details' => 'Wake up at 7 AM',
            'priority' => 'high'
        ];

        $response = $this->postJson('/front-desk/iptv-request', $request_data);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'request'
                 ]);

        $this->assertDatabaseHas('iptv_guest_needs', [
            'reservation_id' => $reservation->id,
            'request_type' => 'wake_up_call'
        ]);
    }

    public function test_mark_iptv_request_as_treated()
    {
        $customer = Customer::factory()->create();
        $unit = Unit::factory()->create();
        $reservation = Reservation::factory()->create([
            'customer_id' => $customer->id,
            'unit_id' => $unit->id,
        ]);

        $iptvRequest = IptvGuestNeed::factory()->create([
            'reservation_id' => $reservation->id,
            'is_treated' => false
        ]);

        $response = $this->postJson("/front-desk/iptv-request/{$iptvRequest->id}/mark-treated");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);

        $this->assertDatabaseHas('iptv_guest_needs', [
            'id' => $iptvRequest->id,
            'is_treated' => true
        ]);
    }
}