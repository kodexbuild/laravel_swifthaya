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
      'description' => 'required|string',
      'job_type' => 'required|in:full-time,part-time,contract',
      'required_skills' => 'required|array',
      'location' => 'required|string|max:255',
      'salary_min' => ['required','numeric','min:1'],  // Ensure min salary is a positive number
      'salary_max' => ['required', 'numeric', 'min:1', 'gte:salary_min'],  // Max salary must be greater than or equal to min salary
      'deadline_date' => 'nullable|date|after_or_equal:posted_at',
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
