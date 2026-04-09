<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum RoleEnum: string
{
    case SUDO = 'sudo';
    case ADMIN = 'admin';

    public function label(): string
    {
        return Str::headline($this->value);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $role) {
            $options[$role->value] = $role->label();
        }

        return $options;
    }
}
