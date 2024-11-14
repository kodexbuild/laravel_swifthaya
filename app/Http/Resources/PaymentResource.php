<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'user_id' => $this->user_id,
      'payer_type' => $this->payer_type,
      // 'swifthaya_id' =>$this->swifthaya_id,
      'net_amount' => $this->net_amount,
      'amount' => $this->amount,
      'platform_fee' => $this->platform_fee,
      'currency' => $this->currency,
      'payment_status' => $this->payment_status,
      'payment_method' => $this->payment_method,
      'payment_reference' => $this->payment_reference,
      'payment_date' => $this->payment_date,
    ];
  }
}
