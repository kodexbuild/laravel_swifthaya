<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'user_type',
    'last_login_at'
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      // 'password' => 'hashed',
    ];
  }

  public function setEmailAttribute($value)
  {
    $this->attributes['email'] = strtolower($value);
  }

  public function setUserTypeAttribute($value)
  {
    $this->attributes['user_type'] = strtolower($value);
  }

  // relationships
  public function userprofile()
  {
    return $this->hasOne(User_profile::class);
  }
  public function talentprofile()
  {
    return $this->hasOne(Talent_Profile::class);
  }
  public function companyprofile()
  {
    return $this->hasOne(Company_profile::class);
  }
  public function individual()
  {
    return $this->hasOne(Individual::class);
  }
  public function project()
  {
    return $this->hasMany(Project::class, "poster_id");
  }
  public function applications()
  {
    return $this->hasMany(Application::class, 'applicant_id');
  }
  public function swifthayajob()
  {
    return $this->hasMany(SwifthayaJob::class, 'company_id');
  }
  public function sentMessages()
  {
    return $this->hasMany(Message::class, 'sender_id');
  }

  public function receivedMessages()
  {
    return $this->hasMany(Message::class, 'recipient_id');
  }
  public function payment()
  {
    return $this->hasMany(Payment::class, 'user_id');
  }
  public function reviewer()
  {
    return $this->belongsTo(Review::class, 'reviewer_id');
  }
  public function reviewee()
  {
    return $this->belongsTo(Review::class, 'reviewee_id');
  }
  public function user_bank_details()
  {
    return $this->hasOne(UserBankDetail::class, "user_id");
  }
  public function transfer_recipient()
  {
    return $this->hasOne(Recipient::class, "user_bank_details_id");
  }
}
