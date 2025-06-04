<?php

namespace App\Infrastructure\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class CloseTicketRequest extends FormRequest
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
            'solution' => 'nullable|string',
            'comment' => 'nullable|string',
        ];
    }

    /**
     * Get custom validation rules for the request.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // If the ticket is not yet resolved, a solution is required
            $ticket = request()->route('id');
            $ticketRepo = app(\App\Domains\Tickets\Repositories\TicketRepositoryInterface::class);
            $ticketEntity = $ticketRepo->findById($ticket);
            
            if ($ticketEntity && 
                $ticketEntity->getStatut()->toString() !== 'resolved' && 
                empty($this->solution)) {
                $validator->errors()->add(
                    'solution', 'A solution is required when closing an unresolved ticket.'
                );
            }
        });
    }
}