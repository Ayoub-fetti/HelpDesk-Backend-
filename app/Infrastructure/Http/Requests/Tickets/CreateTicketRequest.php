<?php

namespace App\Infrastructure\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|string|in:low,average,high,urgent',
            'category_id' => 'required|integer|exists:categories,id',
            'technician_id' => 'nullable|integer|exists:users,id',
            'technician_last_name' => 'nullable|string|max:255',
            'technician_first_name' => 'nullable|string|max:255',
            'technician_email' => 'nullable|email',
            'technician_phone' => 'nullable|string|max:20',
            'attachments.*' => 'nullable|file|max:10240',
        ];
    }
}