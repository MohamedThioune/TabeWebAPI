<?php

namespace App\Infrastructure\External;

use App\Infrastructure\External\DTO\PaymentResponseDTO;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;

class PaydunyaGateway implements PaymentGateway
{
    public static string $url;
    public static string $masterKey;
    public static string $publicKey;
    public static string $token;
    public static string $mode;
    public static string $shop_name;

    public function __construct(){
        self::$url = config('services.paydunya.url');
        self::$masterKey = config('services.paydunya.masterKey');
        self::$publicKey = config('services.paydunya.publicKey');
        self::$token = config('services.paydunya.token');
        self::$mode = config('services.paydunya.mode');
        self::$shop_name = config('services.paydunya.shop_name', 'HelloPme');
    }

    public static function charge(int $amount, string $description, array $customer = []) : ?PaymentResponseDTO
    {
        $payload = [
            'total_amount' => $amount,
            'description' => $description,
            'store' => [
                'name' => self::$shop_name,
            ],
        ];

        //add customer information through the invoice
        if(!empty($customer))
            $payload['invoice'] = ['customer' => $customer] ;

        $url = self::$url . '/checkout-invoice/create';

        try
        {
            //Call the checkout invoice endpoint
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'PAYDUNYA-MASTER-KEY' => self::$masterKey,
                'PAYDUNYA-PUBLIC-KEY' => self::$publicKey,
                'PAYDUNYA-TOKEN' => self::$token])
                ->timeout(10)
                ->retry(3, 200)
                ->post($url, $payload);

            $response->throw(); // launch exception if 4xx/5xx

            //Log the api request
            Log::info('PayDunya request', [
                'endpoint' => $url,
                'payload' => $payload,
            ]);
        }
        catch (RequestException $e)
        {
            //Log the api request failed
            Log::error('Paydunya request failed', [
                'url' => $url,
                'status' => $e->response?->status(),
            ]);

            return null;
        }

        return PaymentResponseDTO::fromArray($response->json());

    }

}
