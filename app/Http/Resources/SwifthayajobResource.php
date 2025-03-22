<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SwifthayajobResource extends JsonResource
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
      'employer_id' => $this->employer_id,
      'title' => $this->title,
      'location' => $this->location,
      'salary' => $this->salary_amount ? [
        'amount' => $this->salary_amount,
        'period' => $this->salary_period,
      ] : null,
      'job_summary' => $this->job_summary,
      'responsibilities' => $this->responsibilities,
      'requirements' => $this->requirements,
      'qualifications' => $this->qualifications,
      'experience_level' => $this->experience_level,
      'job_type' => $this->job_type,
      'job_status' => $this->job_status,
      'posted_at' => $this->posted_at,
      'status' => $this->status,
    ];
  }
}
