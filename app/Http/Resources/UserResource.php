<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'office_id' => $this->office_id,
            'name' => $this->name,
            'email' => $this->email,
            'office' => $this->whenLoaded('office', fn () => [
                'id' => $this->office?->id,
                'name' => $this->office?->name,
                'slug' => $this->office?->slug,
            ]),
        ];
    }
}
