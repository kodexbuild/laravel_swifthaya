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
      'job_title' => $this->job_title,
      'bio' => $this->userprofile->bio,
      'profile_picture' => $this->userprofile->profile_picture,
      'tech_skills' => $this->tech_skills,
      'soft_skills' => $this->soft_skills,
      'linkedin_url' => $this->userprofile->linkedin_url,
      'github_url' => $this->userprofile->github_url,
      'twitter_url' => $this->userprofile->twitter_url,
      'portfolio_url' => $this->userprofile->portfolio_url,
      'resume' => $this->resume,
      'status' => $this->status,
      'user' => new UserResource($this->userprofile->user),  // Including user 
    ];
  }
}
