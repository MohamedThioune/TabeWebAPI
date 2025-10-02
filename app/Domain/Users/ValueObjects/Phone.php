<?php

namespace App\Domain\Users\ValueObjects;

class Phone
{
    private string $number;

    public function __construct(string $number)
    {
        // Basic validation for phone number format (you can enhance this as needed)
        if (!preg_match('/^\+?[1-9]\d{1,14}$/', $number)) {
            throw new \InvalidArgumentException("Invalid phone number format.");
        }
        $this->number = $number;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function __toString(): string
    {
        return $this->number;
    }

}
