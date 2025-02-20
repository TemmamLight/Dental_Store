<?php

namespace Database\Factories;

use App\Enums\ProductTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return  [
            'name' => $this->faker->words(3, true), // Generates a fake product name
            'slug' => Str::slug($this->faker->words(3, true)), // Generates a slug from the name
            'brand_id' => $this->faker->numberBetween(1, 3), // Example brand ID
            'description' => $this->faker->paragraph, // Generates a fake description
            'image' => $this->faker->imageUrl(), // Generates a fake image URL
            'sku' => $this->faker->unique()->ean13, // Generates a unique SKU
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'), // Random publish date
            'quantity' => $this->faker->numberBetween(0, 100), // Random quantity
            'price' => $this->faker->randomFloat(2, 10, 1000), // Random price
            'status' => $this->faker->boolean, // Random status (true/false)
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
            'is_visible' => $this->faker->boolean(80), // 80% chance of being visible
            'date' => $this->faker->dateTimeThisYear(), // Random date within the current year
            'type' => $this->faker->randomElement([ProductTypeEnum::DELIVERABLE->value, ProductTypeEnum::DOWNLOADABLE->value]), // Random type from enum
            'category_id' => $this->faker->numberBetween(1, 2), // Example category ID
        ];
    }
}