<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company_profile extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_profile_id',
    'company_name',
    'company_logo',
    'company_slogan',
    'company_website',
    'industry',
    'company_size',
    'founded_year',
    'linkedin_url',
    'github_url',
    'twitter_url',
    'instagram_url',
  ];


  // public function setCompanyNameAttribute($value)
  // {
  //   // Convert to cents (assuming $value is in dollars)
  //   $this->attributes['company_name'] = strtolower($value);
  // }

  public function setIndustryAttribute($value)
  {
    $this->attributes['industry'] = ucwords(strtolower($value));
  }
  public function setCompanySizeAttribute($value)
  {
    $this->attributes['company_size'] = intval($value);
  }
  public function setFoundedYearAttribute($value)
  {
    $this->attributes['founded_year'] = intval($value);
  }


  // relations
  public function userprofile()
  {
    return $this->belongsTo(User_profile::class, 'user_profile_id', 'id');
  }
  public function user()
  {
    return $this->belongsTo(User::class);
  }
  public function swifthayajob()
  {
    return $this->hasMany(SwifthayaJob::class, 'company_id');
  }
}
