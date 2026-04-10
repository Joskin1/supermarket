<?php

namespace App\Policies;

use App\Models\StockEntry;
use App\Models\User;
use App\Policies\Concerns\AuthorizesInventoryAccess;

class StockEntryPolicy
{
    use AuthorizesInventoryAccess;

    public function viewAny(User $user): bool
    {
        return $this->canManageInventory($user);
    }

    public function view(User $user, StockEntry $stockEntry): bool
    {
        return $this->canManageInventory($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageInventory($user);
    }

    public function update(User $user, StockEntry $stockEntry): bool
    {
        return false;
    }

    public function delete(User $user, StockEntry $stockEntry): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
