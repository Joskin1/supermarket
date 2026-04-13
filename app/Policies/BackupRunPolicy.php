<?php

namespace App\Policies;

use App\Models\BackupRun;
use App\Models\User;

class BackupRunPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSudo();
    }

    public function view(User $user, BackupRun $backupRun): bool
    {
        return $user->isSudo();
    }

    public function create(User $user): bool
    {
        return $user->isSudo();
    }

    public function update(User $user, BackupRun $backupRun): bool
    {
        return false;
    }

    public function delete(User $user, BackupRun $backupRun): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
