<?php

namespace App\Events;

use App\Models\User;
use App\Models\Payout;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReceivePayoutProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public Payout $payout;
    /**
     * Create a new event instance "admin notification of a payout received". 
     */
    public function __construct(Payout $payout, User $user)
    {
        $this->user = $user;
        $this->payout = $payout;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): Channel
    {
        return 
            new PrivateChannel('notifs.admin.' . $this->user->id); //channel for the admin
    }

    public function broadcastWith(): array
    {
        return [
            "title" => "Nouvelle demande de rembourse reçue !",
            "message" => "Vous avez reçu une nouvelle demande de remboursement d'un montant de " . $this->payout?->gross_amount . " venant d'un partenaire !",
            "payout" => [
                'id' => $this->payout?->id,
                'gross_amount' => $this->payout?->gross_amount,
                'requested_at' => $this->payout?->created_at,
            ],
            "notification_count" => $this->user->unreadNotifications()->count()
        ];
    }
}
