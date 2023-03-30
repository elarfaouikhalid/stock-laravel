<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'address' => $this->faker->streetAddress(),
            'other_address' => $this->faker->secondaryAddress(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'postal_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'created_at' => "2023-03-01 21:43:47"
        ];
    }
}
