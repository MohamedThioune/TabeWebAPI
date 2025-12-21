<?php

namespace App\Notifications;

use App\Domain\Users\DTO\Node;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SharedCardNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Node $node, public string $beneficiary_phone, public string $channel)
    {
        $this->channel = $channel;
        $this->$beneficiary_phone = $beneficiary_phone;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $notifiable->phone = $this->beneficiary_phone;
        $notifiable->whatsApp = 'whatsapp:' . $this->beneficiary_phone;
        return ['twilio'];
    }

    public function toTwilio(object $notifiable): array{
        return [
            "from" => config("services.twilio.whatsapp"),
            "contentSid" => "HX54e1abe2a9b140947d4e6d50fa43c773",
            "contentVariables" => $this->node->contentVariables,
            "body" => $this->node->body
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'content' => $this->node->content,
            'title' => $this->node->title,
            'body' => $this->node->body,
            'level' => $this->node->level, //Important, Urgent, Info
            'model' => $this->node->model, //transaction, card, profile, maintenance
        ];
    }
}
