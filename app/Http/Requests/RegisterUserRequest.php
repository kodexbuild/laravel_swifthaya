<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterUserRequest extends FormRequest
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
      'first_name' => ['required', 'string', 'max:255'],
      'last_name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'email', 'unique:users'],
      'password' => [
        'required',
        'confirmed',
        Rules\Password::defaults()
      ],
      'street_address' => ['nullable', 'string', 'max:255'],
      'city' => ['nullable', 'string', 'max:255'],
      'state' => ['nullable', 'string', 'max:255'],
      'phone_number' => ['required', 'string', 'regex:/^(070|080|081|090|091)[0-9]{7,8}$/'], // Must be a Nigerian phone number      
      'company_name' => ['sometimes', 'string', 'max:255'],
      'company_website' => ['sometimes', 'url', 'max:255'],
      'industry' => ['sometimes', 'string', 'max:255'],
    ];
  }
  public function messages()
  {
    return [
      'phone_number.required' => 'The phone number field is required.',
      'phone_number.regex' => 'The phone number must be a valid Nigerian phone number, with a valid prefix and a minmax of 11 digits.',
      'email.required' => 'The email field is required.',
      'email.email' => 'The email must be a valid email address.',
      'email.unique' => 'The email has already been taken.',
      'password.required' => 'The password field is required.',
      'password.confirmed' => 'The password confirmation does not match.',
      // Add other custom messages as needed
    ];
  }
}
