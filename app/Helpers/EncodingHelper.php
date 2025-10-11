<?php

if (!function_exists('base64url_encode')) {
    /**
     * Encode data to Base64 URL-safe format (RFC 4648 §5).
     */
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

if (!function_exists('base64url_decode')) {
    /**
     * Decode Base64 URL-safe string back to binary.
     */
    function base64url_decode(string $data): string
    {
        // Replace URL-safe characters back to normal Base64
        $base64 = strtr($data, '-_', '+/');
        // Add padding if needed
        $padding = strlen($base64) % 4;
        if ($padding) {
            $base64 .= str_repeat('=', 4 - $padding);
        }
        return base64_decode($base64);
    }
}
