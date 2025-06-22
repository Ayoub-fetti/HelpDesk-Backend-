<?php

namespace App\Notifications;

use App\Domains\Tickets\Entities\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentAdded extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;
    protected $comment;
    protected $commentAuthor;

    public function __construct($ticket, $comment, $commentAuthor)
    {
        $this->ticket = $ticket;
        $this->comment = $comment;
        $this->commentAuthor = $commentAuthor;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $authorName = $this->commentAuthor->getFirstName() . ' ' . $this->commentAuthor->getLastName();
        $isPrivate = $this->comment->isPrivate() ? ' (Private)' : '';

        return (new MailMessage)
            ->subject("New Comment{$isPrivate} on Ticket #" . $this->ticket->getId())
            ->greeting('Hello!')
            ->line("$authorName added a new comment on ticket #" . $this->ticket->getId())
            ->line('Title: ' . $this->ticket->getTitle())
            ->line('Comment:')
            ->line($this->comment->getContent())
            ->action('View Ticket', url(config('app.frontend_url') . '/tickets/' . $this->ticket->getId()))
            ->line('Thank you for using our HelpDesk system!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->getId(),
            'title' => $this->ticket->getTitle(),
            'comment_id' => $this->comment->getId(),
            'comment_content' => $this->comment->getContent(),
            'is_private' => $this->comment->isPrivate(),
            'type' => 'comment_added',
            'message' => 'New comment added to ticket',
            'author' => [
                'id' => $this->commentAuthor->getId(),
                'name' => $this->commentAuthor->getFirstName() . ' ' . $this->commentAuthor->getLastName(),
            ],
        ];
    }
}