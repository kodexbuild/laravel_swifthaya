<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
  use HasFactory;

  protected $fillable = [
    'applicant_id',
    'swifthayajob_id',
    'project_id',
    'applied_at',
    'status',
  ];

  public function user()
  {
    return $this->belongsTo(User::class, 'applicant_id');
  }

  public function swifthayajob()
  {
    return $this->belongsTo(Swifthayajob::class, "swifthayajob_id");
  }
  public function project()
  {
    return $this->belongsTo(Project::class, "project_id");
  }

  public function company()
  {
    return $this->belongsTo(Project::class, "poster_id");
  }
  // public function individual()
  // {
  //   return $this->belongsTo(Project::class, "poster_id");
  // }

  public function userprofile()
  {
    return $this->belongsTo(User_profile::class, "applicant_id");
  }

  public function getAttachments()
  {
    if ($this->attachments) {
      return url('storage/' . $this->attachments);
    }
    return;
  }


  /**
   * Get the status color based on the current status.
   *
   * @return string
   */
  public function getStatusColorAttribute()
  {
    switch ($this->status) {
      case 'accepted':
        return 'green';
      case 'rejected':
        return 'red';
      case 'pending':
      default:
        return 'yellow';
    }
  }
}
