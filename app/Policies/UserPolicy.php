<?php

namespace App\Policies;

use App\Actions\Users\EnsureUserAccountSafetyAction;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSudo();
    }

    public function view(User $user, User $record): bool
    {
        return $user->isSudo();
    }

    public function create(User $user): bool
    {
        return $user->isSudo();
    }

    public function update(User $user, User $record): bool
    {
        return $user->isSudo();
    }

    public function delete(User $user, User $record): bool
    {
        if (! $user->isSudo()) {
            return false;
        }

        // Prevent deleting yourself through the admin panel.
        if ($user->id === $record->id) {
            return false;
        }

        // Delegate business-rule checks (last-sudo, operational history) to the action.
        $message = app(EnsureUserAccountSafetyAction::class)->deletionBlockMessage($record);

        return $message === null;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
