<?php

namespace App\Filament\Resources\SystemSettings\Pages;

use App\Actions\Audit\RecordActivityAction;
use App\Filament\Resources\SystemSettings\SystemSettingResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSystemSetting extends EditRecord
{
    protected static string $resource = SystemSettingResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $updatedRecord = parent::handleRecordUpdate($record, $data);

        app(RecordActivityAction::class)->execute(
            event: 'system_settings.updated',
            description: 'System settings were updated.',
            subject: $updatedRecord,
            properties: $updatedRecord->only([
                'business_name',
                'business_timezone',
                'currency_code',
                'low_stock_contact_email',
            ]),
        );

        return $updatedRecord;
    }
}
