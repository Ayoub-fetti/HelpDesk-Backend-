<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketClosed extends BaseTicketNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $ticket;

    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
            $performedBy = $this->actionPerformedBy 
            ? $this->actionPerformedBy->getFirstName() . ' ' . $this->actionPerformedBy->getLastName()
            : 'A technician';

        return (new MailMessage)
            ->subject('Ticket #' . $this->ticket->getId() . ' Has Been Closed')
            ->greeting('Hello!')
            ->line("$performedBy has closed ticket #" . $this->ticket->getId())
            ->line('Title: ' . $this->ticket->getTitle())
            ->action('View Ticket', url(config('app.frontend_url') . '/tickets/' . $this->ticket->getId()))
            ->line('Thank you for using our HelpDesk system!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return array_merge($this->getTicketData(), [
            'type' => 'ticket_closed',
            'message' => 'Ticket has been closed',
        ]);
    }
}
