<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Policies\Concerns\AuthorizesInventoryAccess;

class CategoryPolicy
{
    use AuthorizesInventoryAccess;

    public function viewAny(User $user): bool
    {
        return $this->canManageInventory($user);
    }

    public function view(User $user, Category $category): bool
    {
        return $this->canManageInventory($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageInventory($user);
    }

    public function update(User $user, Category $category): bool
    {
        return $this->canManageInventory($user);
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->canManageInventory($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canManageInventory($user);
    }
}
