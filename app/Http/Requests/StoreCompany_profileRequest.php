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
      'email' => $this->getMethod() == "PATCH" ? ['required', 'email'] : ['required', 'email', 'unique:users'],
      'password' => [
        'required',
        'confirmed',
        Rules\Password::defaults()
      ],
      'street_address' => ['nullable', 'string', 'max:255'],
      'city' => ['nullable', 'string', 'max:255'],
      'state' => ['nullable', 'string', 'max:255'],
      'bio' => ['nullable', 'string', 'max:255'],
      'phone_number' => ['required', 'string', 'regex:/^(070|080|081|090|091)[0-9]{7,8}$/'], // Must be a Nigerian phone number      
      'company_name' => ['required', 'string', 'max:255'],
      'company_size' => ['nullable', 'string', 'max:255'],
      'founded_year' => ['nullable', 'string', 'max:255'],
      'company_slogan' => ['nullable', 'string', 'max:255'],
      'company_website' => ['required', 'url', 'max:255'],
      'industry' => ['required', 'string', 'max:255'],
      'founded_year' => 'nullable|integer|digits:4|min:1800|max:' . date('Y'),
      'linkedin_url' => 'nullable|url|max:255',
      'github_url' => 'nullable|url|max:255',
      'instagram_url' => 'nullable|url|max:255',
      'twitter_url' => 'nullable|url|max:255',
    ];
  }
}
