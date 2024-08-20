<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'birthday' => $this->birthday,
            'admin' => $this->is_admin,
            'bonus' => $this->bonus,
            'avatar' => $this->image,
            'email_verified' => $this->whenNotNull($this->email_verified_at),
            'token' => $this->whenNotNull($this->token),
            'favourites_count' => $this->whenNotNull($this->favourites_count),
            'progress_count' => $this->whenNotNull($this->onread_count),
            'completed_count' => $this->whenNotNull($this->finishedbooks_count),
            'progress' => $this->whenNotNull($this->onread),
        ];
    }
}
