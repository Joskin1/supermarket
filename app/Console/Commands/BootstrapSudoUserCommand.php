<?php

namespace App\Console\Commands;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class BootstrapSudoUserCommand extends Command
{
    protected $signature = 'users:bootstrap-sudo
        {email : Email address for the sudo user}
        {--name=Supermarket Sudo : Display name for the sudo user}
        {--password= : Password for the sudo user}';

    protected $description = 'Create or update the first sudo user with an explicitly supplied credential set';

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));
        $name = trim((string) $this->option('name'));
        $password = (string) ($this->option('password') ?: $this->secret('Password for the sudo user'));

        $data = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', Password::default()],
        ])->validate();

        /** @var User $user */
        $user = User::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ],
        );

        if (! $user->email_verified_at) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        $user->syncRoles([RoleEnum::SUDO->value]);

        $this->info("Sudo user ready: {$user->email}");

        return self::SUCCESS;
    }
}
