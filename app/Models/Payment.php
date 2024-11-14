<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
  use HasFactory;
  protected $fillable = [
    "user_id",
    "swifthayajob_id",
    "project_id",
    "payer_type",
    "net_amount",
    "amount",
    "refunded_amount",
    "balance",
    "platform_fee",
    "currency",
    "payment_status",
    "payment_date",
    "payment_reference",
  ];
  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }
  public function refund()
  {
    $this->hasMany(Refund::class, "payment_id");
  }
}
