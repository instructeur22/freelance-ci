<?php
namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\DisputeStatus;
use App\Enums\ReportStatus;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\Contract;
use App\Models\Dispute;
use App\Models\Payment;
use App\Models\PlatformSetting;
use App\Models\Project;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use App\Models\Verification;
use App\Models\VerifiedBadge;
use App\Models\Boost;
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

    public function listUsers(): LengthAwarePaginator
    {
        return User::with("profile")
            ->orderBy("created_at", "desc")
            ->paginate(20);
    }

    public function updateUserStatus(string $id, string $status): ?User
    {
        $user = User::find($id);
        if (!$user) return null;

        $user->update(["status" => AccountStatus::tryFrom($status) ?? AccountStatus::Active]);
        return $user->fresh();
    }

    public function pendingVerifications(): LengthAwarePaginator
    {
        return Verification::with("user:id,first_name,last_name,email")
            ->where("status", VerificationStatus::Pending)
            ->latest()
            ->paginate(20);
    }

    public function approveVerification(string $id): ?Verification
    {
        $verification = Verification::find($id);
        if (!$verification) return null;

        $verification->update([
            "status" => VerificationStatus::Approved,
            "reviewed_at" => now(),
        ]);
        return $verification->fresh();
    }

    public function rejectVerification(string $id, ?string $note = null): ?Verification
    {
        $verification = Verification::find($id);
        if (!$verification) return null;

        $verification->update([
            "status" => VerificationStatus::Rejected,
            "reviewed_at" => now(),
            "admin_notes" => $note,
        ]);
        return $verification->fresh();
    }

    public function listReports(): LengthAwarePaginator
    {
        return Report::with(["reporter:id,first_name,last_name", "reported:id,first_name,last_name"])
            ->latest()
            ->paginate(20);
    }

    public function resolveReport(string $id, array $data): ?Report
    {
        $report = Report::find($id);
        if (!$report) return null;

        $report->update([
            "status" => ($data["action"] ?? "") === "dismiss" ? ReportStatus::Dismissed : ReportStatus::Resolved,
            "resolved_at" => now(),
            "resolution_note" => $data["note"] ?? null,
        ]);

        return $report->fresh();
    }

    public function listDisputes(): LengthAwarePaginator
    {
        return Dispute::with([
            "contract:id,title",
            "openedBy:id,first_name,last_name",
        ])->latest()->paginate(20);
    }

    public function resolveDispute(string $id, array $data): ?Dispute
    {
        $dispute = Dispute::find($id);
        if (!$dispute) return null;

        $dispute->update([
            "status" => DisputeStatus::Closed,
            "resolved_at" => now(),
            "resolution" => $data["resolution"] ?? null,
            "resolution_note" => $data["note"] ?? null,
        ]);

        return $dispute->fresh();
    }

    public function monitorPayments(): LengthAwarePaginator
    {
        return Payment::with("user:id,first_name,last_name")
            ->latest()
            ->paginate(20);
    }

    public function getSettings()
    {
        return PlatformSetting::all();
    }

    public function updateSetting(string $key, ?string $value): ?PlatformSetting
    {
        if (!$value) return null;

        return PlatformSetting::updateOrCreate(
            ["key" => $key],
            ["value" => $value],
        );
    }

    public function listBadges(): LengthAwarePaginator
    {
        return VerifiedBadge::with("freelanceProfile.user:id,first_name,last_name,email")
            ->latest()
            ->paginate(20);
    }

    public function grantBadge(string $profileId, ?string $verificationId = null): ?VerifiedBadge
    {
        $profile = \App\Models\FreelanceProfile::find($profileId);
        if (!$profile) return null;

        return app(BadgeService::class)->activate($profile, $verificationId, 0);
    }

    public function revokeBadge(string $badgeId): bool
    {
        return app(BadgeService::class)->revoke($badgeId);
    }

    public function listBoosts(): LengthAwarePaginator
    {
        return Boost::with("freelanceProfile.user:id,first_name,last_name,email")
            ->latest()
            ->paginate(20);
    }

    public function revokeBoost(string $boostId): bool
    {
        return app(BoostService::class)->revoke($boostId);
    }
}
