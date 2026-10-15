<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Notifications\EmailChangeRequested;
use App\Notifications\VerifyNewEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Update the name, and request an e-mail change.
     *
     * A new address requires the current password. For a verified account it is stored in
     * pending_email and only replaces the current address once confirmed from the link sent to it
     * (the current address keeps working meanwhile and is warned of the request).
     *
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        $input['email'] = Str::lower(trim((string) ($input['email'] ?? '')));
        $emailChanges = $input['email'] !== $user->email;

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
                Rule::unique('users', 'pending_email')->ignore($user->id),
            ],
            'current_password' => $emailChanges ? ['required', 'string', 'current_password:web'] : ['nullable'],
        ])->validateWithBag('updateProfileInformation');

        $user->forceFill(['name' => $input['name']]);

        if (! $emailChanges) {
            $user->forceFill(['pending_email' => null])->save();

            return;
        }

        if (! $user->hasVerifiedEmail()) {
            // Never verified: nothing to protect yet, simply fix the address and resend the link.
            $user->forceFill(['email' => $input['email'], 'pending_email' => null])->save();
            $user->sendEmailVerificationNotification();

            return;
        }

        $user->forceFill(['pending_email' => $input['email']])->save();

        Notification::route('mail', $input['email'])->notify(new VerifyNewEmail($user));
        $user->notify(new EmailChangeRequested($input['email']));
    }
}
