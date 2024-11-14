<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompany_profileRequest extends FormRequest
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
      'company_name' => 'nullable|string|max:255',
      'industry' => 'nullable|string|max:255',
      'company_size' => 'nullable|integer|min:1',
      'founded_year' => 'nullable|integer|digits:4|min:1800|max:' . date('Y'),
    ];
  }
}
