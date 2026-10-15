<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Audit log entry of an admin action. Write it with AdminAction::record().
 */
class AdminAction extends Model
{
    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    /**
     * Logs an action of the signed-in admin on a record, e.g. record('claim.approve', $claim, 'BCE checked').
     */
    public static function record(string $action, Model $subject, ?string $details = null): self
    {
        $entry = new self;
        $entry->forceFill([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => Str::snake(class_basename($subject)),
            'subject_id' => $subject->getKey(),
            'details' => $details,
            'created_at' => now(),
        ])->save();

        return $entry;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
