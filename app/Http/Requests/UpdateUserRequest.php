<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class UpdateUserRequest extends FormRequest
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
      'first_name' => ['nullable', 'string', 'min:3', 'max:20'],
      'last_name' => ['nullable', 'string', 'min:3', 'max:20'],
      'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
      'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
      'bio' => 'nullable|string',
      'location' => 'nullable|string|max:255',
      'phone_number' => 'nullable|string|max:20',
      'website' => 'nullable|url|max:255',
    ];
  }
}
