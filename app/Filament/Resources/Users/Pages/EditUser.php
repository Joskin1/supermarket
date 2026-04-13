<?php

namespace App\Filament\Resources\Users\Pages;

use App\Actions\Audit\RecordActivityAction;
use App\Actions\Users\EnsureUserAccountSafetyAction;
use App\Actions\Users\SendUserEmailVerificationAction;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected bool $verificationEmailWasSent = false;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role'] = $this->getRecord()->roles->value('name');

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $role = $data['role'];
        $emailChanged = $record->email !== $data['email'];
        $previousRole = $record->roles->value('name');

        app(EnsureUserAccountSafetyAction::class)->ensureRoleChangeIsSafe($record, $role);

        unset($data['role']);

        $record = parent::handleRecordUpdate($record, $data);
        $record->syncRoles([$role]);

        if ($emailChanged) {
            app(SendUserEmailVerificationAction::class)->execute($record, markAsUnverified: true);
            $this->verificationEmailWasSent = true;
        }

        app(RecordActivityAction::class)->execute(
            event: 'user.updated',
            description: 'A user account was updated.',
            subject: $record,
            properties: [
                'user_id' => $record->id,
                'email' => $record->email,
                'previous_role' => $previousRole,
                'role' => $role,
                'email_changed' => $emailChanged,
            ],
        );

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resend_verification_email')
                ->label('Resend verification email')
                ->icon('heroicon-o-envelope')
                ->visible(fn (): bool => ! $this->getRecord()->hasVerifiedEmail())
                ->action(function (): void {
                    app(SendUserEmailVerificationAction::class)->execute($this->getRecord());

                    Notification::make()
                        ->success()
                        ->title('Verification email resent')
                        ->body('The user can verify the email address before signing in.')
                        ->send();
                }),
            DeleteAction::make()
                ->before(function (DeleteAction $action): void {
                    try {
                        app(EnsureUserAccountSafetyAction::class)->ensureCanDelete($this->getRecord());
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->danger()
                            ->title('User cannot be deleted')
                            ->body(Arr::join(Arr::flatten($exception->errors()), ' '))
                            ->send();

                        $action->cancel();
                    }
                })
                ->after(function (): void {
                    $record = $this->getRecord();

                    app(RecordActivityAction::class)->execute(
                        event: 'user.deleted',
                        description: 'A user account was deleted.',
                        subject: $record,
                        properties: [
                            'user_id' => $record->id,
                            'email' => $record->email,
                        ],
                    );
                }),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('User updated')
            ->body($this->verificationEmailWasSent
                ? 'The email address changed, so a new verification email has been sent.'
                : null);
    }
}
