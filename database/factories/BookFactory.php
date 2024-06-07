<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'isbn' => fake()->isbn13(),
            'title' => fake()->word(),
            'description' => fake()->text(),
            'published_year' => fake()->year(),
            'price' => fake()->randomNumber(2, true),
            'pages' => fake()->randomNumber(2, true),
        ];
    }

    public function isbn10(): static
    {
        return $this->state(fn (array $attributes) => [
            'isbn' => fake()->isbn10(),
        ]);
    }
}
