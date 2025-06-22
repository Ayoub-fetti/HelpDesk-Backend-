<?php

namespace App\Notifications;
use Illuminate\Notifications\Messages\MailMessage;


class TicketCreated extends BaseTicketNotification
{
    public function via(object $notifiable): array
    {
        return ['database'];
    }
  
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Ticket Created: ' . $this->ticket->getTitle())
            ->greeting('Hello!')
            ->line('A new ticket has been created:')
            ->line('ID: #' . $this->ticket->getId())
            ->line('Title: ' . $this->ticket->getTitle())
            ->line('Priority: ' . $this->ticket->getPriority()->toString())
            ->action('View Ticket', url(config('app.frontend_url') . '/tickets/' . $this->ticket->getId()))
            ->line('Thank you for using our HelpDesk system!');
    }

    public function toArray(object $notifiable): array
    {
        return array_merge($this->getTicketData(), [
            'type' => 'ticket_created',
            'message' => 'A new ticket has been created',
            'title' => 'New Ticket: ' . $this->ticket->getTitle(),
        ]);
    }

}
