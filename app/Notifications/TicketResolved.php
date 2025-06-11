<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;




class TicketResolved extends BaseTicketNotification
{
    protected $solution;

    public function __construct($ticket, $actionPerformedBy = null)
    {
        parent::__construct($ticket, $actionPerformedBy);
        $this->solution = $ticket->getSolution();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $performedBy = $this->actionPerformedBy 
            ? $this->actionPerformedBy->getFirstName() . ' ' . $this->actionPerformedBy->getLastName()
            : 'A technician';

        return (new MailMessage)
            ->subject('Ticket #' . $this->ticket->getId() . ' Has Been Resolved')
            ->greeting('Hello!')
            ->line("$performedBy has resolved ticket #" . $this->ticket->getId())
            ->line('Title: ' . $this->ticket->getTitle())
            ->line('Solution:')
            ->line($this->solution)
            ->action('View Ticket', url(config('app.frontend_url') . '/tickets/' . $this->ticket->getId()))
            ->line('Please let us know if this solution works for you.');
    }
    
    public function toArray(object $notifiable): array
    {
        return array_merge($this->getTicketData(), [
            'type' => 'ticket_resolved',
            'message' => 'Ticket has been resolved',
            'solution' => $this->solution,
        ]);
    }
}