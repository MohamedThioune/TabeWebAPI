<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

trait ResponseController
{
    public static function success(?string $message, int $code = 200): JsonResponse
    {
        return response()->json(['status' => true, 'message' => $message ?? 'Success!', 'code' => $code], $code);
    }

    public static function error(?string $message, int $code = 400): JsonResponse
    {
        return response()->json(['status' => false, 'message' => $message ?? 'Error!', 'code' => $code], $code);
    }

    public static function response($data, ?string $message, int $code = 200): JsonResponse
    {
        return response()->json(['status' => true, 'data' => $data, 'message' => $message ?? 'Success!', 'code' => $code], $code);
    }
}
