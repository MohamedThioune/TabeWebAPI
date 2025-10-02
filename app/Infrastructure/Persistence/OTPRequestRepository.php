<?php

namespace App\Infrastructure\Persistence;
use App\Models\OTPRequest;

class OTPRequestRepository
{
    public function save (array $dto): OTPRequest
    {
        $model = OTPRequest::create($dto);

        return $model;
    }

    public function update(OTPRequest $otpRequest, array $fields): OTPRequest {
        $otpRequest->fill($fields);
        $otpRequest->save();

        return $otpRequest;
    }

}
