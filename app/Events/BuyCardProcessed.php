<?php

namespace App\Events;

use App\Models\GiftCard;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BuyCardProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(private GiftCard $giftCard, private User $user)
    {
        $this->giftCard = $giftCard;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('notifs.client.'.$this->user->id);
    }

    public function broadcastWith(): array
    {
        return [
            'title' => 'Félicitations ✨!',
            'message' => "Votre carte Tabé 💳 d'un montant de ".$this->giftCard?->face_amount.' a été ajoutée avec succès ! Voir dans Mes cartes.',
            'gift_card' => [
                'id' => $this->giftCard?->id,
                'code' => $this->giftCard?->code,
                'face_amount' => $this->giftCard?->face_amount,
                'status' => $this->giftCard?->status,
                'expired_at' => $this->giftCard?->expired_at,
                'beneficiary' => $this->giftCard?->beneficiary?->only('id', 'full_name', 'phone'),
            ],
            'notification_count' => $this->user->unreadNotifications()->count(),
        ];
    }
}
