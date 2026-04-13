<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class SudoUserSeeder extends Seeder
{
    private const DEFAULT_NAME = 'Supermarket Sudo';

    public function run(): void
    {
        $email = env('SUDO_EMAIL');
        $password = env('SUDO_PASSWORD');

        if (blank($email) || blank($password)) {
            throw new InvalidArgumentException(
                'SUDO_EMAIL and SUDO_PASSWORD must be set before running SudoUserSeeder. '
                .'Use the users:bootstrap-sudo command for explicit onboarding instead.'
            );
        }

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
