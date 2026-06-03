<?php
namespace App\Services;

use App\Enums\ContractStatus;
use App\Enums\EscrowStatus;
use App\Enums\ProjectStatus;
use App\Enums\QuoteStatus;
use App\Enums\SubscriptionPlan;
use App\Models\Contract;
use App\Models\Escrow;
use App\Models\Project;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    public function __construct(
        private readonly ContractService $contractService,
        private readonly NotificationService $notificationService,
    ) {}

    public function listForProject(User $user, string $projectId): Collection
    {
        return Quote::where('project_id', $projectId)
            ->with('freelance')
            ->orderByDesc('created_at')
            ->get();
    }

    public function find(User $user, string $id): ?Quote
    {
        return Quote::with(['project', 'freelance'])->find($id);
    }

    public function update(User $user, string $id, array $data): ?Quote
    {
        $quote = Quote::find($id);
        if (!$quote) return null;

        if ($quote->freelance_id !== $user->id) {
            return null;
        }

        if ($quote->status !== QuoteStatus::Pending) {
            return null;
        }

        $quote->update([
            'amount' => $data['amount'] ?? $quote->amount,
            'currency' => $data['currency'] ?? $quote->currency,
            'estimated_duration' => $data['duration'] ?? $data['estimated_duration'] ?? $quote->estimated_duration,
            'proposal' => $data['description'] ?? $data['proposal'] ?? $quote->proposal,
        ]);

        return $quote;
    }

    public function delete(User $user, string $id): bool
    {
        try {
            $this->withdrawQuote($user, $id);
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function create(User $freelance, string $projectId, array $data): ?Quote
    {
        try {
            return $this->createQuote($freelance, $projectId, $data);
        } catch (\Exception) {
            return null;
        }
    }

    public function accept(User $client, string $quoteId): ?Contract
    {
        try {
            return $this->acceptQuote($client, $quoteId);
        } catch (\Exception) {
            return null;
        }
    }

    public function refuse(User $client, string $quoteId): bool
    {
        try {
            $this->refuseQuote($client, $quoteId);
            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function createQuote(User $freelance, string $projectId, array $data): Quote
    {
        $project = Project::findOrFail($projectId);

        $this->checkSubscriptionLimits($freelance);
        $this->checkExistingQuote($freelance, $project);

        return DB::transaction(function () use ($freelance, $project, $data) {
            $quote = $freelance->quotes()->create([
                "project_id" => $project->id,
                "amount" => $data["amount"],
                "currency" => $data["currency"] ?? "XOF",
                "estimated_duration" => $data["duration"] ?? null,
                "proposal" => $data["description"] ?? null,
                "status" => QuoteStatus::Pending,
            ]);

            $this->notificationService->createNotification(
                $project->client,
                "offer",
                "Nouvelle offre re\u00e7ue",
                ($freelance->name ?? $freelance->email) . " a soumis une offre sur votre projet \"{$project->title}\".",
                ["quote_id" => $quote->id, "project_id" => $project->id],
            );

            return $quote;
        });
    }

    public function acceptQuote(User $client, string $quoteId): Contract
    {
        $quote = Quote::with("project")->findOrFail($quoteId);

        if ($quote->project->client_id !== $client->id) {
            abort(403, "Vous n'\u00eates pas le propri\u00e9taire de ce projet.");
        }

        if ($quote->status !== QuoteStatus::Pending) {
            abort(400, "Cette offre n'est plus disponible.");
        }

        return DB::transaction(function () use ($quote) {
            $quote->update(["status" => QuoteStatus::Accepted]);
            $quote->project->update(["status" => ProjectStatus::InProgress]);

            $contract = $this->contractService->createContract([
                "project_id" => $quote->project_id,
                "client_id" => $quote->project->client_id,
                "freelance_id" => $quote->freelance_id,
                "quote_id" => $quote->id,
                "title" => $quote->project->title,
                "total_amount" => $quote->amount,
                "currency" => $quote->currency,
                "status" => ContractStatus::Draft,
            ]);

            Escrow::create([
                "contract_id" => $contract->id,
                "payment_id" => null,
                "amount" => $quote->amount,
                "status" => EscrowStatus::Holding,
            ]);

            return $contract;
        });
    }

    public function refuseQuote(User $client, string $quoteId): void
    {
        $quote = Quote::with("project")->findOrFail($quoteId);

        if ($quote->project->client_id !== $client->id) {
            abort(403, "Vous n'\u00eates pas le propri\u00e9taire de ce projet.");
        }

        $quote->update(["status" => QuoteStatus::Refused]);
    }

    public function withdrawQuote(User $freelance, string $quoteId): void
    {
        $quote = $freelance->quotes()->findOrFail($quoteId);

        if ($quote->status !== QuoteStatus::Pending) {
            abort(400, "Cette offre ne peut plus \u00eatre retir\u00e9e.");
        }

        $quote->update(["status" => QuoteStatus::Withdrawn]);
    }

    private function checkSubscriptionLimits(User $freelance): void
    {
        $subscription = $freelance->subscription;
        $monthlyQuotes = $freelance->quotes()
            ->whereMonth("created_at", now()->month)
            ->whereYear("created_at", now()->year)
            ->count();

        $limits = [
            SubscriptionPlan::Starter->value => 5,
            SubscriptionPlan::Pro->value => 20,
            SubscriptionPlan::Expert->value => -1,
        ];

        $limit = $limits[$subscription?->plan?->value ?? SubscriptionPlan::Starter->value] ?? 5;

        if ($limit !== -1 && $monthlyQuotes >= $limit) {
            abort(429, "Vous avez atteint votre limite mensuelle d'offres.");
        }
    }

    private function checkExistingQuote(User $freelance, Project $project): void
    {
        $existing = $freelance->quotes()
            ->where("project_id", $project->id)
            ->whereIn("status", [QuoteStatus::Pending, QuoteStatus::Accepted])
            ->exists();

        if ($existing) {
            abort(409, "Vous avez d\u00e9j\u00e0 soumis une offre sur ce projet.");
        }
    }
}
