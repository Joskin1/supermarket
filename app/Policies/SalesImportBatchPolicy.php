<?php

namespace App\Policies;

use App\Models\SalesImportBatch;
use App\Models\User;
use App\Policies\Concerns\AuthorizesSalesAccess;

class SalesImportBatchPolicy
{
    use AuthorizesSalesAccess;

    public function viewAny(User $user): bool
    {
        return $this->canManageSales($user);
    }

    public function view(User $user, SalesImportBatch $salesImportBatch): bool
    {
        return $this->canManageSales($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageSales($user);
    }

    public function update(User $user, SalesImportBatch $salesImportBatch): bool
    {
        return false;
    }

    public function delete(User $user, SalesImportBatch $salesImportBatch): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
