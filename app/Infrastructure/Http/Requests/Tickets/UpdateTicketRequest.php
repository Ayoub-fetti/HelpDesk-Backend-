<?php

namespace App\Infrastructure\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
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
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|string|in:low,average,high,urgent',
            'category_id' => 'sometimes|integer|exists:categories,id',
            'attachments.*' => 'sometimes|file|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'The ticket title cannot exceed 255 characters.',
            'priority.in' => 'Priority must be one of: low, average, high, urgent.',
            'category_id.exists' => 'The selected category does not exist.',
        ];
    }
}
