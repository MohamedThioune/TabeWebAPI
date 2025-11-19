<?php

namespace App\Infrastructure\External\DTO;

class PaymentResponseDTO implements DataTransferObject
{
    public ?string $status;
    public ?string $response_code;
    public ?string $response_text;
    public ?string $description;
    public ?string $token;

    public static function fromArray(array $data): self
    {
        return new self([
            'status' => $data['status'] ?? 'FAILED',
            'response_code' => $data['response_code'] ?? "00",
            'response_text' => $data['response_text'] ?? null,
            'description' => $data['description'] ?? null,
            'token' =>  $data['token'] ?? null,
            ]);
    }

}
