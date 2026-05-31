<?php
namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function createProject(User $client, array $data): Project
    {
        $project = $client->projects()->create([
            "title" => $data["title"],
            "description" => $data["description"],
            "budget_min" => $data["budget_min"] ?? null,
            "budget_max" => $data["budget_max"] ?? null,
            "budget_type" => $data["budget_type"] ?? "fixed",
            "category_id" => $data["category_id"] ?? null,
            "subcategory_id" => $data["subcategory_id"] ?? null,
            "duration" => $data["duration"] ?? null,
            "experience_level" => $data["experience_level"] ?? "intermediate",
            "skills_required" => $data["skills_required"] ?? [],
            "status" => $data["status"] ?? ProjectStatus::Open,
            "is_featured" => $data["is_featured"] ?? false,
            "location" => $data["location"] ?? null,
            "remote_type" => $data["remote_type"] ?? "remote",
        ]);

        if (!empty($data["attachments"])) {
            $project->attachments()->createMany(array_map(fn ($url) => ["url" => $url], $data["attachments"]));
        }

        return $project;
    }

    public function updateProject(User $client, string $id, array $data): Project
    {
        $project = $this->getClientProject($client, $id);
        $project->update($data);
        return $project->fresh();
    }

    public function deleteProject(User $client, string $id): void
    {
        $project = $this->getClientProject($client, $id);
        $project->status = ProjectStatus::Cancelled;
        $project->save();
        $project->delete();
    }

    public function getProjects(array $filters): LengthAwarePaginator
    {
        $query = Project::with(["client:id,first_name,last_name,avatar_url", "category:id,name"]);

        if (!empty($filters["status"])) {
            $query->where("status", $filters["status"]);
        } else {
            $query->where("status", ProjectStatus::Open);
        }

        if (!empty($filters["category_id"])) {
            $query->where("category_id", $filters["category_id"]);
        }

        if (!empty($filters["is_featured"])) {
            $query->where("is_featured", true);
        }

        if (!empty($filters["search"])) {
            $search = $filters["search"];
            $query->where(function (Builder $q) use ($search) {
                $q->where("title", "like", "%{$search}%")
                  ->orWhere("description", "like", "%{$search}%");
            });
        }

        if (!empty($filters["budget_min"])) {
            $query->where("budget_min", ">=", $filters["budget_min"]);
        }

        if (!empty($filters["budget_max"])) {
            $query->where("budget_max", "<=", $filters["budget_max"]);
        }

        if (!empty($filters["client_id"])) {
            $query->where("client_id", $filters["client_id"]);
        }

        if (!empty($filters["remote_type"])) {
            $query->where("remote_type", $filters["remote_type"]);
        }

        $sortField = $filters["sort_by"] ?? "created_at";
        $sortOrder = $filters["sort_order"] ?? "desc";
        $query->orderBy($sortField, $sortOrder);

        $perPage = $filters["per_page"] ?? 12;
        return $query->paginate($perPage);
    }

    public function getProject(string $id): Project
    {
        return Project::with([
            "client:id,first_name,last_name,avatar_url",
            "category:id,name",
            "quotes" => function ($q) {
                $q->with("freelance:id,first_name,last_name,avatar_url")->where("status", "accepted");
            },
        ])->findOrFail($id);
    }

    public function incrementViews(string $id): void
    {
        Project::where("id", $id)->increment("views_count");
    }

    private function getClientProject(User $client, string $id): Project
    {
        return $client->projects()->findOrFail($id);
    }
}
