<?php

namespace App\Filament\Resources\Users\Pages;

use App\Actions\Audit\RecordActivityAction;
use App\Actions\Users\SendUserEmailVerificationAction;
use App\Filament\Resources\Users\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $role = $data['role'];

        unset($data['role']);

        $record = parent::handleRecordCreation($data);
        $record->syncRoles([$role]);
        app(SendUserEmailVerificationAction::class)->execute($record, markAsUnverified: true);
        app(RecordActivityAction::class)->execute(
            event: 'user.created',
            description: 'A new user account was created.',
            subject: $record,
            properties: [
                'user_id' => $record->id,
                'email' => $record->email,
                'role' => $role,
            ],
        );

        return $record;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('User created')
            ->body('A verification email has been sent. The user must verify their email before signing in.');
    }
}
