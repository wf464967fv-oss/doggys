<?php

namespace App\Models;

use App\Enums\ClaimStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Request for the blue "licensed" badge. proof_path points to the private "local" disk.
 * Status values are shared with claims (pending, approved, rejected).
 */
#[Fillable(['regulatory_number', 'message'])]
#[Hidden(['proof_path'])]
class BadgeRequest extends Model
{
    /**
     * Deleting a request (through Eloquent) also deletes its private proof file once committed.
     */
    protected static function booted(): void
    {
        static::deleting(function (BadgeRequest $badgeRequest): void {
            if ($badgeRequest->proof_path) {
                $path = $badgeRequest->proof_path;
                DB::afterCommit(fn () => Storage::disk('local')->delete($path));
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ClaimStatus::class,
            'handled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Provider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
