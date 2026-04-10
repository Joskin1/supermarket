<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait AuthorizesSalesAccess
{
    protected function canManageSales(User $user): bool
    {
        return $user->isAdmin();
    }
}
