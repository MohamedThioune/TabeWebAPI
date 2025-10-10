<?php

namespace App\Domain\GiftCards\ValueObjects;

class CardEvent
{
    private string $id;
    private string $type;
    private string $gift_card_id;
    private ?array $meta_json;

    public function __construct(string $id, string $type, string $gift_card_id, ?array $meta_json){
        $this->id = $id;
        $this->type = $type;
        $this->gift_card_id = $gift_card_id;
        $this->meta_json = $meta_json;
    }
    public function getType(): string{
        return $this->type;
    }
    public function getGiftCardId(): string{
        return $this->gift_card_id;
    }
    public function getMetaJson(): array{
        return $this->meta_json;
    }

    public function toArray(): array{
        return [
            'id' => $this->id,
            'type' => $this->type,
            'meta_json' => $this->meta_json,
            'gift_card_id' => $this->gift_card_id
        ];
    }
}
