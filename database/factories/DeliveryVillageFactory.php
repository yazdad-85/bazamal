<?php

namespace Database\Factories;

use App\Models\DeliveryVillage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryVillage>
 */
class DeliveryVillageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('35.25.99.####'),
            'district' => fake()->randomElement(DeliveryVillage::DISTRICTS),
            'name' => fake()->unique()->city(),
            'latitude' => fake()->randomFloat(7, -7.15, -7.0),
            'longitude' => fake()->randomFloat(7, 112.5, 112.65),
            'distance_km' => fake()->randomFloat(2, 1, 20),
        ];
    }
}
