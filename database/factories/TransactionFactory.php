<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'document_id' => $this->faker->numberBetween(1, 10),
            'total_pages' => $this->faker->numberBetween(1, 10),
            'amount_to_be_paid' => $this->faker->numberBetween(20, 100),
        ];
    }
}
