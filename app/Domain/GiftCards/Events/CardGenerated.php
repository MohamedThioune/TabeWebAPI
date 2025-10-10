<?php

namespace App\Domain\GiftCards\Events;

use App\Domain\GiftCards\Entities\Card;
use App\Domain\GiftCards\ValueObjects\CardEvent;
use App\Domain\GiftCards\ValueObjects\QrSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CardGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Card $card, public ?QrSession $qrSession, public ?CardEvent $cardEvent, public ?array $errorMessage){
        $this->card = $card;
        $this->qrSession = $qrSession;
        $this->cardEvent = $cardEvent;
    }

}
