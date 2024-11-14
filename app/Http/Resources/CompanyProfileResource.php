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
      'company_name' => $this->company_name,
      'industry' =>  $this->industry,
      'company_size' => $this->company_size,
      'founded_year' =>  $this->founded_year,
      'status' => $this->status,
      'user' => new UserResource($this->userprofile->user),  // Including user 
      // 'user_details' => new UserResource($this->whenLoaded('user')), 

    ];
  }
}
