<?php

namespace Tests\Unit\Services;

use App\Enums\AccountStatus;
use App\Enums\DisputeStatus;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Models\Dispute;
use App\Models\PlatformSetting;
use App\Models\Report;
use App\Models\User;
use App\Models\Verification;
use App\Models\VerifiedBadge;
use App\Models\FreelanceProfile;
use App\Models\Boost;
use App\Services\AdminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdminService $adminService;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'geniuspay.api_key' => 'pk_test_key',
            'geniuspay.api_secret' => 'sk_test_secret',
            'geniuspay.webhook_secret' => 'whsec_test',
            'geniuspay.mode' => 'test',
        ]);
        $this->adminService = new AdminService();
    }

    public function test_updateUserStatus_changes_status(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['status' => AccountStatus::Active]);

        $result = $this->adminService->updateUserStatus($user->id, 'suspended');

        $this->assertNotNull($result);
        $this->assertEquals(AccountStatus::Suspended, $result->status);
    }

    public function test_approveVerification_approves_verification(): void
    {
        $verification = Verification::factory()->create(['status' => VerificationStatus::Pending]);

        $result = $this->adminService->approveVerification($verification->id);

        $this->assertNotNull($result);
        $this->assertEquals(VerificationStatus::Approved, $result->status);
    }

    public function test_rejectVerification_rejects_verification(): void
    {
        $verification = Verification::factory()->create(['status' => VerificationStatus::Pending]);

        $result = $this->adminService->rejectVerification($verification->id, 'Invalid document');

        $this->assertNotNull($result);
        $this->assertEquals(VerificationStatus::Rejected, $result->status);
        $this->assertEquals('Invalid document', $result->admin_notes);
    }

    public function test_resolveDispute_resolves_dispute(): void
    {
        $admin = User::factory()->admin()->create();
        $dispute = Dispute::factory()->create(['status' => DisputeStatus::Open]);

        $result = $this->adminService->resolveDispute($dispute->id, [
            'admin_notes' => 'Resolved in favor of freelancer',
            'status' => DisputeStatus::Closed->value,
        ]);

        $this->assertNotNull($result);
        $this->assertEquals(DisputeStatus::Closed, $result->status);
    }

    public function test_resolveReport_resolves_report(): void
    {
        $admin = User::factory()->admin()->create();
        $report = Report::factory()->create(['status' => ReportStatus::Open]);

        $result = $this->adminService->resolveReport($report->id, [
            'admin_notes' => 'No action needed',
            'status' => ReportStatus::Resolved->value,
        ]);

        $this->assertNotNull($result);
        $this->assertEquals(ReportStatus::Resolved, $result->status);
    }

    public function test_updateSetting_updates_platform_setting(): void
    {
        PlatformSetting::factory()->create([
            'key' => 'badge_price',
            'value' => '5000',
            'group' => 'payment',
        ]);

        $setting = $this->adminService->updateSetting('badge_price', '7500');

        $this->assertNotNull($setting);
        $this->assertEquals('7500', $setting->value);
    }

    public function test_grantBadge_grants_badge(): void
    {
        $profile = FreelanceProfile::factory()->create();

        $badge = $this->adminService->grantBadge($profile->id);

        $this->assertNotNull($badge);
        $this->assertInstanceOf(VerifiedBadge::class, $badge);
        $this->assertTrue($badge->is_active);
    }

    public function test_revokeBadge_revokes_badge(): void
    {
        $badge = VerifiedBadge::factory()->create(['is_active' => true]);

        $result = $this->adminService->revokeBadge($badge->id);

        $this->assertTrue($result);
        $this->assertFalse($badge->fresh()->is_active);
    }

    public function test_revokeBoost_revokes_boost(): void
    {
        $boost = Boost::factory()->create(['is_active' => true]);

        $result = $this->adminService->revokeBoost($boost->id);

        $this->assertTrue($result);
        $this->assertFalse($boost->fresh()->is_active);
    }
}
