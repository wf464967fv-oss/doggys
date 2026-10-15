<?php

namespace App\Models;

use App\Enums\ProviderReportType;
use App\Enums\ReportStatus;
use Database\Factories\ProviderReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['email', 'type', 'message'])]
class ProviderReport extends Model
{
    /** @use HasFactory<ProviderReportFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ProviderReportType::class,
            'status' => ReportStatus::class,
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
}
