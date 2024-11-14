<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
      'required_skills' => 'nullable|array',
      'budget' => 'required|numeric|min:0',
      'duration' => ['required', 'integer', 'min:1'],  // Ensure duration is a positive integer and at least 1 hour
      'deadline_date' => 'nullable|date|after_or_equal:posted_at',
    ];
  }
  public function messages()
  {
    return [
      'duration.required' => 'The duration in hours is required.',
      'duration.integer' => 'The duration must be a valid integer representing the number of hours.',
      'duration.min' => 'The duration must be at least 1 hour.',
    ];
  }
}
