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
      'company_id' => $this->company_id,
      'title' => $this->title,
      'description' => $this->description,
      'required_skills' => $this->required_skills,
      'location' => $this->location,
      'salary_min' => $this->salary_min,
      'salary_max' => $this->salary_max,
      'job_type' => $this->job_type,
      'posted_at' => $this->posted_at,
      'deadline_date' => (is_null($this->deadline_date)) ? $this->deadline_date : $this->deadline_date->toDateTimeString(),
      'status' => $this->status,
    ];
  }
}
