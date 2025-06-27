<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clothingCategories = [
            'T-Shirts',
            'Polo Shirts',
            'Dress Shirts',
            'Casual Shirts',
            'Jeans',
            'Chinos',
            'Shorts',
            'Dresses',
            'Blouses',
            'Sweaters',
            'Hoodies',
            'Jackets',
            'Coats',
            'Blazers',
            'Skirts',
            'Pants',
            'Activewear',
            'Underwear',
            'Socks',
            'Accessories'
        ];

        return [
            'name' => $this->faker->randomElement($clothingCategories) . ' ' . $this->faker->numberBetween(1, 1000),
        ];
    }
}
