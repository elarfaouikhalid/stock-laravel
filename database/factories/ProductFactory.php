<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'stock_quantity' => $this->faker->numberBetween(10, 100),
            'image' => $this->faker->imageUrl(640, 480, 'cats'),
            'price' => $this->faker->randomFloat(2, 10, 100),
            'cost' => $this->faker->randomFloat(2, 5, 50),
        ];
    }
}
