<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Provider
 */
class ProviderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'url' => localized_route('providers.show', $this->resource),
            'categories' => $this->categories->map(fn (Category $category) => [
                'slug' => $category->slug,
                'name' => $category->label(),
            ]),
            'address' => [
                'street' => $this->street,
                'house_number' => $this->house_number,
                'postal_code' => $this->postal_code,
                'city' => $this->city,
                'region' => $this->region?->value,
            ],
            'location' => ['lat' => (float) $this->lat, 'lng' => (float) $this->lng],
            'distance_km' => $this->whenHas('distance_km'),
            'is_mobile' => $this->is_mobile,
            'service_radius_km' => $this->service_radius_km,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->safeWebsite(),
            'opening_hours' => $this->opening_hours,
            'verification' => $this->verification_level->value,
            'rating' => $this->reviews_count > 0 ? ['average' => (float) $this->rating_avg, 'count' => $this->reviews_count] : null,
            'source' => $this->source->value,
        ];
    }
}
