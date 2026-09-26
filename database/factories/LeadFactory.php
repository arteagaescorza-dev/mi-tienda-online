<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'source' => $this->faker->randomElement(['web','facebook','instagram','import']),
            'status' => $this->faker->randomElement(['new','contacted','qualified','lost']),
            'notes' => $this->faker->optional()->sentence(),
            'metadata' => ['utm_source' => $this->faker->randomElement(['google','facebook','newsletter'])],
            'last_contacted_at' => null,
        ];
    }
}
