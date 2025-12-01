<?php

namespace App\Infrastructure\External\Payment\DTO;

class PaymentResponseDTO implements DataTransferObject
{
    public function __construct(
        public ?string $status,
        public ?string $hash,
        public ?string $response_code,
        public ?string $reference_number,
        public ?string $url,
        public ?string $response_text,
        public ?string $description,
        public ?string $token,
        public ?array  $custom_data,
        public ?string $receipt_url,
        public ?string $fail_reason,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self (
            status: $data['status'] ?? 'pending',
            hash: $data['hash'] ?? '',
            response_code: $data['response_code'] ?? "00",
            reference_number: $data['reference_number'] ?? null,
            url: $data['url'] ?? null,
            response_text: $data['response_text'] ?? null,
            description: $data['description'] ?? null,
            token:  $data['token'] ?? null,
            custom_data: $data['custom_data'] ?? null,
            receipt_url: $data['receipt_url'] ?? null,
            fail_reason: $data['fail_reason'] ?? null,
        );
    }

}
