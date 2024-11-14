<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      "id" => $this->id,
      "user_id" => $this->user_id,
      "recipient_id" => $this->recipient_id,
      'messages' => $this->messages, // Nested Message Resource
      // 'messages' => new MessageResource($this->whenLoaded('messages')), // Nested Messages Resource


    ];
  }
}
