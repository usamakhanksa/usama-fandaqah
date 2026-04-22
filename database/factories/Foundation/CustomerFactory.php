<?php
namespace Database\Factories\Foundation;

use App\Models\Foundation\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'first_name_en' => fake()->randomElement(['Ahmed','Sara','Faisal']),
            'last_name_en' => fake()->randomElement(['Al-Fahad','Al-Qahtani','Al-Harbi']),
            'first_name_ar' => fake()->randomElement(['أحمد','سارة','فيصل']),
            'last_name_ar' => fake()->randomElement(['الفهد','القحطاني','الحربي']),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+9665'.fake()->unique()->numerify('########'),
            'document_type' => fake()->randomElement(['saudi_id','iqama','passport']),
            'document_number' => (string) fake()->unique()->numberBetween(1000000000, 9999999999),
            'date_of_birth' => fake()->date(),
            'city' => fake()->randomElement(['Riyadh','Jeddah','Dammam']),
            'address_line' => fake()->streetAddress(),
            'vip' => fake()->boolean(20),
            'notes' => fake()->sentence(),
        ];
    }
}
