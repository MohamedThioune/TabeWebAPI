<?php

namespace App\Domain\GiftCards\Entities;

use App\Domain\GiftCards\ValueObjects\CardEvent;
use App\Domain\GiftCards\ValueObjects\QrSession;

class Card
{
    private string $id;
    private string $belonging_type;
    private string $pin_hash;
    private string $pin_mask;
    private int $face_amount;
    private string $owner_user_id;
    private ?string $beneficiary_id;
    private string $design_id;

    /**
     * @param string $id
     * @param string $belonging_type
     * @param string $pin_hash
     * @param int $face_amount
     * @param string $pin_mask
     * @param string $owner_user_id
     * @param string|null $beneficiary_id
     * @param string $design_id
     */
    public function __construct(string $id, string $belonging_type, string $pin_hash, int $face_amount, string $pin_mask, string $owner_user_id, ?string $beneficiary_id, string $design_id)
    {
        $this->id = $id;
        $this->belonging_type = $belonging_type;
        $this->pin_hash = $pin_hash;
        $this->face_amount = $face_amount;
        $this->pin_mask = $pin_mask;
        $this->owner_user_id = $owner_user_id;
        $this->beneficiary_id = $beneficiary_id;
        $this->design_id = $design_id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getBelongingType(): string
    {
        return $this->belonging_type;
    }

    public function getPinHash(): string
    {
        return $this->pin_hash;
    }

    public function getPinMask(): string
    {
        return $this->pin_mask;
    }

    public function getFaceAmount(): int
    {
        return $this->face_amount;
    }

    public function getOwnerUserId(): string
    {
        return $this->owner_user_id;
    }

    public function getBeneficiaryId(): ?string
    {
        return $this->beneficiary_id;
    }

    public function getDesignId(): string
    {
        return $this->design_id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'belonging_type' => $this->belonging_type,
            'pin_hash' => $this->pin_hash,
            'pin_mask' => $this->pin_mask,
            'face_amount' => $this->face_amount,
            'owner_user_id' => $this->owner_user_id,
            'beneficiary_id' => $this->beneficiary_id,
            'design_id' => $this->design_id,
        ];
    }

}

