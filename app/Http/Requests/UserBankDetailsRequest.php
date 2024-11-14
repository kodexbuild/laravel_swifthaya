<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserBankDetailsRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'account_number' => 'required|string|digits_between:10,12|regex:/^\d+$/',
      // 'bank_code' => 'required|string|size:3|exists:banks,code',
      'bank_name' => 'required|string|max:50|exists:banks,name',
    ];
  }
}
