<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 15);

        return [
            'title' => fake()->sentence(fake()->numberBetween(2, 5)),
            'author' => fake()->name(),
            'isbn' => fake()->unique()->isbn13(),
            'category_id' => Category::factory(),
            'category' => null,
            'quantity' => $quantity,
            'available_quantity' => $quantity,
            'price' => fake()->randomFloat(2, 5, 120),
        ];
    }
}

