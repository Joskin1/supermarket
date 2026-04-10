<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Policies\Concerns\AuthorizesInventoryAccess;

class ProductPolicy
{
    use AuthorizesInventoryAccess;

    public function viewAny(User $user): bool
    {
        return $this->canManageInventory($user);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->canManageInventory($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageInventory($user);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->canManageInventory($user);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->canManageInventory($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canManageInventory($user);
    }
}
