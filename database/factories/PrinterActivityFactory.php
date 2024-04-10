<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PrinterActivity>
 */
class PrinterActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'isSuccess' => $this->faker->numberBetween(0, 1),
            'description' => $this->faker->paragraph,
        ];
    }
}
