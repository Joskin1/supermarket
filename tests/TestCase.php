<?php

namespace Tests;

use App\Enums\RoleEnum;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }

    protected function makeSudo(array $attributes = []): User
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create($attributes);
        $user->assignRole(RoleEnum::SUDO->value);

        return $user;
    }

    protected function confirmedTwoFactorAttributes(): array
    {
        return [
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code-1', 'code-2'])),
            'two_factor_confirmed_at' => now(),
        ];
    }
}
