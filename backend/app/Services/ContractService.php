<?php
namespace App\Services;

use App\Enums\ContractStatus;
use App\Enums\EscrowStatus;
use App\Enums\MilestoneStatus;
use App\Enums\ProjectStatus;
use App\Models\Contract;
use App\Models\Milestone;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContractService
{
    public function createContract(array $data): Contract
    {
        return Contract::create($data);
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

    public function addMilestone(Contract $contract, array $data): Milestone
    {
        return $contract->milestones()->create([
            "title" => $data["title"],
            "description" => $data["description"] ?? null,
            "amount_xof" => $data["amount"] ?? $data["amount_xof"],
            "due_date" => $data["due_date"] ?? null,
        ]);
    }

    public function deliverMilestone(Milestone $milestone): Milestone
    {
        if ($milestone->status !== MilestoneStatus::Pending) {
            abort(400, "Ce jalon ne peut pas \u00eatre livr\u00e9.");
        }

        $milestone->update([
            "status" => MilestoneStatus::Delivered,
            "delivered_at" => now(),
        ]);

        return $milestone->fresh();
    }

    public function validateMilestone(Milestone $milestone): Milestone
    {
        if ($milestone->status !== MilestoneStatus::Delivered) {
            abort(400, "Ce jalon n'a pas encore \u00e9t\u00e9 livr\u00e9.");
        }

        $milestone->update([
            "status" => MilestoneStatus::Validated,
            "validated_at" => now(),
        ]);

        return $milestone->fresh();
    }
}
