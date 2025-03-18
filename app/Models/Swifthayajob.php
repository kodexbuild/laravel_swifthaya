<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Swifthayajob extends Model
{
  use HasFactory;
  protected $fillable = [
    'company_id',
    'title',

    'location',
    'job_type',
    'salary_amount',
    'salary_period',
    'job_summary',
    'responsibilities',
    'requirements',
    'qualifications',
    'experience_level',
    'deadline_date',
  ];

  protected $casts = [
    'required_skills' => 'array',
    'posted_at' => 'datetime',
    'deadline_date' => 'datetime',
  ];

  // Mutator for 'required_skills'
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

  // Mutator for 'title' to ensure it's always lowercase
  public function setTitleAttribute($value)
  {
    $this->attributes['title'] = strtolower($value);
  }

  // Mutator for 'location' to ensure it's always lowercase
  public function setLocationAttribute($value)
  {
    $this->attributes['location'] = strtolower($value);
  }

  // Mutator for 'salary_min' to convert naira to kobo
  public function setSalaryMinAttribute($value)
  {
    // Convert to kobo 
    $this->attributes['salary_min'] = intval($value * 100);
  }

  // Mutator for 'salary_max' to convert naira to kobo
  public function setSalaryMaxAttribute($value)
  {
    // Convert to kobo 
    $this->attributes['salary_max'] = intval($value * 100);
  }

  // Mutator for 'salary_min' to convert kobo back to naira
  public function getSalaryMinAttribute($value)
  {
    // Convert to kobo 
    $this->attributes['salary_min'] = intval($value / 100);
  }

  // Mutator for 'salary_max' to convert kobo back to nairas
  public function getSalaryMaxAttribute($value)
  {
    // Convert to kobo 
    $this->attributes['salary_max'] = intval($value / 100);
  }

  public function setJobTypeAttribute($value)
  {
    $this->attributes['job_type'] = strtolower($value);
  }
  // public function companyprofile()
  // {
  //   return $this->belongsTo(Company_profile::class, 'company_id');
  // }
  public function user()
  {
    return $this->belongsTo(User::class, 'company_id');
  }
  public function application()
  {
    return $this->hasMany(Application::class, "swifthayajob_id");
  }
}
