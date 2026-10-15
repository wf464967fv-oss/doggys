<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\VerificationLevel;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isPro(): bool
    {
        return $this->role === UserRole::Pro;
    }

    /**
     * @return HasMany<Dog, $this>
     */
    public function dogs(): HasMany
    {
        return $this->hasMany(Dog::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<Claim, $this>
     */
    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    /**
     * @return HasMany<Provider, $this>
     */
    public function claimedProviders(): HasMany
    {
        return $this->hasMany(Provider::class, 'claimed_by_user_id');
    }

    /**
     * Review reports written by the user.
     *
     * @return HasMany<ReviewReport, $this>
     */
    public function reviewReports(): HasMany
    {
        return $this->hasMany(ReviewReport::class);
    }

    /**
     * @return HasMany<ContactMessage, $this>
     */
    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    /**
     * @return HasMany<BadgeRequest, $this>
     */
    public function badgeRequests(): HasMany
    {
        return $this->hasMany(BadgeRequest::class);
    }

    /**
     * Name shown under public reviews: first name and initial of the last name ("Camille D.").
     */
    public function publicName(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $first = array_shift($parts) ?? '';
        $last = array_pop($parts);

        return $last ? $first.' '.Str::upper(mb_substr($last, 0, 1)).'.' : $first;
    }

    /**
     * GDPR erasure: deletes the account with its dogs, reviews (ratings of the reviewed listings are
     * recomputed), claims and badge requests (and their private proof files), contact messages, sessions
     * and password reset tokens. Review reports are kept for moderation but anonymised (user_id set to
     * null by the foreign key). Listings the user managed lose their owner and the verification badge
     * that was tied to this account.
     */
    public function purge(): void
    {
        DB::transaction(function () {
            $this->claimedProviders()->each(fn (Provider $provider) => $provider->forceFill([
                'verification_level' => VerificationLevel::None,
                'plan' => 'free',
            ])->save());
            $this->claims()->each(fn (Claim $claim) => $claim->delete());
            $this->badgeRequests()->each(fn (BadgeRequest $badgeRequest) => $badgeRequest->delete());

            $reviewedProviderIds = $this->reviews()->distinct()->pluck('provider_id');
            $this->reviews()->delete();

            $this->contactMessages()->delete();
            DB::table('sessions')->where('user_id', $this->id)->delete();
            DB::table('password_reset_tokens')->whereIn('email', array_filter([$this->email, $this->pending_email]))->delete();
            $this->delete();

            Provider::withTrashed()->whereIn('id', $reviewedProviderIds)->each(fn (Provider $provider) => $provider->refreshRating());
        });
    }
}
