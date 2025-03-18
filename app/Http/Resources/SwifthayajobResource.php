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
<<<<<<< HEAD
      'description' => $this->description,
      'required_skills' => $this->required_skills,
      'location' => $this->location,
      'salary_min' => $this->salary_min,
      'salary_max' => $this->salary_max,
      'job_type' => $this->job_type,
      'posted_at' => $this->posted_at,
      'deadline_date' => (is_null($this->deadline_date)) ? $this->deadline_date : $this->deadline_date->toDateTimeString(),
=======
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
>>>>>>> d8307de (Recovering lost project)
      'status' => $this->status,
    ];
  }
}
