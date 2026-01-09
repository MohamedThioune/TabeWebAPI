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
        public ?string $withdraw_mode, 
        public ?string $amount, 
        public ?string $token,
        public ?string $disburse_token,  
        public ?string $transaction_id,      
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
            withdraw_mode: $data['withdraw_mode'] ?? null,
            amount: $data['amount'] ?? null,
            token: $data['token'] ?? null,
            disburse_token: $data['disburse_token'] ?? null,
            transaction_id: $data['transaction_id'] ?? null,
            custom_data: $data['custom_data'] ?? null,
            receipt_url: $data['receipt_url'] ?? null,
            fail_reason: $data['fail_reason'] ?? null,
        );
    }

}
