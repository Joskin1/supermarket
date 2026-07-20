<?php

namespace App\Filament\Resources\SystemSettings\Pages;

use App\Actions\Audit\RecordActivityAction;
use App\Filament\Resources\SystemSettings\SystemSettingResource;
use App\Models\SystemSetting;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSystemSetting extends CreateRecord
{
    protected static string $resource = SystemSettingResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation([
            ...$data,
            'singleton_key' => SystemSetting::SINGLETON_KEY,
        ]);
        $record->markBusinessProfileComplete();

        app(RecordActivityAction::class)->execute(
            event: 'system_settings.created',
            description: 'System settings were created.',
            subject: $record,
            properties: $record->only([
                'business_name',
                'business_type',
                'business_timezone',
                'currency_code',
                'low_stock_contact_email',
                'business_phone',
                'business_email',
            ]),
        );

        return $record;
    }
}
