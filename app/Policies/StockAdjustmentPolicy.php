<?php

namespace App\Policies;

use App\Models\StockAdjustment;
use App\Models\User;
use App\Policies\Concerns\AuthorizesInventoryAccess;

class StockAdjustmentPolicy
{
    use AuthorizesInventoryAccess;

    public function viewAny(User $user): bool
    {
        return $this->canManageInventory($user);
    }

    public function view(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $this->canManageInventory($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageInventory($user);
    }

    public function update(User $user, StockAdjustment $stockAdjustment): bool
    {
        return false;
    }

    public function delete(User $user, StockAdjustment $stockAdjustment): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
