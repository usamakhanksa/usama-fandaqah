<?php
namespace Database\Factories\Foundation;

use App\Models\Foundation\UnitCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitCategoryFactory extends Factory
{
    protected $model = UnitCategory::class;

    public function definition(): array
    {
        return [
            'name_en' => fake()->randomElement(['Deluxe King','Executive Suite','Royal Twin']),
            'name_ar' => fake()->randomElement(['ديلوكس كينج','جناح تنفيذي','رويال توين']),
            'code' => strtoupper(fake()->unique()->lexify('UC???')),
            'default_capacity' => fake()->numberBetween(1,4),
            'base_rate_sar' => fake()->numberBetween(400,1800),
            'active' => true,
        ];
    }
}
