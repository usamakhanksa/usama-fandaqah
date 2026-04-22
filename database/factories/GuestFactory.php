<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GuestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+966 '.fake()->numberBetween(500000000, 599999999),
            'type' => fake()->randomElement(['vip', 'normal', 'company']),
            'gender' => fake()->randomElement(['male', 'female']),
            'card_id' => (string) fake()->numberBetween(1000000000, 9999999999),
            'date_of_birth' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'preferences' => json_encode([
                'room_view' => fake()->randomElement(['city', 'pool', 'sea']),
                'bed_type' => fake()->randomElement(['king', 'double', 'twin']),
                'smoking' => fake()->boolean(20),
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
