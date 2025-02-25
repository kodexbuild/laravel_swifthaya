<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProfileResource extends JsonResource
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
      "bio" => $this->userprofile->bio,
      "street_address"=> $this->userprofile->street_address,
      "city"=>  $this->userprofile->city,
      "state"=>  $this->userprofile->state,
      "phone_number"=>  $this->userprofile->phone_number,
      // 'company_logo' => $this->company_logo,
      'company_name' => $this->company_name,
      'company_slogan' => $this->company_slogan,
      'industry' =>  $this->industry,
      'company_size' => $this->company_size,
      'company_website' => $this->company_website,
      'founded_year' =>  $this->founded_year,
      'linkedin_url' => $this->linkedin_url,
      'github_url' => $this->github_url,
      'twitter_url' => $this->twitter_url,
      'instagram_url' => $this->instagram_url,
      'status' => $this->status,
      // 'user' => new UserResource($this->userprofile->user),  // Including user 
      // // 'user_details' => new UserResource($this->whenLoaded('user')), 


    ];
  }
}
