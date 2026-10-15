<?php

namespace App\Models;

use App\Enums\ClaimAction;
use App\Enums\ClaimStatus;
use Database\Factories\ClaimFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * proof_path points to the private "local" disk (storage/app/private), never to the public disk.
 */
#[Fillable(['bce_number', 'message', 'action'])]
#[Hidden(['proof_path'])]
class Claim extends Model
{
    /** @use HasFactory<ClaimFactory> */
    use HasFactory;

    /**
     * Deleting a claim (one by one, through Eloquent) also deletes its private proof file, once the
     * surrounding transaction (if any) is committed.
     */
    protected static function booted(): void
    {
        static::deleting(function (Claim $claim): void {
            if ($claim->proof_path) {
                $path = $claim->proof_path;
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
            'action' => ClaimAction::class,
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
