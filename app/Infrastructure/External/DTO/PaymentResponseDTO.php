<?php

namespace App\Infrastructure\External\DTO;

class PaymentResponseDTO implements DataTransferObject
{
    public function __construct(
        public ?string $status,
        public ?string $response_code,
        public ?string $reference_number,
        public ?string $url,
        public ?string $response_text,
        public ?string $description,
        public ?string $token,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self (
            status: $data['status'] ?? 'SUCCESS',
            response_code: $data['response_code'] ?? "00",
            reference_number: $data['reference_number'] ?? null,
            url: $data['url'] ?? null,
            response_text: $data['response_text'] ?? null,
            description: $data['description'] ?? null,
            token:  $data['token'] ?? null,
            );
    }

}
