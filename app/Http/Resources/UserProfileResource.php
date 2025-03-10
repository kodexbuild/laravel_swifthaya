<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
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
      'user_id' => $this->user_id,
      'first_name' => $this->first_name,
      'last_name' => $this->last_name,
      'profile_picture' => $this->profile_picture,
      'city' => $this->city,
      'state' => $this->state,
      'phone_number' => $this->phone_number,
    ];
  }
}
