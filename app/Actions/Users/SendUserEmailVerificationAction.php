<?php

namespace App\Actions\Users;

use App\Models\User;

class SendUserEmailVerificationAction
{
    public function execute(User $user, bool $markAsUnverified = false): void
    {
        if (blank($user->email)) {
            return;
        }

        if ($markAsUnverified && $user->hasVerifiedEmail()) {
            $user->markEmailAsUnverified();
        }

        $user->sendEmailVerificationNotification();
    }
}
