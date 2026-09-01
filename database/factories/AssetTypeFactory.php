<?php

namespace Database\Factories;

use App\Enums\AssetTrackingType;
use App\Models\AssetCategory;
use App\Models\AssetType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetType>
 */
class AssetTypeFactory extends Factory
{
    protected $model = AssetType::class;

    public function definition(): array
    {
        return [
            'asset_category_id' => AssetCategory::factory(),
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->lexify('????')),
            'tracking_type' => AssetTrackingType::Individual->value,
            'description' => fake()->optional()->sentence(),
            'status' => 'active',
        ];
    }

    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'tracking_type' => AssetTrackingType::Individual->value,
        ]);
    }

    public function quantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'tracking_type' => AssetTrackingType::Quantity->value,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}