<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssigned extends BaseTicketNotification
{

    public function toMail(object $notifiable): MailMessage
    {
        $actionText = ($notifiable->id === $this->ticket->getTechnician()->getId()) 
                     ? 'You have been assigned to this ticket' 
                     : 'This ticket has been assigned to ' . $this->ticket->getTechnician()->getFirstName() . ' ' . $this->ticket->getTechnician()->getLastName();
        
        return (new MailMessage)
            ->subject('Ticket #' . $this->ticket->getId() . ' Has Been Assigned')
            ->greeting('Hello!')
            ->line($actionText)
            ->line('Ticket details:')
            ->line('Title: ' . $this->ticket->getTitle())
            ->line('Priority: ' . $this->ticket->getPriority()->toString())
            ->action('View Ticket', url(config('app.frontend_url') . '/tickets/' . $this->ticket->getId()))
            ->line('Thank you for using our HelpDesk system!');
    }

    public function toArray(object $notifiable): array
    {
        return array_merge($this->getTicketData(), [
            'type' => 'ticket_assigned',
            'message' => 'Ticket has been assigned to a technician',
            'technician' => [
                'id' => $this->ticket->getTechnician()->getId(),
                'name' => $this->ticket->getTechnician()->getFirstName() . ' ' . $this->ticket->getTechnician()->getLastName(),
            ],
        ]);
    }
}
