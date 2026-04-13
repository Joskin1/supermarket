<?php

use App\Actions\Users\EnsureUserAccountSafetyAction;
use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout, EnsureUserAccountSafetyAction $accountSafety): void
    {
        $this->resetErrorBag('account');

        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        try {
            $accountSafety->ensureCanDelete(Auth::user());
        } catch (ValidationException $exception) {
            $this->addError('account', Arr::join(Arr::flatten($exception->errors()), ' '));

            return;
        }

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form method="POST" wire:submit="deleteUser" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Are you sure you want to delete your account?') }}</flux:heading>

            <flux:subheading>
                {{ __('Deleting this account removes its sign-in access. The request will be blocked if this is the last sudo account or if the account is still referenced by inventory or sales history. Enter your password to continue.') }}
            </flux:subheading>
        </div>

        @if ($errors->has('account'))
            <flux:text class="text-sm text-red-600 dark:text-red-400">
                {{ $errors->first('account') }}
            </flux:text>
        @endif

        <flux:input wire:model="password" :label="__('Password')" type="password" viewable />

        <div class="flex justify-end space-x-2 rtl:space-x-reverse">
            <flux:modal.close>
                <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>

            <flux:button variant="danger" type="submit" data-test="confirm-delete-user-button">
                {{ __('Delete account') }}
            </flux:button>
        </div>
    </form>
</flux:modal>
