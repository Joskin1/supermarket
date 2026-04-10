<?php

namespace App\Policies;

use App\Models\SalesRecord;
use App\Models\User;
use App\Policies\Concerns\AuthorizesSalesAccess;

class SalesRecordPolicy
{
    use AuthorizesSalesAccess;

    public function viewAny(User $user): bool
    {
        return $this->canManageSales($user);
    }

    public function view(User $user, SalesRecord $salesRecord): bool
    {
        return $this->canManageSales($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, SalesRecord $salesRecord): bool
    {
        return false;
    }

    public function delete(User $user, SalesRecord $salesRecord): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
