<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('doggys:make-admin {email}', function (string $email) {
    $user = User::where('email', Str::lower($email))->first();

    if (! $user) {
        $this->error("No user found with the e-mail address {$email}.");

        return 1;
    }

    $user->forceFill(['role' => UserRole::Admin])->save();
    $this->info("{$user->name} <{$user->email}> is now an administrator.");

    return 0;
})->purpose('Promote an existing user to administrator');
