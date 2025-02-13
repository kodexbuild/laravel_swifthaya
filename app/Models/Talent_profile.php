<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Talent_profile extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_profile_id',
    'job_title',
    'professional_bio',
    'tech_skills',
    'soft_skills',
    'linkedin_url',
    'github_url',
    'twitter_url',
    'portfolio_url',
    'resume',
    'status',
  ];

  protected $casts = [
    'tech_skills' => 'array',
    'soft_skills' => 'array',
  ];
  public function setTechSkillsAttribute($value)
  {
    // Ensure $value is an array and convert each skill to lowercase
    // Log::info('Encoded required skills:', ['value' => $value]);
    if (is_array($value)) {
      $value = array_map('strtolower', $value); // Convert each skill to lowercase

    }
    // Encode the array as JSON and store it
    $this->attributes['tech_skills'] = json_encode($value);
  }
  public function setSoftSkillsAttribute($value)
  {
    // Ensure $value is an array and convert each skill to lowercase
    // Log::info('Encoded required skills:', ['value' => $value]);
    if (is_array($value)) {
      $value = array_map('strtolower', $value); // Convert each skill to lowercase

    }
    // Encode the array as JSON and store it
    $this->attributes['soft_skills'] = json_encode($value);
  }
  // Optionally, create an accessor to decode the JSON back to an array when retrieving
  public function getTechSkillsAttribute($value)
  {
    return json_decode($value, true);  // Decode JSON back to an array
  }
  public function getSoftSkillsAttribute($value)
  {
    return json_decode($value, true);  // Decode JSON back to an array
  }

  public function userprofile()
  {
    return $this->belongsTo(User_profile::class, 'user_profile_id', 'id');
  }
  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
