<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Price>
 */
class PriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'black_and_white_price' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 10, $max = 100),
            'colored_price' => $this->faker->randomFloat($nbMaxDecimals = 2, $min = 20, $max = 200),
        ];
    }
}
