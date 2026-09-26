<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        $sizes = ['S','M','L','XL'];
        $colors = ['negro','blanco','rosa','rojo'];
        $size = $this->faker->randomElement($sizes);
        $color = $this->faker->randomElement($colors);

        return [
            'product_id' => null,
            'sku' => strtoupper('V'.Str::random(10)),
            'attributes' => ['size' => $size, 'color' => $color],
            'price' => $this->faker->randomFloat(2, 5, 80),
            'cost' => $this->faker->randomFloat(2, 2, 50),
            'visible' => true,
        ];
    }
}
