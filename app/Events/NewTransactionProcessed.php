<?php

namespace App\Events;

use App\Models\User; 
use App\Models\GiftCard;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewTransactionProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance "merchant notification".
    */
    public function __construct(private User $user, private int $amount, private GiftCard $giftCard)
    {
        $this->user = $user;
        $this->amount = $amount;
        $this->giftCard = $giftCard;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
    */
    public function broadcastOn(): Channel
    {
        return 
            new PrivateChannel('notifs.merchant.' . $this->user->id);
    }

    public function broadcastWith(): array
    {
        return [
            "title" => "Nouvelle transaction !",
            "message" => "Une nouvelle transaction d'un montant de " . $this->amount . " FCFA a été effectuée avec votre client ! Voir dans transactions pour plus de détails.",
            "gift_card" => [
                'id' => $this->giftCard?->id,
                'code' => $this->giftCard?->code,
                'face_amount' => $this->giftCard?->face_amount,
                'status' => $this->giftCard?->status,
                'expired_at' => $this->giftCard?->expired_at,
                'beneficiary' => $this->giftCard?->beneficiary?->only('id', 'full_name', 'phone'),
            ],
            "notification_count" => $this->user->unreadNotifications()->count()
        ];
    }
}
