<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class StoreTalent_profileRequest extends FormRequest
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
      'email' => ['required', 'email'],
      'city' => ['nullable', 'string', 'max:255'],
      'state' => ['nullable', 'string', 'max:255'],
      'phone_number' => ['required', 'string', 'regex:/^(070|080|081|090|091)[0-9]{7,8}$/'], // Must be a Nigerian phone number  
      'job_title' => 'required|string',
      'professional_bio' => 'required|string',
      'tech_skills' => 'required|array',
      'tech_skills.*' => 'string',
      'soft_skills' => 'required|array',
      'soft_skills.*' => 'string',
      'experience_level' => 'required|in:entry,mid_senior,senior',
      'linkedin_url' => 'nullable|string',
      'github_url' => 'nullable|string',
      'twitter_url' => 'nullable|string',
      'portfolio_url' => 'nullable|string',
    ];
  }
}
