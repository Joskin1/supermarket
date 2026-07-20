<?php

namespace App\Filament\Resources\SystemSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SystemSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Business profile')
                    ->description('These details personalize this inventory system for the business using it.')
                    ->schema([
                        FileUpload::make('business_logo_path')
                            ->label('Business logo')
                            ->image()
                            ->disk('local')
                            ->directory('business/logos')
                            ->maxSize(2048)
                            ->imageEditor()
                            ->columnSpanFull(),
                        TextInput::make('business_name')
                            ->label('Business name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('business_type')
                            ->label('Business type')
                            ->placeholder('Pharmacy, boutique, supermarket, electronics shop...')
                            ->maxLength(255),
                        TextInput::make('business_timezone')
                            ->label('Business timezone')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Use a valid PHP timezone identifier, for example Africa/Lagos.')
                            ->rule(Rule::in(timezone_identifiers_list())),
                        TextInput::make('currency_code')
                            ->required()
                            ->length(3)
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Str::upper(trim($state)) : null),
                        TextInput::make('low_stock_contact_email')
                            ->label('Low-stock alert contact email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('business_phone')
                            ->label('Business phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('business_email')
                            ->label('Business email')
                            ->email()
                            ->maxLength(255),
                        Textarea::make('business_address')
                            ->label('Business address')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('receipt_footer')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Optional footer text for printed or exported receipts and reports.'),
                    ])
                    ->columns(2),
            ]);
    }
}
