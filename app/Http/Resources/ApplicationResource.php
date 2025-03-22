<?php

namespace App\Http\Resources;

use App\Models\Swifthayajob;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request)
  {
    // if ($this->project_id) {
    //   return [
    //     'id' => $this->id,
    //     'applicant_id' => $this->applicant_id,
    //     'swifthayajob_id' => $this->swifthayajob_id,
    //     'applied_at' => $this->applied_at,
    //     'status' => $this->status,
    //     // Here we include the job/project details
    //     'applicant' => new UserResource($this->whenLoaded('user')), // Nested Job Resource
    //   ];
    // }
    // if ($this->swifthayajob_id) {
      return [
        'id' => $this->id,
        'applicant_id' => $this->applicant_id,
        'swifthayajob_id' => $this->swifthayajob_id,
        'applied_at' => $this->applied_at,
        'status' => $this->status,
        // Here we include the job/project details
        'job' => new SwifthayajobResource($this->whenLoaded('swifthayajob')), // Nested Job Resource
        'applicant' => new UserResource($this->whenLoaded('user')), // Nested Job Resource
      ];
    // }
  }
}
