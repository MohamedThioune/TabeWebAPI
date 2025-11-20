<?php

namespace App\Infrastructure\External;

use App\Infrastructure\External\DTO\PaymentResponseDTO;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;

class PaydunyaGateway implements PaymentGateway
{
    public string $url;
    public string $masterKey;
    public string $publicKey;
    public string $privateKey;
    public string $token;
    public string $mode;
    public string $shop_name;

    public function __construct(){
//        $this->url = config('services.paydunya.url');
         $this->url = config('services.paydunya.urlSandBox');
        $this->masterKey = config('services.paydunya.masterKey');
        $this->publicKey = config('services.paydunya.publicKey');
        $this->privateKey = config('services.paydunya.privateKey');
        $this->token = config('services.paydunya.token');
        $this->mode = config('services.paydunya.mode');
        $this->shop_name = config('services.paydunya.shop_name', 'HelloPme');
    }

    public function post_callout(string $url, array $headers, array $payload) : \Illuminate\Http\Client\Response
    {
        //Call the checkout invoice endpoint
        $response = Http::withHeaders($headers)
            ->timeout(10)
            ->retry(3, 200)
            ->post($url, $payload);

        $response->throw(); // launch exception if 4xx/5xx

        //Log the api request
        Log::info('PayDunya request', [
            'endpoint' => $url,
            'headers' => $headers,
            'payload' => $payload,
        ]);

        return $response;
    }

    public function charge(int $amount, string $description, array $customer = []) : ?PaymentResponseDTO
    {
        $url = $this->url . '/checkout-invoice/create';

        $headers = [
            'Content-Type' => 'application/json',
            'PAYDUNYA-MASTER-KEY' => $this->masterKey,
            'PAYDUNYA-PRIVATE-KEY' => $this->privateKey,
            'PAYDUNYA-TOKEN' => $this->token
        ];
        $payload = [
            'invoice' => [
            'total_amount' => $amount,
            'description' => $description,
            ],
            'store' => [
                'name' => $this->shop_name,
            ],
        ];

        //add customer information through the invoice
        if(!empty($customer))
            $payload['invoice']['customer'] =  $customer ;

        try
        {
            $response = $this->post_callout($url, $headers, $payload);
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

        Log::info('PayDunya response', $response->json());

        return PaymentResponseDTO::fromArray($response->json());

    }

    public function quick_pay(int $amount,string $recipient_email, string $recipient_phone = null,  int $support_fees = 1, int $send_notification = 0)
    {
        $url = $this->url . '/dmp-api';

        $headers = [
            'Content-Type' => 'application/json',
            'PAYDUNYA-MASTER-KEY' => $this->masterKey,
            'PAYDUNYA-PRIVATE-KEY' => $this->privateKey,
            'PAYDUNYA-TOKEN' => $this->token
        ];
        $payload = [
            'recipient_email' => $recipient_email,
            'amount' => $amount,
            'support_fees' => $support_fees,
            'send_notification' => $send_notification,
        ];

        if($recipient_phone)
            $payload['recipient_phone'] = $recipient_phone;

        try
        {
            $response = $this->post_callout($url, $headers, $payload);
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

        Log::info('PayDunya response', $response->json());

        return PaymentResponseDTO::fromArray($response->json());
    }

}
