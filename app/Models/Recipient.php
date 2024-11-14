<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
  use HasFactory;
  protected $fillable = [
    "user_bank_details_id",
    "code"
  ];
  public function bank_details(){
    $this->belongsTo(User::class, "user_bank_details_id");
  }
}
