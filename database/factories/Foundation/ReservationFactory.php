<?php
namespace Database\Factories\Foundation;

use App\Models\Foundation\Customer;
use App\Models\Foundation\Reservation;
use App\Models\Foundation\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('+2 days', '+20 days');
        $nights = fake()->numberBetween(1, 5);
        $rate = fake()->numberBetween(600, 2200);
        $subtotal = $nights * $rate;

        return [
            'reservation_no' => 'RSV-'.strtoupper(fake()->bothify('######')),
            'customer_id' => Customer::factory(),
            'unit_id' => Unit::factory(),
            'check_in_date' => $checkIn,
            'check_out_date' => (clone $checkIn)->modify("+{$nights} days"),
            'adults' => fake()->numberBetween(1, 3),
            'children' => fake()->numberBetween(0, 2),
            'status' => fake()->randomElement(['pending', 'confirmed']),
            'night_rate_sar' => $rate,
            'nights' => $nights,
            'subtotal_sar' => $subtotal,
            'tax_sar' => round($subtotal * 0.15, 2),
            'total_sar' => round($subtotal * 1.15, 2),
            'source' => fake()->randomElement(['direct', 'booking_com', 'walk_in']),
            'special_requests' => fake()->optional()->sentence(),
        ];
    }
}
