<?php

namespace App\Notifications;


use Illuminate\Notifications\Messages\MailMessage;


class TicketStatusChanged extends BaseTicketNotification
{
    protected $oldStatus;
    protected $newStatus;

    public function __construct($ticket, $oldStatus, $newStatus, $actionPerformedBy = null)
    {
        parent::__construct($ticket, $actionPerformedBy);
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $performedBy = $this->actionPerformedBy 
            ? $this->actionPerformedBy->getFirstName() . ' ' . $this->actionPerformedBy->getLastName()
            : 'A user';

        return (new MailMessage)
            ->subject('Ticket #' . $this->ticket->getId() . ' Status Changed')
            ->greeting('Hello!')
            ->line("$performedBy has changed the status of ticket #" . $this->ticket->getId())
            ->line('From: ' . $this->oldStatus->toString())
            ->line('To: ' . $this->newStatus->toString())
            ->line('Ticket title: ' . $this->ticket->getTitle())
            ->action('View Ticket', url(config('app.frontend_url') . '/tickets/' . $this->ticket->getId()))
            ->line('Thank you for using our HelpDesk system!');
    }

    public function toArray(object $notifiable): array
    {
        return array_merge($this->getTicketData(), [
            'type' => 'ticket_status_changed',
            'message' => 'Ticket status has been changed',
            'old_status' => $this->oldStatus->toString(),
            'new_status' => $this->newStatus->toString(),
        ]);
    }
}
