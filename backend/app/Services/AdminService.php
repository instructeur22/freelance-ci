<?php
namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\DisputeStatus;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\Contract;
use App\Models\Dispute;
use App\Models\PlatformSetting;
use App\Models\Project;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AdminService
{
    public function getDashboardStats(): array
    {
        return [
            "total_users" => User::count(),
            "total_clients" => User::where("role", UserRole::Client->value)->count(),
            "total_freelances" => User::where("role", UserRole::Freelance->value)->count(),
            "total_projects" => Project::count(),
            "open_projects" => Project::where("status", "open")->count(),
            "total_contracts" => Contract::count(),
            "active_contracts" => Contract::where("status", \App\Enums\ContractStatus::Signed->value)->count(),
            "pending_verifications" => Verification::where("status", VerificationStatus::Pending)->count(),
            "open_reports" => Report::where("status", ReportStatus::Open)->count(),
            "open_disputes" => Dispute::where("status", DisputeStatus::Open)->count(),
            "recent_users" => User::latest()->take(10)->get(),
            "recent_projects" => Project::with("client:id,first_name,last_name")->latest()->take(10)->get(),
        ];
    }

    public function getPendingVerifications(): LengthAwarePaginator
    {
        return Verification::with("user:id,first_name,last_name,email")
            ->where("status", VerificationStatus::Pending)
            ->latest()
            ->paginate(20);
    }

    public function approveVerification(string $id, User $admin): Verification
    {
        $verification = Verification::findOrFail($id);
        $verification->update([
            "status" => VerificationStatus::Approved,
            "reviewed_by" => $admin->id,
            "reviewed_at" => now(),
        ]);
        return $verification;
    }

    public function rejectVerification(string $id, User $admin, ?string $note = null): Verification
    {
        $verification = Verification::findOrFail($id);
        $verification->update([
            "status" => VerificationStatus::Rejected,
            "reviewed_by" => $admin->id,
            "reviewed_at" => now(),
            "admin_note" => $note,
        ]);
        return $verification;
    }

    public function getReports(array $filters): LengthAwarePaginator
    {
        $query = Report::with(["reporter:id,first_name,last_name", "reported:id,first_name,last_name"]);

        if (!empty($filters["status"])) {
            $query->where("status", $filters["status"]);
        }

        if (!empty($filters["type"])) {
            $query->where("type", $filters["type"]);
        }

        return $query->latest()->paginate($filters["per_page"] ?? 20);
    }

    public function resolveReport(string $id, User $admin, array $data): Report
    {
        $report = Report::findOrFail($id);
        $report->update([
            "status" => $data["action"] === "dismiss" ? ReportStatus::Dismissed : ReportStatus::Resolved,
            "resolved_by" => $admin->id,
            "resolved_at" => now(),
            "resolution_note" => $data["note"] ?? null,
        ]);

        if (!empty($data["action_taken"])) {
            $reportedUser = User::find($report->reported_id);
            if ($reportedUser && $data["action_taken"] === "suspend") {
            $reportedUser->update(["status" => AccountStatus::Suspended]);
            } elseif ($reportedUser && $data["action_taken"] === "ban") {
                $reportedUser->update(["status" => AccountStatus::Banned]);
            }
        }

        return $report;
    }

    public function getDisputes(array $filters): LengthAwarePaginator
    {
        $query = Dispute::with([
            "contract:id,title",
            "openedBy:id,first_name,last_name",
        ]);

        if (!empty($filters["status"])) {
            $query->where("status", $filters["status"]);
        }

        return $query->latest()->paginate($filters["per_page"] ?? 20);
    }

    public function resolveDispute(string $id, User $admin, array $data): Dispute
    {
        $dispute = Dispute::findOrFail($id);
        $dispute->update([
            "status" => DisputeStatus::Closed,
            "resolved_by" => $admin->id,
            "resolved_at" => now(),
            "resolution" => $data["resolution"] ?? null,
            "resolution_note" => $data["note"] ?? null,
        ]);

        return $dispute;
    }

    public function updatePlatformSetting(string $key, string $value, User $admin): PlatformSetting
    {
        return PlatformSetting::updateOrCreate(
            ["key" => $key],
            ["value" => $value, "updated_by" => $admin->id],
        );
    }
}
