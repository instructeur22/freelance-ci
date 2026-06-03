<?php

namespace Database\Factories;

use App\Models\FreelanceLanguage;
use App\Models\FreelanceProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class FreelanceLanguageFactory extends Factory
{
    protected $model = FreelanceLanguage::class;

    public function definition(): array
    {
        return [
            'freelance_id' => FreelanceProfile::factory(),
            'language' => fake()->languageCode(),
            'proficiency_level' => fake()->randomElement(['debutant', 'intermediaire', 'courant', 'langue_maternelle']),
        ];
    }
}
