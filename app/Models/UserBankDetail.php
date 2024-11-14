<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class UserBankDetail extends Model
{
  use HasFactory;
  protected $fillable = [
    "user_id",
    "account_name",
    "account_number",
    "bank_code",
    "bank_name"
  ];


  public function setAccountNumberAttribute($value)
  {
    // Encrypt account number before saving

    $this->attributes['account_number'] = Crypt::encryptString($value);
  }
  // Optionally, create an accessor to decode the JSON back to an array when retrieving
  public function getAccountNumberAttribute($value)
  {
    // To decrypt when retrieving
   return Crypt::decryptString($value);
  }


  public function user()
  {
    return $this->belongsTo(User::class, "user_id");
  }
}
