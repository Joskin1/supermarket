<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SudoUserSeeder extends Seeder
{
    private const DEFAULT_EMAIL = 'akinjoseph221@gmail.com';

    private const DEFAULT_PASSWORD = 'akinjoseph221@gmail.com';

    private const DEFAULT_NAME = 'Supermarket Sudo';

    public function run(): void
    {
        $email = env('SUDO_EMAIL', self::DEFAULT_EMAIL);
        $password = env('SUDO_PASSWORD', self::DEFAULT_PASSWORD);

        // Development bootstrap only. Production should replace these defaults
        // with secure environment-driven credentials or a separate onboarding flow.
        $sudoUser = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => self::DEFAULT_NAME,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );

        if (! $sudoUser->email_verified_at) {
            $sudoUser->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        $sudoUser->syncRoles([RoleEnum::SUDO->value]);
    }
}
