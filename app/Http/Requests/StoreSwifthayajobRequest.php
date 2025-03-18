<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSwifthayajobRequest extends FormRequest
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
      'title' => 'required|string|max:255',
      'location' => 'nullable|string|max:255',
      'salary_amount' => 'nullable|numeric|min:0|max:99999999.99',
      'salary_period' => 'nullable|string|in:daily,weekly,monthly,annually,per_project',
      'job_summary' => 'nullable|string',
      'responsibilities' => 'nullable|string',
      'requirements' => 'nullable|string',
      'qualifications' => 'nullable|string',
      'experience_level' => 'required|string|in:entry,junior,intermediate,senior,lead',
      'job_type' => 'required|string|in:full_time,part_time,contract,internship',

    ];
  }
  public function messages()
  {
    return [
      "salary_min.min" => "The minimum salary must be at least ₦1",
      "salary_max.min" => "The maximum salary must be at least ₦1"
    ];
  }
}
