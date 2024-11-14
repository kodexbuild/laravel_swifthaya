<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_profile extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'first_name',
    'last_name',
    'profile_picture',
    'bio',
    'location',
    'phone_number',
    'website',
  ];
  // mutators
  public function setFirstNameAttribute($value)
  {
    $this->attributes['first_name'] = strtolower($value);
  }

  public function setLastNameAttribute($value)
  {
    $this->attributes['last_name'] = strtolower($value);
  }
  public function setLocationAttribute($value)
  {
    $this->attributes['location'] = strtolower($value);
  }
  public function setWebsiteAttribute($value)
  {
    $this->attributes['website'] = strtolower($value);
  }
  // relations
  public function user()
  {
    return $this->belongsTo(User::class);
  }
  public function talentprofile()
  {
    return $this->hasOne(Talent_profile::class);
  }
  public function companyprofile()
  {
    return $this->hasOne(Company_profile::class);
  }
  public function individual()
  {
    return $this->hasOne(Individual::class);
  }
  public function applications()
  {
    return $this->hasMany(Application::class);
  }

  public function getImgUrl()
  {
    if ($this->profile_picture) {
      return url('storage/' . $this->profile_picture);
    }
    return;
  }
}
