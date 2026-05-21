<?php

namespace App\Events;

use App\Models\Payout;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReimburseProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Payout $payout, public User $user) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('notifs.admin.'.$this->user->id); // channel for the admin
    }

    public function broadcastWith(): array
    {
        return [
            'title' => 'Demande de remboursement traitée avec succès !',
            'message' => "La demande de remboursement d'un montant de ".$this->payout?->gross_amount.' a été traitée avec succès !',
            'payout' => [
                'id' => $this->payout?->id,
                'gross_amount' => $this->payout?->gross_amount,
                'requested_at' => $this->payout?->created_at,
                'transactions' => $this->payout->transactions,
            ],
            'notification_count' => $this->user->unreadNotifications()->count(),
        ];
    }
}
