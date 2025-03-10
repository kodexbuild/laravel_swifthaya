<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class StoreCompany_profileRequest extends FormRequest
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
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'email' => $this->getMethod() == "PATCH" ? ['required', 'email'] : ['required', 'email', 'unique:users'],
      'password' => $this->getMethod() == "PATCH" ? [
        'required',
        'confirmed',
        Rules\Password::defaults()
      ] : "",
      'company_email' => ['required', 'email'],
      'city' => ['required', 'string', 'max:255'],
      'state' => ['required', 'string', 'max:255'],
      'bio' => ['nullable', 'string', 'max:255'],
      'phone_number' => ['required', 'string', 'regex:/^(070|080|081|090|091)[0-9]{7,8}$/'], // Must be a Nigerian phone number
            
      // comp details
      'company_phone_number' => ['required', 'string', 'regex:/^(070|080|081|090|091)[0-9]{7,8}$/'], // Must be a Nigerian phone number      
      'company_name' => ['required', 'string', 'max:255'],
      'company_size' => ['nullable', 'string', 'max:255'],
      'founded_year' => ['nullable', 'string', 'max:255'],
      'company_website' => ['required', 'url', 'max:255'],
      'company_city' => ['required', 'string', 'max:255'],
      'company_state' => ['required', 'string', 'max:255'],
      'industry' => ['required', 'string', 'max:255'],
      'founded_year' => 'nullable|integer|digits:4|min:1800|max:' . date('Y'),
    ];
  }
}
