<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory {
    protected $model = Booking::class;

    public function definition() {
        return [
            'reservation_id' => Reservation::factory(),
            'guest_id' => Guest::factory(),
            'room_id' => Room::factory(),
            'reference_code' => 'FAN-' . $this->faker->unique()->numberBetween(10000, 99999),
            'guest_name' => $this->faker->name(),
            'guest_phone' => '+9665' . $this->faker->numerify('########'),
            'property_reference' => $this->faker->randomElement(['RYD', 'JED', 'DMH', 'MAK']) . '-' . $this->faker->numberBetween(100, 999),
            'check_in' => $this->faker->dateTimeBetween('now', '+1 month'),
            'check_out' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
            'total_amount' => $this->faker->randomFloat(2, 500, 5000),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'completed', 'cancelled']),
            'notes' => $this->faker->sentence(),
        ];
    }
}
