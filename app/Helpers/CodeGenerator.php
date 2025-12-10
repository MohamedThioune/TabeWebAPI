<?php

namespace App\Helpers;

class CodeGenerator
{
    /**
     * Generates a secure code: 4 letters + 4 numbers + checksum
     */
    public static function generate(): string
    {
        $letters = self::randomLetters(4);
        $numbers = self::randomNumbers(4);

        // Base du code sans checksum
        $raw = $letters . $numbers;

        // Lettre de contrôle
        $checksum = self::checksumLetter($raw);

        return "{$letters}-{$numbers}-{$checksum}";
    }

    /**
     * Generates X random uppercase letters
     */
    private static function randomLetters(int $length): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return collect(range(1, $length))
            ->map(fn () => $alphabet[random_int(0, 25)])
            ->join('');    
    }

    /**
     * Generates X random digits
     */
    private static function randomNumbers(int $length): string
    {
        $digits = '';
        for ($i = 0; $i < $length; $i++) {
            $digits .= random_int(0, 9);
        }
        return $digits;
    }

    /**
     * Generates a checksum letter
     * based on a hash mod 26
     */
    private static function checksumLetter(string $input): string
    {
        $hash = crc32($input);       // fast hash
        $index = $hash % 26;         // 0-25
        return chr(65 + $index);     // converts to A-Z
    }

    /**
    * Verify that a code has not been tampered with by validating the checksum.
    */
    public static function isValid(string $fullCode): bool
    {
        // Expected format: AAAA-1234-K
        if (!preg_match('#^([A-Z]{4})-([0-9]{4})-([A-Z])$#', $fullCode, $matches)) {
            return false; // invalid format
        }

        $letters = $matches[1];
        $numbers = $matches[2];
        $checksumProvided = $matches[3];

        // Recreate the raw part
        $raw = $letters . $numbers;

        // Recalculate checksum
        $expectedChecksum = self::checksumLetter($raw);

        // Secure comparison (avoids timing attacks)
        return hash_equals($expectedChecksum, $checksumProvided);
    }

}
