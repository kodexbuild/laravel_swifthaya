<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
      'poster_id' => $this->poster_id,
      'title' => $this->title,
      'description' => $this->description,
      'required_skills' => json_decode($this->required_skills),
      'budget' => $this->budget,
      'duration' => $this->duration,
      'posted_at' => $this->posted_at->toDateTimeString(),
      'deadline_date' => (is_null($this->deadline_date)) ? $this->deadline_date : $this->deadline_date->toDateTimeString(),
      'status' => $this->status,
    ];
  }
}
