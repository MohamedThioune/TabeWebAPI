<?php

namespace App\Domain\GiftCards\Entities;

use App\Domain\GiftCards\ValueObjects\CardEvent;
use App\Domain\GiftCards\ValueObjects\QrSession;

class Card
{
    private string $id;
    private string $belonging_type;
    private string $type;
    private int $face_amount;
    private string $expired_at;
    private string $owner_user_id;
    private ?string $beneficiary_id;
    private string $design_id;

    /**
     * @param string $id
     * @param string $belonging_type
     * @param int $face_amount
     * @param string $expired_at
     * @param string $owner_user_id
     * @param string|null $beneficiary_id
     * @param string $design_id
     */
    public function __construct(string $id, string $belonging_type, string $type, int $face_amount, string $expired_at, string $owner_user_id, ?string $beneficiary_id, string $design_id)
    {
        $this->id = $id;
        $this->belonging_type = $belonging_type;
        $this->type = $type;
        $this->face_amount = $face_amount;
        $this->expired_at = $expired_at;
        $this->owner_user_id = $owner_user_id;
        $this->beneficiary_id = $beneficiary_id;
        $this->design_id = $design_id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'belonging_type' => $this->belonging_type,
            'type' => $this->type,
            'face_amount' => $this->face_amount,
            'expired_at' => $this->expired_at,
            'owner_user_id' => $this->owner_user_id,
            'beneficiary_id' => $this->beneficiary_id,
            'design_id' => $this->design_id,
        ];
    }

}

