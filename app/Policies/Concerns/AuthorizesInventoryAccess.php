<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait AuthorizesInventoryAccess
{
    protected function canManageInventory(User $user): bool
    {
        return $user->isAdmin();
    }
}
