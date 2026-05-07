<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FileFailed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $meaning;
    public User $user;

    /**
     * Create a new event instance.
    */
    public function __construct(string $meaning, User $user)
    {
        $this->meaning = $meaning;
        $this->user = $user;            
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
    */
    public function broadcastOn(): Channel
    {
        $channel = new FindChannel($this->user);
        return new PrivateChannel($channel);
    }

    public function broadcastWith(): array
    {
        return [
            "title" => "Echec de traitement !",
            "message" => "Votre " . $this->meaning . " a échoué lors du traitement. Veuillez réessayer ultérieurement ou contacter le support si le problème persiste.",
            "notification_count" => $this->user->unreadNotifications()->count()
        ];
       
    }

}
