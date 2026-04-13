<?php

namespace App\Policies;

use App\Models\SystemSetting;
use App\Models\User;

class SystemSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSudo();
    }

    public function view(User $user, SystemSetting $systemSetting): bool
    {
        return $user->isSudo();
    }

    public function create(User $user): bool
    {
        return $user->isSudo() && ! SystemSetting::query()->exists();
    }

    public function update(User $user, SystemSetting $systemSetting): bool
    {
        return $user->isSudo();
    }

    public function delete(User $user, SystemSetting $systemSetting): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
