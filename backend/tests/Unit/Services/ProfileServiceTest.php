<?php

namespace Tests\Unit\Services;

use App\Models\ClientProfile;
use App\Models\FreelanceProfile;
use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProfileService $profileService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->profileService = new ProfileService();
    }

    public function test_getFullProfile_returns_user_with_relations(): void
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        $result = $this->profileService->getFullProfile($user);

        $this->assertInstanceOf(User::class, $result);
        $this->assertNotNull($result->profile);
    }

    public function test_updateCommonProfile_creates_profile_if_not_exists(): void
    {
        $user = User::factory()->create();

        $profile = $this->profileService->updateCommonProfile($user, [
            'bio' => 'Hello world',
            'city' => 'Abidjan',
        ]);

        $this->assertInstanceOf(Profile::class, $profile);
        $this->assertEquals('Hello world', $profile->bio);
        $this->assertEquals('Abidjan', $profile->city);
    }

    public function test_updateCommonProfile_updates_existing(): void
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id, 'bio' => 'Old bio']);

        $profile = $this->profileService->updateCommonProfile($user, [
            'bio' => 'Updated bio',
        ]);

        $this->assertEquals('Updated bio', $profile->bio);
    }

    public function test_getFreelanceProfile_returns_null_when_not_freelance(): void
    {
        $user = User::factory()->client()->create();

        $profile = $this->profileService->getFreelanceProfile($user);

        $this->assertNull($profile);
    }

    public function test_getFreelanceProfile_returns_profile_when_exists(): void
    {
        $user = User::factory()->create();
        FreelanceProfile::factory()->create(['user_id' => $user->id]);

        $profile = $this->profileService->getFreelanceProfile($user);

        $this->assertInstanceOf(FreelanceProfile::class, $profile);
    }

    public function test_listFreelances_returns_paginated_results(): void
    {
        User::factory()->count(3)->create();
        FreelanceProfile::factory()->count(3)->create();

        $result = $this->profileService->listFreelances([]);

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $result);
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    public function test_addSkill_adds_skill_to_freelance(): void
    {
        $freelance = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $freelance->id]);
        $skill = Skill::factory()->create();

        $this->profileService->addSkill($freelance, [
            'skill_id' => $skill->id,
            'proficiency_level' => 'avance',
        ]);

        $this->assertDatabaseHas('freelance_skills', [
            'freelance_id' => $profile->id,
            'skill_id' => $skill->id,
        ]);
    }

    public function test_addPortfolioItem_creates_item(): void
    {
        $freelance = User::factory()->create();
        $profile = FreelanceProfile::factory()->create(['user_id' => $freelance->id]);

        $item = $this->profileService->addPortfolioItem($freelance, [
            'title' => 'Mon projet',
            'description' => 'Description du projet',
        ]);

        $this->assertInstanceOf(PortfolioItem::class, $item);
        $this->assertEquals($profile->id, $item->freelance_profile_id);
    }
}
