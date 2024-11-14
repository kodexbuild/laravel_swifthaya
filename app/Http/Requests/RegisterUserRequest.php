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
      'user_type' => ['required', 'in:company,individual,talent,admin'],
      'location' => 'required|string|max:255',
      'phone_number' => ['nullable', 'regex:/^\+?[0-9\s\-\(\)]+$/'],  // Allows numbers, spaces, dashes, parentheses, and an optional leading '+'
      'bio' => 'nullable|string',
      'website' => 'nullable|url|max:255',
    ];
  }
}
