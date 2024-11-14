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
      'first_name' => ['required', 'string', 'max:255'],
      'last_name' => ['required', 'string', 'max:255'],
      'email' => ['required', 'email', 'unique:users'],
      'password' => [
        'required',
        'confirmed',
        Rules\Password::defaults()
      ],
      'location' => 'required|string|max:255',
      'phone_number' => ['nullable', 'regex:/^\+?[0-9\s\-\(\)]+$/'],  // Allows numbers, spaces, dashes, parentheses, and an optional leading '+'
      'bio' => 'nullable|string',
      'website' => 'nullable|url|max:255',
      // company details
      'company_name' => 'required|string|max:255',
      'industry' => 'required|string|max:255',
      'company_size' => 'required|integer|min:1',
      'founded_year' => 'required|integer|digits:4|min:1800|max:' . date('Y'),
    ];
  }
}
