<?php

namespace App\Notifications;

use App\Domain\Users\DTO\Node;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PushSMSNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Node $node, public string $channel)
    {
        $this->channel = $channel;
    }
    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['twilio'];
        // return ['twilio', 'mail', 'database'];
    }

//    public function shouldSend($notifiable, string $channel): bool
//    {
//        return (bool) $notifiable->accept_notification;
//    }

    public function toTwilio(object $notifiable): array{
        return [
            "from" => config("services.twilio.phone"),
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

    public function toDatabase(object $notifiable): array
    {
        return [
            'data' => $this->toTwilio($notifiable),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
