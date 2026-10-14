<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $name = fake()->words(3, true), 'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'sport' => 'Running', 'year' => 2026, 'event_date' => '2026-04-26',
            'location' => 'Bandar Lampung', 'description' => fake()->paragraph(),
            'categories' => ['5K'], 'gallery' => [], 'published' => false,
        ];
    }
}
