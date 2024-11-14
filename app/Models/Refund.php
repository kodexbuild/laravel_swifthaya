<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
  use HasFactory;

  protected $fillable = [
    "payment_id",
    "amount",
    "refund_type",
    "status",
    "refund_reference"
  ];
  public function payment(){
    $this->belongsTo(Payment::class, "payment_id");
  }
}
