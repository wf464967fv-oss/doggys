<?php

namespace App\Models;

use App\Enums\DogSex;
use Database\Factories\DogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name', 'breed', 'sex', 'birth_date', 'microchip_number', 'is_neutered', 'weight_kg', 'vaccinations', 'health_notes',
])]
class Dog extends Model
{
    /** @use HasFactory<DogFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sex' => DogSex::class,
            'birth_date' => 'date',
            'is_neutered' => 'boolean',
            'weight_kg' => 'decimal:2',
            'vaccinations' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
