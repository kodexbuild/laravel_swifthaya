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
      'company_logo' => $this->userprofile->profile_picture,
      'company_name' => $this->company_name,
      'company_email' => $this->company_email,
      'company_phone_number' => $this->company_phone_number,
      'company_size' => $this->company_size,
      'company_website' => $this->company_website,
      'company_city' => $this->city,
      'company_state' => $this->state,
      'industry' =>  $this->industry,
      'founded_year' =>  $this->founded_year,
      'status' => $this->status,
      'user' => new UserResource($this->userprofile->user),  // Including user 
      // // 'user_details' => new UserResource($this->whenLoaded('user')), 


    ];
  }
}
