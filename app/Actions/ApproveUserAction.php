<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApproveUserAction
{
    /**
     * Validate if a user meets the requirements for approval.
     */
    public function validate(User $user): array
    {
        $errors = [];

        if ($user->isApproved()) {
            $errors[] = "This account is already approved.";
        }

        if ($user->hasRole('carrier')) {
            if ($user->fleets()->count() === 0) {
                $errors[] = "No fleet information has been supplied.";
            }
            if ($user->traderefs()->count() < 2) {
                $errors[] = "At least 2 trade references are required.";
            }
            if ($user->directors()->count() < 2) {
                $errors[] = "At least 2 directors are required.";
            }
            if ($user->buslocation()->count() === 0) {
                $errors[] = "No business location has been set.";
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Execute the approval process.
     */
    public function execute(User $user, int $approverId): void
    {
        $validation = $this->validate($user);

        if (!$validation['is_valid']) {
            throw ValidationException::withMessages([
                'approval' => $validation['errors'],
            ]);
        }

        DB::transaction(function () use ($user, $approverId) {
            $generatedId = $user->identification_number;
            $user->update([
                'approved_at' => now(),
                'approved_by_id' => $approverId,
                'approved_by_id' => $approverId,
                'identification_number' => $generatedId,
            ]);

            // Optional: Log activity or send notification here
        });
    }
}