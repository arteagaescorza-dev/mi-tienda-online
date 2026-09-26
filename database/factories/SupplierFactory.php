<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'website' => $this->faker->url(),
            'contact_email' => $this->faker->companyEmail(),
            'type' => $this->faker->randomElement(['dropship_scrape','dropship_api','wholesale','local']),
            'credentials' => null,
            'notes' => $this->faker->sentence(),
        ];
    }
}
