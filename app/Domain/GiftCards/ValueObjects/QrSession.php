<?php

namespace App\Domain\GiftCards\ValueObjects;

class QrSession
{

    private string $id;
    private ?string $token;
    private string $url;
    private string $expired_at;
    private string $gift_card_id;

    /**
     * @param string $id
     * @param string $token
     * @param string $url
     * @param string $expired_at
     * @param string $gift_card_id
     */
    public function __construct(string $id, ?string $token, string $url, string $expired_at, string $gift_card_id)
    {
        $this->id = $id;
        $this->token = $token;
        $this->url = $url;
        $this->expired_at = $expired_at;
        $this->gift_card_id = $gift_card_id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getExpiredAt(): string
    {
        return $this->expired_at;
    }

    public function getGiftCardId(): string
    {
        return $this->gift_card_id;
    }

    public function toArray(): array{
        return [
            'id' => $this->id,
            'token' => $this->token,
            'url' => $this->url,
            'expired_at' => $this->expired_at,
            'gift_card_id' => $this->gift_card_id
        ];
    }

}
