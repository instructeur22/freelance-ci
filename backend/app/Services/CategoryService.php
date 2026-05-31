<?php
namespace App\Services;
use App\Models\JobCategory;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    public function listAll(): Collection
    {
        return JobCategory::whereNull("parent_id")
            ->with(["children", "skills"])
            ->orderBy("sort_order")
            ->get();
    }

    public function getSkills(string $id): ?Collection
    {
        $category = JobCategory::find($id);
        if (!$category) return null;
        return Skill::where("category_id", $category->id)
            ->orWhereIn("category_id", $category->children->pluck("id"))
            ->where("is_active", true)
            ->get();
    }
}
