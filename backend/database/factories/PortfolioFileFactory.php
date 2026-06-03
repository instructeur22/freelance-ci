<?php

namespace Database\Factories;

use App\Models\PortfolioFile;
use App\Models\PortfolioItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PortfolioFileFactory extends Factory
{
    protected $model = PortfolioFile::class;

    public function definition(): array
    {
        return [
            'portfolio_item_id' => PortfolioItem::factory(),
            'file_url' => fake()->url(),
            'file_type' => fake()->randomElement(['image/jpeg', 'image/png', 'video/mp4']),
            'file_size' => fake()->numberBetween(10000, 5000000),
        ];
    }
}
