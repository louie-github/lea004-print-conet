<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'url' => $this->faker->imageUrl(),
            'name' => $this->faker->name,
            'color' => $this->faker->colorName,
            'page_range_start' => 1,
            'page_range_end' => 10,
            'amount_paid' => 999,
            'no_of_copies' => 20,
        ];
    }
}
