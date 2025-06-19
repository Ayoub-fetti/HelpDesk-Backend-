<?php

namespace App\Notifications;
use App\Domains\Tickets\Entities\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class BaseTicketNotification extends Notification 
{
    use Queueable;

    protected $ticket;
    protected $actionPerformedBy;

    public function __construct($ticket, $actionPerformedBy = null)
    {
        $this->ticket = $ticket;
        $this->actionPerformedBy = $actionPerformedBy;
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
    abstract public function toMail(object $notifiable): MailMessage;

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(object $notifiable): array;

    protected function getTicketData(): array
    {
        return [
            'ticket_id' => $this->ticket->getId(),
            'title' => $this->ticket->getTitle(),
            'status' => $this->ticket->getStatut()->toString(),
            'priority' => $this->ticket->getPriority()->toString(),
            'performer' => $this->actionPerformedBy ? [
                'id' => $this->actionPerformedBy->getId(),
                'name' => $this->actionPerformedBy->getFirstName() . ' ' . $this->actionPerformedBy->getLastName(),
                'email' => $this->actionPerformedBy->getEmail(),
                'type' => $this->actionPerformedBy->getUserType(),
            ] : null,
        ];
    }

}
