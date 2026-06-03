<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'reporter_id' => User::factory(),
            'reported_user_id' => User::factory(),
            'type' => fake()->randomElement(ReportType::cases()),
            'description' => fake()->paragraph(),
            'evidence' => [fake()->url()],
            'admin_notes' => null,
            'status' => ReportStatus::Open,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
