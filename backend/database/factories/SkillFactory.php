<?php

namespace Database\Factories;

use App\Models\JobCategory;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();
        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'category_id' => JobCategory::factory(),
        ];
    }
}
