<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Models\User_profile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReviewResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    if ($this->reviewer_id === Auth::user()->id) {

      return [
        "id" => $this->id,
        "reviewer_id" => $this->reviewer_id,
        "reviewee_id" => $this->reviewee_id,
        "rating" => $this->rating,
        "comment" => $this->comment,
        'status' => $this->status,
        'reviewee' => new UserResource($this->reviewee),  // Including user 

      ];
    };
    if ($this->reviewee_id === Auth::user()->id) {

      return [
        "id" => $this->id,
        "reviewer_id" => $this->reviewer_id,
        "reviewee_id" => $this->reviewee_id,
        "rating" => $this->rating,
        "comment" => $this->comment,
        'status' => $this->status,
        'reviewer' => new UserResource($this->reviewer),  // Including user 

      ];
    };
    return [
      "id" => $this->id,
      "reviewer_id" => $this->reviewer_id,
      "reviewee_id" => $this->reviewee_id,
      "rating" => $this->rating,
      "comment" => $this->comment,
      'status' => $this->status,
    ];
  }
}
