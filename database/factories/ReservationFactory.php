<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('-1 month', '+2 months');
        $nights = fake()->numberBetween(1, 10);
        $checkOut = (clone $checkIn)->modify("+{$nights} days");

        return [
            'code' => 'RSV'.fake()->unique()->numberBetween(10000, 99999),
            'number' => 'RES'.fake()->unique()->numberBetween(10000, 99999),
            'guest_id' => 1,
            'customer_id' => 1,
            'room_id' => 1,
            'unit_id' => 1,
            'reservation_status_id' => fake()->numberBetween(1, 5),
            'check_in' => $checkIn->format('Y-m-d H:i:s'),
            'check_out' => $checkOut->format('Y-m-d H:i:s'),
            'date_in' => $checkIn->format('Y-m-d H:i:s'),
            'date_out' => $checkOut->format('Y-m-d H:i:s'),
            'status' => fake()->randomElement(['confirmed', 'canceled', 'awaiting-payment']),
            'total_price' => fake()->randomFloat(2, 200, 2400),
            'stay_type' => 'checkin',
            'adults' => fake()->numberBetween(1, 4),
            'children' => fake()->numberBetween(0, 2),
            'cancellation_reason' => fake()->optional()->sentence(),
            'is_no_show_charged' => fake()->boolean(20),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
