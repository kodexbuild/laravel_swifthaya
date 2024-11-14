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
      'email' => $this->email,
      'user_type' => $this->user_type,
      'user_status' => $this->user_status,
      'status' => $this->status,
      // 'last_login_at' => $this->last_login_at,
      'created_at' => $this->created_at->toDateTimeString(),
      
      'user_profile' => new UserProfileResource($this->userprofile),  // Including user 

    ];
  }
}
