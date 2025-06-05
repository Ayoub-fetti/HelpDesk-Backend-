<?php

namespace App\Infrastructure\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;
use App\Domains\Tickets\ValueObjects\StatutTicket;

class ChangeStatusRequest extends FormRequest
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
            'statut' => [
                'required', 
                'string',
                function ($attribute, $value, $fail) {
                    try {
                        StatutTicket::fromString($value);
                    } catch (\InvalidArgumentException $e) {
                        $fail('The selected status is invalid.');
                    }
                },
            ],
            'comment' => 'nullable|string',
        ];
    }
}