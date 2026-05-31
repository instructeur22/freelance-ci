<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\AdminService;
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
    #[OA\Response(response: 200, description: 'Dashboard statistics')]
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
    #[OA\Response(response: 200, description: 'List of users')]
    public function users(Request $request): JsonResponse
    {
        $users = $this->adminService->listUsers($request->all());

        return $this->success($users);
    }

    #[OA\Put(
        path: '/admin/users/{id}/status',
        summary: 'Update user status (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'User ID')]
    #[OA\Response(response: 200, description: 'User status updated')]
    #[OA\Response(response: 400, description: 'Unable to update user status')]
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
    #[OA\Response(response: 200, description: 'List of pending verifications')]
    public function verifications(): JsonResponse
    {
        $verifications = $this->adminService->pendingVerifications();

        return $this->success($verifications);
    }

    #[OA\Post(
        path: '/admin/verifications/{id}/approve',
        summary: 'Approve a verification (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Verification ID')]
    #[OA\Response(response: 200, description: 'Verification approved')]
    #[OA\Response(response: 400, description: 'Unable to approve verification')]
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
    #[OA\Response(response: 200, description: 'Verification rejected')]
    #[OA\Response(response: 400, description: 'Unable to reject verification')]
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
    #[OA\Response(response: 200, description: 'List of reports')]
    public function reports(): JsonResponse
    {
        $reports = $this->adminService->listReports();

        return $this->success($reports);
    }

    #[OA\Put(
        path: '/admin/reports/{id}',
        summary: 'Resolve a report (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Report ID')]
    #[OA\Response(response: 200, description: 'Report resolved')]
    #[OA\Response(response: 400, description: 'Unable to resolve report')]
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
    #[OA\Response(response: 200, description: 'List of disputes')]
    public function disputes(): JsonResponse
    {
        $disputes = $this->adminService->listDisputes();

        return $this->success($disputes);
    }

    #[OA\Put(
        path: '/admin/disputes/{id}',
        summary: 'Resolve a dispute (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Dispute ID')]
    #[OA\Response(response: 200, description: 'Dispute resolved')]
    #[OA\Response(response: 400, description: 'Unable to resolve dispute')]
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
    #[OA\Response(response: 200, description: 'List of payments')]
    public function payments(): JsonResponse
    {
        $payments = $this->adminService->monitorPayments();

        return $this->success($payments);
    }

    #[OA\Get(
        path: '/admin/settings',
        summary: 'Get platform settings (admin)',
        tags: ['Admin'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Platform settings')]
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
    #[OA\Response(response: 200, description: 'Setting updated')]
    #[OA\Response(response: 400, description: 'Unable to update setting')]
    public function updateSetting(string $key, Request $request): JsonResponse
    {
        $result = $this->adminService->updateSetting($key, $request->input('value'));

        if (!$result) {
            return $this->error('Unable to update setting', 400);
        }

        return $this->success($result, 'Setting updated');
    }
}
