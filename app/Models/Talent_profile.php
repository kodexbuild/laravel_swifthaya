<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Talent_profile extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_profile_id',
    'skills',
    'experience',
    'education',
    'portfolio',
  ];

  protected $casts = [
    'skills' => 'array', // Cast skills as an array
    'experience' => 'array', // Cast experience as an array
    'education' => 'array', // Cast education as an array
    'portfolio' => 'array',
  ];
  public function setRequiredSkillsAttribute($value)
  {
    // Ensure $value is an array and convert each skill to lowercase
    // Log::info('Encoded required skills:', ['value' => $value]);
    if (is_array($value)) {
      $value = array_map('strtolower', $value); // Convert each skill to lowercase

    }

    // Encode the array as JSON and store it
    $this->attributes['skills'] = json_encode($value);
  }
  // Optionally, create an accessor to decode the JSON back to an array when retrieving
  public function getRequiredSkillsAttribute($value)
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
