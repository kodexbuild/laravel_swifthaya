<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
  use HasFactory;
  protected $fillable = [
    'poster_id',
    'title',
    'description',
    'required_skills',
    'budget',
    'duration',
    'deadline_date',
  ];

  protected $casts = [
    'required_skills' => 'array',
    'posted_at' => 'datetime',
    'deadline_date' => 'datetime',
  ];
  public function setRequiredSkillsAttribute($value)
  {
    // Ensure $value is an array and convert each skill to lowercase
    // Log::info('Encoded required skills:', ['value' => $value]);
    if (is_array($value)) {
      $value = array_map('strtolower', $value); // Convert each skill to lowercase

    }

    // Encode the array as JSON and store it
    $this->attributes['required_skills'] = json_encode($value);
  }
  // Optionally, create an accessor to decode the JSON back to an array when retrieving
  public function getRequiredSkillsAttribute($value)
  {
    return json_decode($value, true);  // Decode JSON back to an array
  }

  public function setTitleAttribute($value)
  {
    $this->attributes['title'] = strtolower($value);
  }

  public function setBudgetAttribute($value)
  {
    // Convert to kobo 
    $this->attributes['budget'] = intval($value * 100);
  }
  public function getBudgetAttribute($value)
  {
    // Convert to back to kobo 
    $this->attributes['budget'] = intval($value / 100);
  }


  // relations
  public function user()
  {
    return $this->belongsTo(User::class, "poster_id");
  }
  public function application()
  {
    return $this->hasMany(Application::class, "project_id");
  }
}
