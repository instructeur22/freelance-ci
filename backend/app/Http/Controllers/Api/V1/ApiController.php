<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Base', description: 'Méthodes de base des réponses API')]

// ─── Reusable Schemas ──────────────────────────────────────
#[OA\Schema(schema: 'PaginationMeta', properties: [
    new OA\Property(property: 'current_page', type: 'integer'),
    new OA\Property(property: 'last_page', type: 'integer'),
    new OA\Property(property: 'per_page', type: 'integer'),
    new OA\Property(property: 'total', type: 'integer'),
])]
#[OA\Schema(schema: 'User', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'first_name', type: 'string'),
    new OA\Property(property: 'last_name', type: 'string', nullable: true),
    new OA\Property(property: 'email', type: 'string', format: 'email'),
    new OA\Property(property: 'phone', type: 'string', nullable: true),
    new OA\Property(property: 'role', type: 'string', enum: ['client', 'freelance', 'admin']),
    new OA\Property(property: 'status', type: 'string', enum: ['active', 'suspended', 'banned']),
    new OA\Property(property: 'avatar_url', type: 'string', nullable: true),
    new OA\Property(property: 'average_rating', type: 'number', format: 'float', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'Profile', properties: [
    new OA\Property(property: 'bio', type: 'string', nullable: true),
    new OA\Property(property: 'title', type: 'string', nullable: true),
    new OA\Property(property: 'country', type: 'string', nullable: true),
    new OA\Property(property: 'city', type: 'string', nullable: true),
    new OA\Property(property: 'is_visible', type: 'boolean'),
])]
#[OA\Schema(schema: 'ClientProfile', properties: [
    new OA\Property(property: 'company_name', type: 'string', nullable: true),
    new OA\Property(property: 'company_website', type: 'string', nullable: true),
    new OA\Property(property: 'industry', type: 'string', nullable: true),
    new OA\Property(property: 'total_projects_posted', type: 'integer'),
    new OA\Property(property: 'total_spent', type: 'number', format: 'float'),
    new OA\Property(property: 'average_rating', type: 'number', format: 'float'),
])]
#[OA\Schema(schema: 'FreelanceProfile', properties: [
    new OA\Property(property: 'professional_title', type: 'string', nullable: true),
    new OA\Property(property: 'experience_level', type: 'string', nullable: true),
    new OA\Property(property: 'years_experience', type: 'integer', nullable: true),
    new OA\Property(property: 'hourly_rate_min', type: 'number', format: 'float', nullable: true),
    new OA\Property(property: 'hourly_rate_max', type: 'number', format: 'float', nullable: true),
    new OA\Property(property: 'is_available', type: 'boolean'),
    new OA\Property(property: 'average_rating', type: 'number', format: 'float'),
    new OA\Property(property: 'total_reviews', type: 'integer'),
    new OA\Property(property: 'total_earnings', type: 'number', format: 'float'),
    new OA\Property(property: 'success_rate', type: 'number', format: 'float'),
])]
#[OA\Schema(schema: 'Skill', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'name', type: 'string'),
])]
#[OA\Schema(schema: 'Category', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'slug', type: 'string'),
    new OA\Property(property: 'description', type: 'string', nullable: true),
    new OA\Property(property: 'icon', type: 'string', nullable: true),
    new OA\Property(property: 'is_active', type: 'boolean'),
])]
#[OA\Schema(schema: 'Project', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'title', type: 'string'),
    new OA\Property(property: 'description', type: 'string'),
    new OA\Property(property: 'status', type: 'string', enum: ['open', 'in_progress', 'completed', 'cancelled']),
    new OA\Property(property: 'budget_min', type: 'number', format: 'float', nullable: true),
    new OA\Property(property: 'budget_max', type: 'number', format: 'float', nullable: true),
    new OA\Property(property: 'experience_level', type: 'string', nullable: true),
    new OA\Property(property: 'is_featured', type: 'boolean'),
    new OA\Property(property: 'is_remote', type: 'boolean'),
    new OA\Property(property: 'quotes_count', type: 'integer'),
    new OA\Property(property: 'views_count', type: 'integer'),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'ProjectFile', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'file_url', type: 'string'),
    new OA\Property(property: 'file_name', type: 'string'),
    new OA\Property(property: 'file_type', type: 'string'),
    new OA\Property(property: 'file_size', type: 'integer'),
])]
#[OA\Schema(schema: 'Quote', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'amount', type: 'number', format: 'float'),
    new OA\Property(property: 'estimated_duration', type: 'integer', nullable: true),
    new OA\Property(property: 'proposal', type: 'string'),
    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'accepted', 'refused', 'withdrawn']),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'Contract', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'title', type: 'string'),
    new OA\Property(property: 'total_amount', type: 'number', format: 'float'),
    new OA\Property(property: 'platform_fee', type: 'number', format: 'float'),
    new OA\Property(property: 'freelance_amount', type: 'number', format: 'float'),
    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'active', 'completed', 'cancelled', 'disputed']),
    new OA\Property(property: 'client_signed_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'freelance_signed_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'Milestone', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'title', type: 'string'),
    new OA\Property(property: 'amount', type: 'number', format: 'float'),
    new OA\Property(property: 'due_date', type: 'string', format: 'date', nullable: true),
    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'delivered', 'validated']),
    new OA\Property(property: 'delivered_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'validated_at', type: 'string', format: 'date-time', nullable: true),
])]
#[OA\Schema(schema: 'Payment', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'amount', type: 'number', format: 'float'),
    new OA\Property(property: 'platform_fee', type: 'number', format: 'float'),
    new OA\Property(property: 'net_amount', type: 'number', format: 'float'),
    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'completed', 'failed', 'refunded']),
    new OA\Property(property: 'channel', type: 'string'),
    new OA\Property(property: 'reference', type: 'string', nullable: true),
    new OA\Property(property: 'paid_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'Wallet', properties: [
    new OA\Property(property: 'balance', type: 'number', format: 'float'),
    new OA\Property(property: 'pending_balance', type: 'number', format: 'float'),
    new OA\Property(property: 'total_earned', type: 'number', format: 'float'),
])]
#[OA\Schema(schema: 'WalletTransaction', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'type', type: 'string', enum: ['credit', 'debit']),
    new OA\Property(property: 'amount', type: 'number', format: 'float'),
    new OA\Property(property: 'balance_before', type: 'number', format: 'float'),
    new OA\Property(property: 'balance_after', type: 'number', format: 'float'),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'Notification', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'type', type: 'string'),
    new OA\Property(property: 'title', type: 'string'),
    new OA\Property(property: 'body', type: 'string', nullable: true),
    new OA\Property(property: 'is_read', type: 'boolean'),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'Conversation', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'subject', type: 'string', nullable: true),
    new OA\Property(property: 'last_message_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'Message', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'sender_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'content', type: 'string'),
    new OA\Property(property: 'status', type: 'string', enum: ['sent', 'read']),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'Review', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5),
    new OA\Property(property: 'comment', type: 'string', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'PortfolioItem', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'title', type: 'string'),
    new OA\Property(property: 'description', type: 'string', nullable: true),
    new OA\Property(property: 'project_url', type: 'string', nullable: true),
    new OA\Property(property: 'is_featured', type: 'boolean'),
])]
#[OA\Schema(schema: 'AdminDashboard', properties: [
    new OA\Property(property: 'total_users', type: 'integer'),
    new OA\Property(property: 'total_freelances', type: 'integer'),
    new OA\Property(property: 'total_clients', type: 'integer'),
    new OA\Property(property: 'total_projects', type: 'integer'),
    new OA\Property(property: 'total_contracts', type: 'integer'),
    new OA\Property(property: 'total_revenue', type: 'number', format: 'float'),
    new OA\Property(property: 'pending_verifications', type: 'integer'),
    new OA\Property(property: 'open_disputes', type: 'integer'),
])]
#[OA\Schema(schema: 'Verification', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'type', type: 'string'),
    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'approved', 'rejected']),
    new OA\Property(property: 'admin_notes', type: 'string', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'Report', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'type', type: 'string'),
    new OA\Property(property: 'description', type: 'string'),
    new OA\Property(property: 'status', type: 'string', enum: ['open', 'resolved', 'dismissed']),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'Dispute', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'reason', type: 'string'),
    new OA\Property(property: 'status', type: 'string', enum: ['open', 'resolved', 'dismissed']),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'PlatformSetting', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'key', type: 'string'),
    new OA\Property(property: 'value', type: 'string', nullable: true),
    new OA\Property(property: 'is_public', type: 'boolean'),
])]
#[OA\Schema(schema: 'Transaction', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'type', type: 'string', enum: ['mission', 'subscription', 'boost_profile', 'boost_project', 'badge_verified', 'ad', 'refund']),
    new OA\Property(property: 'amount', type: 'number', format: 'float'),
    new OA\Property(property: 'currency', type: 'string'),
    new OA\Property(property: 'payment_channel', type: 'string', nullable: true),
    new OA\Property(property: 'payment_operator', type: 'string', nullable: true),
    new OA\Property(property: 'operator_status', type: 'string'),
    new OA\Property(property: 'payment_url', type: 'string', nullable: true),
    new OA\Property(property: 'operator_reference', type: 'string', nullable: true),
    new OA\Property(property: 'paid_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'VerifiedBadge', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'freelance_profile_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'badge_type', type: 'string'),
    new OA\Property(property: 'is_active', type: 'boolean'),
    new OA\Property(property: 'granted_at', type: 'string', format: 'date-time'),
    new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'Boost', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'freelance_profile_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'target_type', type: 'string', enum: ['profile', 'project']),
    new OA\Property(property: 'target_id', type: 'string', format: 'uuid', nullable: true),
    new OA\Property(property: 'duration', type: 'string', enum: ['7_days', '30_days']),
    new OA\Property(property: 'amount_paid', type: 'number', format: 'float'),
    new OA\Property(property: 'is_active', type: 'boolean'),
    new OA\Property(property: 'started_at', type: 'string', format: 'date-time'),
    new OA\Property(property: 'ends_at', type: 'string', format: 'date-time'),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'SubscriptionPlanConfig', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'plan', type: 'string', enum: ['starter', 'pro', 'expert']),
    new OA\Property(property: 'name', type: 'string'),
    new OA\Property(property: 'description', type: 'string', nullable: true),
    new OA\Property(property: 'price_monthly', type: 'number', format: 'float'),
    new OA\Property(property: 'price_yearly', type: 'number', format: 'float'),
    new OA\Property(property: 'max_projects', type: 'integer', nullable: true),
    new OA\Property(property: 'max_quotes_per_month', type: 'integer', nullable: true),
    new OA\Property(property: 'has_verified_badge', type: 'boolean'),
    new OA\Property(property: 'has_boost_option', type: 'boolean'),
    new OA\Property(property: 'features', type: 'array', items: new OA\Items(type: 'string')),
    new OA\Property(property: 'is_active', type: 'boolean'),
    new OA\Property(property: 'sort_order', type: 'integer'),
])]
#[OA\Schema(schema: 'FreelanceSubscription', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'freelance_profile_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'plan_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'status', type: 'string', enum: ['active', 'cancelled', 'expired', 'trial']),
    new OA\Property(property: 'started_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'ends_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'trial_ends_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'billing_cycle', type: 'string', enum: ['monthly', 'yearly']),
    new OA\Property(property: 'amount_paid', type: 'number', format: 'float'),
    new OA\Property(property: 'auto_renew', type: 'boolean'),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
#[OA\Schema(schema: 'Referral', properties: [
    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'referrer_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'referred_id', type: 'string', format: 'uuid'),
    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'completed', 'paid']),
    new OA\Property(property: 'reward_amount', type: 'number', format: 'float'),
    new OA\Property(property: 'paid_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
])]
class ApiController extends Controller
{
    protected function success(mixed $data, string $message = "Success", int $code = 200): JsonResponse
    {
        return response()->json(["message" => $message, "data" => $data], $code);
    }

    protected function error(string $message, int $code = 400, mixed $errors = null): JsonResponse
    {
        $response = ["message" => $message];
        if ($errors) {
            $response["errors"] = $errors;
        }
        return response()->json($response, $code);
    }

    protected function created(mixed $data, string $message = "Created", int $code = 201): JsonResponse
    {
        return response()->json(["message" => $message, "data" => $data], $code);
    }

    protected function paginated($paginator): JsonResponse
    {
        return response()->json([
            "data" => $paginator->items(),
            "meta" => [
                "current_page" => $paginator->currentPage(),
                "last_page" => $paginator->lastPage(),
                "per_page" => $paginator->perPage(),
                "total" => $paginator->total(),
            ],
        ]);
    }
}
