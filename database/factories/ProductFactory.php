<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->words(3, true);
        $price = $this->faker->randomFloat(2, 5, 80);
        $cost = round($price * $this->faker->randomFloat(2, 0.4, 0.7), 2);

        return [
            'sku' => strtoupper('P'.Str::random(8)),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->sentence(12),
            'brand' => $this->faker->company(),
            'visible' => true,
            'type' => $this->faker->randomElement(['physical','dropship','both']),
            'price' => $price,
            'cost' => $cost,
            'weight' => $this->faker->randomFloat(2, 0.05, 1.5),
            'meta' => ['material' => $this->faker->randomElement(['algodon','microfibra','encaje'])],
        ];
    }
}
