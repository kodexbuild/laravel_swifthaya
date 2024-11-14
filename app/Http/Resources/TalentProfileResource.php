<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TalentProfileResource extends JsonResource
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
      'user_profile_id' => $this->user_profile_id,
      'skills' => json_decode($this->skills),
      'experience' => json_decode($this->experience),
      'education' => json_decode($this->education),
      'portfolio' => json_decode($this->portfolio),
      'status' => $this->status,
      'user' => new UserResource($this->userprofile->user),  // Including user 
    ];
  }
}
