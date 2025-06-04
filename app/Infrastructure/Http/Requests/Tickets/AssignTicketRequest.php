<?php

namespace App\Infrastructure\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class AssignTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization will be handled by the domain service
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'technician_id' => 'required|integer|exists:users,id',
            'technician_last_name' => 'required|string|max:255',
            'technician_first_name' => 'required|string|max:255',
            'technician_email' => 'required|email',
            'technician_phone' => 'nullable|string|max:20',
            'comment' => 'nullable|string',
        ];
    }
}