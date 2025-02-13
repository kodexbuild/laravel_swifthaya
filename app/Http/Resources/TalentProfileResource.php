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
      'professional_bio' => $this->professional_bio,
      'tech_skills' => $this->tech_skills,
      'soft_skills' => $this->soft_skills,
      'linkedin_url' => $this->linkedin_url,
      'github_url' => $this->github_url,
      'twitter_url' => $this->twitter_url,
      'portfolio_url' => $this->portfolio_url,
      'resume' => $this->resume,
      'status' => $this->status,
      'user' => new UserResource($this->userprofile->user),  // Including user 
    ];
  }
}
