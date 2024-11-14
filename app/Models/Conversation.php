<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
  use HasFactory;
  protected $fillable = [
    'user_id',
    'recipient_id',
  ];

  // Relationship with User (The sender of the conversation)
  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  // Relationship with User (The recipient of the conversation)
  public function recipient()
  {
    return $this->belongsTo(User::class, 'recipient_id');
  }

  // Relationship with Message (All messages in this conversation)
  public function messages()
  {
    return $this->hasMany(Message::class, "conversation_id");
  }
  public function latestMessages()
  {
    return $this->messages();
  }
}
