<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait AuthorizesReportingAccess
{
    protected static function canAccessReports(?User $user): bool
    {
        return (bool) ($user?->isAdmin() || $user?->isSudo());
    }
}
