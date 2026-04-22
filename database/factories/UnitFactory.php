<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    public function definition(): array
    {
        $unitNumber = fake()->unique()->numberBetween(200, 899);

        return [
            'room_id' => 1,
            'room_floor_id' => fake()->numberBetween(1, 6),
            'unit_type_id' => fake()->numberBetween(1, 4),
            'unit_status_id' => fake()->numberBetween(1, 5),
            'name' => fake()->randomElement(['Elite Room', 'Premium Room', 'Suite Room']),
            'number' => (string) $unitNumber,
            'unit_number' => (string) $unitNumber,
            'floor' => (string) fake()->numberBetween(1, 8),
            'building' => fake()->randomElement(['A', 'B', 'C']),
            'status' => 'active',
            'capacity' => fake()->numberBetween(2, 5),
            'beds' => fake()->numberBetween(1, 3),
            'baths' => fake()->numberBetween(1, 2),
            'last_renovated_at' => fake()->dateTimeBetween('-4 years', 'now')->format('Y-m-d H:i:s'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
