<?php

namespace Database\Factories;

use App\Models\DashboardNotice;
use Illuminate\Database\Eloquent\Factories\Factory;

class DashboardNoticeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DashboardNotice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['info', 'warning', 'urgent']),
            'is_active' => $this->faker->boolean(80),
            'expires_at' => $this->faker->optional(0.3)->dateTimeBetween('+1 day', '+1 month'),
        ];
    }
}
