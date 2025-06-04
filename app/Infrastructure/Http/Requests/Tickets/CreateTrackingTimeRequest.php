<?php

namespace App\Infrastructure\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class CreateTrackingTimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only technicians and administrators can add time entries
        $user = $this->user();
        return $user && ($user->user_type === 'technician' || $user->user_type === 'administrator');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:date_debut',
            'duration' => 'required|numeric|min:0.01',
            'description' => 'required|string',
        ];
    }
    
    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'The end date must be equal to or after the start date.',
            'duration.min' => 'The duration must be greater than zero.',
        ];
    }
}