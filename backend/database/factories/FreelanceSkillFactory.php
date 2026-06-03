<?php

namespace Database\Factories;

use App\Models\FreelanceProfile;
use App\Models\FreelanceSkill;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

class FreelanceSkillFactory extends Factory
{
    protected $model = FreelanceSkill::class;

    public function definition(): array
    {
        return [
            'freelance_id' => FreelanceProfile::factory(),
            'skill_id' => Skill::factory(),
            'proficiency_level' => fake()->randomElement(['debutant', 'intermediaire', 'avance', 'expert']),
            'years_experience' => fake()->numberBetween(0, 15),
        ];
    }
}
