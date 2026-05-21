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

class PurchaseMerchantProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(private User $userMerchant, private User $userCustomer, private int $amount, private GiftCard $giftCard)
    {
        $this->userMerchant = $userMerchant;
        $this->userCustomer = $userCustomer;
        $this->amount = $amount;
        $this->giftCard = $giftCard;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('notifs.client.'.$this->userCustomer->id);
    }

    public function broadcastWith(): array
    {
        $shop_name = $this->userMerchant->partner->first()->name ?? 'votre marchand';

        return [
            'title' => 'Achat effectué 🎊 !',
            'message' => "Votre achat d'un montant de ".$this->amount.' a été effectué avec succès chez '.$shop_name.' ! Merci pour votre confiance .',
            'gift_card' => [
                'id' => $this->giftCard?->id,
                'code' => $this->giftCard?->code,
                'face_amount' => $this->giftCard?->face_amount,
                'status' => $this->giftCard?->status,
                'expired_at' => $this->giftCard?->expired_at,
                'beneficiary' => $this->giftCard?->beneficiary?->only('id', 'full_name', 'phone'),
            ],
            'notification_count' => $this->userCustomer->unreadNotifications()->count(),
        ];
    }
}
