<?php
namespace App\Services;

use App\Enums\ContractStatus;
use App\Enums\EscrowStatus;
use App\Enums\MilestoneStatus;
use App\Enums\ProjectStatus;
use App\Models\Contract;
use App\Models\Milestone;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ContractService
{
    public function listForUser(User $user): LengthAwarePaginator
    {
        return Contract::where("client_id", $user->id)
            ->orWhere("freelance_id", $user->id)
            ->orderBy("created_at", "desc")
            ->paginate(20);
    }

    public function find(User $user, string $id): ?Contract
    {
        return Contract::where("id", $id)
            ->where(function ($q) use ($user) {
                $q->where("client_id", $user->id)
                  ->orWhere("freelance_id", $user->id);
            })
            ->first();
    }

    public function sign(User $user, string $id): ?Contract
    {
        try {
            return $this->signContract($user, $id);
        } catch (\Exception) {
            return null;
        }
    }

    public function addMilestone(User $user, string $id, array $data): ?Milestone
    {
        $contract = $this->find($user, $id);
        if (!$contract) return null;
        return $this->addMilestoneToContract($contract, $data);
    }

    public function updateMilestone(User $user, string $contractId, string $milestoneId, array $data): ?Milestone
    {
        $contract = $this->find($user, $contractId);
        if (!$contract) return null;

        $milestone = $contract->milestones()->find($milestoneId);
        if (!$milestone) return null;

        $milestone->update([
            "title" => $data["title"] ?? $milestone->title,
            "description" => $data["description"] ?? $milestone->description,
            "amount" => $data["amount"] ?? $milestone->amount,
            "due_date" => $data["due_date"] ?? $milestone->due_date,
        ]);

        return $milestone->fresh();
    }

    public function deliverMilestone(User $user, string $contractId, string $milestoneId): ?Milestone
    {
        $contract = $this->find($user, $contractId);
        if (!$contract) return null;

        $milestone = $contract->milestones()->find($milestoneId);
        if (!$milestone) return null;

        return $this->deliverMilestoneForContract($milestone);
    }

    public function validateMilestone(User $user, string $contractId, string $milestoneId): ?Milestone
    {
        $contract = $this->find($user, $contractId);
        if (!$contract) return null;

        $milestone = $contract->milestones()->find($milestoneId);
        if (!$milestone) return null;

        return $this->validateMilestoneForContract($milestone);
    }

    public function createContract(array $data): Contract
    {
        $contract = Contract::create($data);
        return $contract;
    }

    public function signContract(User $user, string $contractId): Contract
    {
        $contract = Contract::findOrFail($contractId);

        if ($contract->client_id === $user->id) {
            $contract->update(["client_signed_at" => now()]);
        } elseif ($contract->freelance_id === $user->id) {
            $contract->update(["freelance_signed_at" => now()]);
        } else {
            abort(403, "Vous n'\u00eates pas partie prenante de ce contrat.");
        }

        if ($contract->client_signed_at && $contract->freelance_signed_at) {
            $contract->update(["status" => ContractStatus::Signed]);
        }

        return $contract->fresh();
    }

    public function addMilestoneToContract(Contract $contract, array $data): Milestone
    {
        return $contract->milestones()->create([
            "title" => $data["title"],
            "description" => $data["description"] ?? null,
            "amount" => $data["amount"],
            "due_date" => $data["due_date"] ?? null,
        ]);
    }

    public function deliverMilestoneForContract(Milestone $milestone): Milestone
    {
        if ($milestone->status !== MilestoneStatus::Pending) {
            abort(400, "Ce jalon ne peut pas \u00eatre livr\u00e9.");
        }

        $milestone->update([
            "is_completed" => true,
            "delivered_at" => now(),
        ]);

        return $milestone->fresh();
    }

    public function validateMilestoneForContract(Milestone $milestone): Milestone
    {
        if ($milestone->status !== MilestoneStatus::Delivered) {
            abort(400, "Ce jalon n'a pas encore \u00e9t\u00e9 livr\u00e9.");
        }

        $milestone->update([
            "is_completed" => true,
            "validated_at" => now(),
        ]);

        return $milestone->fresh();
    }
}
