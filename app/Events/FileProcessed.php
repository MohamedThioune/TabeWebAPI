<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FileProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $meaning;

    private string $path;

    public User $user;

    /**
     * Create a new event instance.
     */
    public function __construct(string $meaning, string $path, User $user)
    {
        $this->meaning = $meaning;
        $this->path = $path;
        $this->user = $user;
    }

    public function broadcastOn(): Channel
    {
        $channel = new FindChannel($this->user);

        return new PrivateChannel($channel);
    }

    public function broadcastWith(): array
    {
        return [
            'title' => 'Fichier traité !',
            'message' => 'Votre '.$this->meaning.' a été traité avec succès. Vous pouvez les consulter dans votre profil.',
            'notification_count' => $this->user->unreadNotifications()->count(),
        ];
    }
}
