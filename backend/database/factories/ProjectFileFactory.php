<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFileFactory extends Factory
{
    protected $model = ProjectFile::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'file_url' => fake()->url(),
            'file_name' => fake()->word() . '.' . fake()->fileExtension(),
            'file_type' => fake()->randomElement(['image/jpeg', 'image/png', 'application/pdf', 'text/plain']),
            'file_size' => fake()->numberBetween(1000, 5000000),
        ];
    }
}
