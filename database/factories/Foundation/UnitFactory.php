<?php
namespace Database\Factories\Foundation;

use App\Models\Foundation\Unit;
use App\Models\Foundation\UnitCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'unit_category_id' => UnitCategory::factory(),
            'name' => fake()->randomElement(['Royal View Room','Palm Court Suite','Majlis Premium']),
            'number' => (string) fake()->unique()->numberBetween(101, 999),
            'code' => strtoupper(fake()->unique()->bothify('U-###')),
            'floor_no' => fake()->numberBetween(1, 12),
            'max_occupancy' => fake()->numberBetween(1, 4),
            'current_rate_sar' => fake()->numberBetween(500, 2500),
            'status' => fake()->randomElement(['active', 'dirty', 'maintenance']),
        ];
    }
}
