<?php

namespace App\Actions\Users;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class EnsureUserAccountSafetyAction
{
    /**
     * @throws ValidationException
     */
    public function ensureCanDelete(User $user): void
    {
        if ($message = $this->deletionBlockMessage($user)) {
            throw ValidationException::withMessages([
                'account' => $message,
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    public function ensureRoleChangeIsSafe(User $user, string $newRole): void
    {
        if ($user->isSudo() && ($newRole !== RoleEnum::SUDO->value) && $this->isLastSudo($user)) {
            throw ValidationException::withMessages([
                'role' => 'The last sudo user cannot be reassigned. Create another sudo user first.',
            ]);
        }
    }

    public function deletionBlockMessage(User $user): ?string
    {
        if ($this->isLastSudo($user)) {
            return 'The last sudo user cannot be deleted. Create another sudo user first.';
        }

        if ($this->hasOperationalHistory($user)) {
            return 'This account has inventory or sales history and cannot be deleted. Keep it for accountability and auditability.';
        }

        return null;
    }

    protected function isLastSudo(User $user): bool
    {
        return $user->exists
            && $user->isSudo()
            && (User::query()->role(RoleEnum::SUDO->value)->count() === 1);
    }

    protected function hasOperationalHistory(User $user): bool
    {
        return $user->uploadedSalesImportBatches()->exists()
            || $user->salesRecords()->exists()
            || $user->stockEntries()->exists()
            || $user->stockAdjustments()->exists();
    }
}
