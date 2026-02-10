<?php

namespace App\Infrastructure\External\Payment\DTO;

class PaymentResponseDTO implements DataTransferObject
{
    public function __construct(
        public ?string $status = null,
        public ?string $hash = null,
        public ?string $response_code = null,
        public ?string $reference_number = null,
        public ?string $url = null,
        public ?string $response_text = null,
        public ?string $description = null,
        public ?string $withdraw_mode = null, 
        public ?string $amount = null, 
        public ?string $token = null,
        public ?string $disburse_token = null,  
        public ?string $transaction_id = null,      
        public ?array  $custom_data = null,
        public ?string $receipt_url = null,
        public ?string $fail_reason = null,
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
