<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\AdminService;
use App\Services\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Admin', description: 'Administration (rôle admin requis)')]
class AdminController extends ApiController
{
    public function __construct(
        protected AdminService $adminService
    ) {}

    #[OA\Get(
        path: '/admin/dashboard',
        summary: 'Get admin dashboard stats',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Dashboard statistics', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/AdminDashboard'),
    ]))]
    public function dashboard(): JsonResponse
    {
        $stats = $this->adminService->getDashboardStats();

        return $this->success($stats);
    }

    #[OA\Get(
        path: '/admin/users',
        summary: 'List all users (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of users', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/User')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function users(Request $request): JsonResponse
    {
        $users = $this->adminService->listUsers($request->all());

        return $this->paginated($users);
    }

    #[OA\Put(
        path: '/admin/users/{id}/status',
        summary: 'Update user status (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'User ID')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'suspended', 'banned']),
    ]))]
    #[OA\Response(response: 200, description: 'User status updated', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/User'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to update user status', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function updateUserStatus(string $id, Request $request): JsonResponse
    {
        $result = $this->adminService->updateUserStatus($id, $request->input('status'));

        if (!$result) {
            return $this->error('Unable to update user status', 400);
        }

        return $this->success($result, 'User status updated');
    }

    #[OA\Get(
        path: '/admin/verifications',
        summary: 'List pending verifications (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of pending verifications', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Verification')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function verifications(): JsonResponse
    {
        $verifications = $this->adminService->pendingVerifications();

        return $this->paginated($verifications);
    }

    #[OA\Post(
        path: '/admin/verifications/{id}/approve',
        summary: 'Approve a verification (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Verification ID')]
    #[OA\Response(response: 200, description: 'Verification approved', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Verification'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to approve verification', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function approveVerification(string $id): JsonResponse
    {
        $result = $this->adminService->approveVerification($id);

        if (!$result) {
            return $this->error('Unable to approve verification', 400);
        }

        return $this->success($result, 'Verification approved');
    }

    #[OA\Post(
        path: '/admin/verifications/{id}/reject',
        summary: 'Reject a verification (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Verification ID')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'reason', type: 'string', nullable: true),
    ]))]
    #[OA\Response(response: 200, description: 'Verification rejected', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Verification'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to reject verification', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function rejectVerification(string $id, Request $request): JsonResponse
    {
        $result = $this->adminService->rejectVerification($id, $request->input('reason'));

        if (!$result) {
            return $this->error('Unable to reject verification', 400);
        }

        return $this->success($result, 'Verification rejected');
    }

    #[OA\Get(
        path: '/admin/reports',
        summary: 'List reports (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of reports', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Report')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function reports(): JsonResponse
    {
        $reports = $this->adminService->listReports();

        return $this->paginated($reports);
    }

    #[OA\Put(
        path: '/admin/reports/{id}',
        summary: 'Resolve a report (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Report ID')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'status', type: 'string', enum: ['resolved', 'dismissed']),
        new OA\Property(property: 'resolution_notes', type: 'string', nullable: true),
    ]))]
    #[OA\Response(response: 200, description: 'Report resolved', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Report'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to resolve report', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function resolveReport(string $id, Request $request): JsonResponse
    {
        $result = $this->adminService->resolveReport($id, $request->all());

        if (!$result) {
            return $this->error('Unable to resolve report', 400);
        }

        return $this->success($result, 'Report resolved');
    }

    #[OA\Get(
        path: '/admin/disputes',
        summary: 'List disputes (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of disputes', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Dispute')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function disputes(): JsonResponse
    {
        $disputes = $this->adminService->listDisputes();

        return $this->paginated($disputes);
    }

    #[OA\Put(
        path: '/admin/disputes/{id}',
        summary: 'Resolve a dispute (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Dispute ID')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'status', type: 'string', enum: ['resolved', 'dismissed']),
        new OA\Property(property: 'resolution_notes', type: 'string', nullable: true),
    ]))]
    #[OA\Response(response: 200, description: 'Dispute resolved', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Dispute'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to resolve dispute', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function resolveDispute(string $id, Request $request): JsonResponse
    {
        $result = $this->adminService->resolveDispute($id, $request->all());

        if (!$result) {
            return $this->error('Unable to resolve dispute', 400);
        }

        return $this->success($result, 'Dispute resolved');
    }

    #[OA\Get(
        path: '/admin/payments',
        summary: 'Monitor all payments (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of payments', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Payment')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function payments(): JsonResponse
    {
        $payments = $this->adminService->monitorPayments();

        return $this->paginated($payments);
    }

    #[OA\Get(
        path: '/admin/settings',
        summary: 'Get platform settings (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Platform settings', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/PlatformSetting')),
    ]))]
    public function settings(): JsonResponse
    {
        $settings = $this->adminService->getSettings();

        return $this->success($settings);
    }

    #[OA\Put(
        path: '/admin/settings/{key}',
        summary: 'Update a platform setting (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'key', in: 'path', required: true, description: 'Setting key')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'value', type: 'string'),
    ]))]
    #[OA\Response(response: 200, description: 'Setting updated', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/PlatformSetting'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to update setting', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function updateSetting(string $key, Request $request): JsonResponse
    {
        $result = $this->adminService->updateSetting($key, $request->input('value'));

        if (!$result) {
            return $this->error('Unable to update setting', 400);
        }

        return $this->success($result, 'Setting updated');
    }

    #[OA\Get(
        path: '/admin/badges',
        summary: 'List all verified badges (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of badges', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/VerifiedBadge')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function badges(): JsonResponse
    {
        $badges = $this->adminService->listBadges();

        return $this->paginated($badges);
    }

    #[OA\Post(
        path: '/admin/badges/grant',
        summary: 'Manually grant a verified badge to a freelance (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'freelance_profile_id', type: 'string', description: 'Freelance profile UUID'),
        new OA\Property(property: 'verification_id', type: 'string', nullable: true, description: 'Verification UUID'),
    ]))]
    #[OA\Response(response: 200, description: 'Badge granted', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/VerifiedBadge'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to grant badge', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function grantBadge(Request $request): JsonResponse
    {
        $result = $this->adminService->grantBadge(
            $request->input('freelance_profile_id'),
            $request->input('verification_id')
        );

        if (!$result) {
            return $this->error('Unable to grant badge', 400);
        }

        return $this->success($result, 'Badge granted');
    }

    #[OA\Post(
        path: '/admin/badges/{id}/revoke',
        summary: 'Revoke a verified badge (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Badge ID')]
    #[OA\Response(response: 200, description: 'Badge revoked', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to revoke badge', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function revokeBadge(string $id): JsonResponse
    {
        $result = $this->adminService->revokeBadge($id);

        if (!$result) {
            return $this->error('Unable to revoke badge', 400);
        }

        return $this->success(null, 'Badge revoked');
    }

    #[OA\Get(
        path: '/admin/boosts',
        summary: 'List all boosts (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of boosts', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Boost')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function boosts(): JsonResponse
    {
        $boosts = $this->adminService->listBoosts();

        return $this->paginated($boosts);
    }

    #[OA\Post(
        path: '/admin/boosts/{id}/revoke',
        summary: 'Revoke a boost (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Boost ID')]
    #[OA\Response(response: 200, description: 'Boost revoked', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to revoke boost', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function revokeBoost(string $id): JsonResponse
    {
        $result = $this->adminService->revokeBoost($id);

        if (!$result) {
            return $this->error('Unable to revoke boost', 400);
        }

        return $this->success(null, 'Boost revoked');
    }
}
