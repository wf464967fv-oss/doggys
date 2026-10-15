<?php

use App\Enums\ClaimStatus;
use App\Enums\UserRole;
use App\Models\BadgeRequest;
use App\Models\Claim;
use App\Models\ContactMessage;
use App\Models\ProviderReport;
use App\Models\User;
use Database\Seeders\GuideSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
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

Artisan::command('doggys:import-guides', function () {
    return $this->call('db:seed', ['--class' => GuideSeeder::class, '--force' => true]);
})->purpose('Import (or update) the Markdown guides of database/data/guides');

/*
 * Retention promised in the privacy policy: proofs of rejected requests after 90 days,
 * handled contact messages and reports after 12 months.
 */
Artisan::command('doggys:purge', function () {
    $proofs = 0;
    foreach ([Claim::class, BadgeRequest::class] as $model) {
        $model::where('status', ClaimStatus::Rejected)->whereNotNull('proof_path')
            ->where('handled_at', '<', now()->subDays(90))
            ->each(function ($request) use (&$proofs) {
                Storage::disk('local')->delete($request->proof_path);
                $request->forceFill(['proof_path' => null])->save();
                $proofs++;
            });
    }

    $old = now()->subMonths(12);
    $messages = ContactMessage::whereNotNull('handled_at')->where('handled_at', '<', $old)->delete();
    $reports = ProviderReport::whereNotNull('handled_at')->where('handled_at', '<', $old)->delete();

    $this->info("Purged {$proofs} rejected proofs, {$messages} contact messages, {$reports} reports.");
})->purpose('Delete data past its retention period (GDPR)');

Schedule::command('doggys:purge')->dailyAt('03:30');
Schedule::command('auth:clear-resets')->daily();
