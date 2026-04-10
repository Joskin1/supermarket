<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\RoleEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->helperText('A verification email will be sent when a new user is created or when this address is changed. The user must verify the address before signing in.')
                            ->unique(ignoreRecord: true),
                        Select::make('role')
                            ->options(RoleEnum::options())
                            ->required()
                            ->default(RoleEnum::ADMIN->value)
                            ->native(false),
                        TextInput::make('password')
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->autocomplete('new-password')
                            ->helperText('Leave blank to keep the current password when editing.')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->rule(Password::default())
                            ->same('passwordConfirmation')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->validationAttribute('password'),
                        TextInput::make('passwordConfirmation')
                            ->label('Confirm password')
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->autocomplete('new-password')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ]);
    }
}
