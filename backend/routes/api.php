<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{
    AuthController,
    ProfileController,
    ProjectController,
    QuoteController,
    ContractController,
    PaymentController,
    WalletController,
    MessageController,
    NotificationController,
    ReviewController,
    AdminController,
    CategoryController,
};

// Public routes
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{id}/skills', [CategoryController::class, 'skills']);
Route::get('freelances', [ProfileController::class, 'freelanceList']);
Route::get('freelances/{id}', [ProfileController::class, 'freelanceDetail']);
Route::get('projects', [ProjectController::class, 'index']);
Route::get('projects/{id}', [ProjectController::class, 'show']);

// Auth routes (proxy for Supabase)
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/social/{provider}', [AuthController::class, 'socialAuth']);

// Webhooks (no auth)
Route::post('webhooks/genius-pay', [PaymentController::class, 'webhook']);

// Authenticated routes
Route::middleware(['supabase.auth'])->group(function () {
    // Auth
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/login', [AuthController::class, 'login']);

    // Profiles
    Route::prefix('profiles')->group(function () {
        Route::get('me', [ProfileController::class, 'me']);
        Route::put('me', [ProfileController::class, 'updateMe']);
        Route::get('client', [ProfileController::class, 'clientProfile']);
        Route::put('client', [ProfileController::class, 'updateClientProfile']);
        Route::get('freelance', [ProfileController::class, 'freelanceProfile']);
        Route::put('freelance', [ProfileController::class, 'updateFreelanceProfile']);
        Route::post('freelance/skills', [ProfileController::class, 'addSkill']);
        Route::delete('freelance/skills/{skill}', [ProfileController::class, 'removeSkill']);
        Route::post('freelance/portfolio', [ProfileController::class, 'addPortfolioItem']);
        Route::delete('freelance/portfolio/{item}', [ProfileController::class, 'removePortfolioItem']);
    });

    // Projects
    Route::post('projects', [ProjectController::class, 'store']);
    Route::put('projects/{id}', [ProjectController::class, 'update']);
    Route::delete('projects/{id}', [ProjectController::class, 'destroy']);
    Route::post('projects/{id}/files', [ProjectController::class, 'addFile']);
    Route::delete('projects/{project}/files/{file}', [ProjectController::class, 'removeFile']);

    // Quotes
    Route::get('projects/{project}/quotes', [QuoteController::class, 'index']);
    Route::post('projects/{project}/quotes', [QuoteController::class, 'store']);
    Route::get('quotes/{id}', [QuoteController::class, 'show']);
    Route::put('quotes/{id}', [QuoteController::class, 'update']);
    Route::delete('quotes/{id}', [QuoteController::class, 'destroy']);
    Route::post('quotes/{id}/accept', [QuoteController::class, 'accept']);
    Route::post('quotes/{id}/refuse', [QuoteController::class, 'refuse']);

    // Contracts
    Route::get('contracts', [ContractController::class, 'index']);
    Route::get('contracts/{id}', [ContractController::class, 'show']);
    Route::post('contracts/{id}/sign', [ContractController::class, 'sign']);
    Route::post('contracts/{id}/milestones', [ContractController::class, 'addMilestone']);
    Route::put('contracts/{contract}/milestones/{milestone}', [ContractController::class, 'updateMilestone']);
    Route::post('contracts/{contract}/milestones/{milestone}/deliver', [ContractController::class, 'deliverMilestone']);
    Route::post('contracts/{contract}/milestones/{milestone}/validate', [ContractController::class, 'validateMilestone']);

    // Payments
    Route::post('payments/initiate', [PaymentController::class, 'initiate']);
    Route::post('payments/{id}/confirm', [PaymentController::class, 'confirm']);
    Route::get('payments/{id}', [PaymentController::class, 'show']);
    Route::get('payments', [PaymentController::class, 'index']);

    // Wallet
    Route::get('wallet', [WalletController::class, 'show']);
    Route::get('wallet/transactions', [WalletController::class, 'transactions']);
    Route::post('wallet/withdraw', [WalletController::class, 'withdraw']);

    // Messaging
    Route::get('conversations', [MessageController::class, 'conversations']);
    Route::post('conversations', [MessageController::class, 'startConversation']);
    Route::get('conversations/{id}', [MessageController::class, 'messages']);
    Route::post('conversations/{id}/messages', [MessageController::class, 'sendMessage']);
    Route::put('messages/{id}/read', [MessageController::class, 'markAsRead']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);

    // Reviews
    Route::post('contracts/{contract}/review', [ReviewController::class, 'store']);
    Route::get('freelances/{freelance}/reviews', [ReviewController::class, 'freelanceReviews']);
    Route::post('reviews/{review}/reply', [ReviewController::class, 'reply']);

    // Admin
    Route::prefix('admin')->middleware(['role:admin'])->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard']);
        Route::get('users', [AdminController::class, 'users']);
        Route::put('users/{id}/status', [AdminController::class, 'updateUserStatus']);
        Route::get('verifications', [AdminController::class, 'verifications']);
        Route::post('verifications/{id}/approve', [AdminController::class, 'approveVerification']);
        Route::post('verifications/{id}/reject', [AdminController::class, 'rejectVerification']);
        Route::get('reports', [AdminController::class, 'reports']);
        Route::put('reports/{id}', [AdminController::class, 'resolveReport']);
        Route::get('disputes', [AdminController::class, 'disputes']);
        Route::put('disputes/{id}', [AdminController::class, 'resolveDispute']);
        Route::get('payments', [AdminController::class, 'payments']);
        Route::get('settings', [AdminController::class, 'settings']);
        Route::put('settings/{key}', [AdminController::class, 'updateSetting']);
    });
});
