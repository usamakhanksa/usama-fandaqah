<?php

namespace Database\Factories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomTypeFactory extends Factory {
    protected $model = RoomType::class;

    public function definition() {
        return [
            'name' => $this->faker->randomElement(['Standard Single', 'Deluxe Double', 'Executive Suite', 'Royal Suite']),
            'base_price' => $this->faker->randomFloat(2, 200, 2000),
        ];
    }
}
