<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_title' => fake()->jobTitle,
            'email' => fake()->email,
            'phone' => fake()->phoneNumber,
            'firstname_lastname' => fake()->name.' '.fake()->lastName,
            'registered_since' => fake()->date('Y-m-d'),
        ];
    }
}
