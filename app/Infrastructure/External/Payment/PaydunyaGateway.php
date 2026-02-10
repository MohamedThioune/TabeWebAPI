<?php

namespace App\Infrastructure\External\Payment;

use App\Infrastructure\External\Payment\DTO\PaymentResponseDTO;
use App\Models\GiftCard;
use App\Models\Invoice;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaydunyaGateway implements PaymentGateway
{
    public array $headers;
    public string $url;
    public string $mode;
    public string $shop_name;
    public array $actions;

    public function __construct()
    {
        $this->headers = [
            'Content-Type' => 'application/json',
            'PAYDUNYA-MASTER-KEY' => config('services.paydunya.masterKey'),
            'PAYDUNYA-PRIVATE-KEY' => config('services.paydunya.privateKey'),
            'PAYDUNYA-TOKEN' => config('services.paydunya.token')
        ];

        $this->url = config('services.paydunya.url');
        $this->shop_name = config('services.paydunya.name', 'HelloPme');
        $this->publicKey = config('services.paydunya.publicKey');
        $this->privateKey = config('services.paydunya.privateKey');
        $this->token = config('services.paydunya.token');
        $this->mode = config('services.paydunya.mode');

        $this->actions = [
            "cancel_url" => config('services.paydunya.cancelUrl'),
            "return_url" => config('services.paydunya.returnUrl'),
            "callback_url" => route('api.paydunya.ipn')
        ];
    }

    public function post_callout(string $url, array $headers, array $payload) : \Illuminate\Http\Client\Response
    {
        //Call the checkout invoice endpoint
        $response = Http::withHeaders($headers)
            ->timeout(10)
            ->retry(3, 200)
            ->post($url, $payload);

        //Log the api request
        Log::info('PayDunya request', [
            'endpoint' => $url,
            'headers' => $headers,
            'payload' => $payload,
        ]);

        $response->throw(); // launch exception if 4xx/5xx

        return $response;
    }

    public function get_callout(string $url, array $headers, array $query = null) : \Illuminate\Http\Client\Response
    {
        //Call the checkout invoice endpoint
        $response = Http::withHeaders($headers)
            ->timeout(10)
            ->retry(3, 200)
            ->get($url, $query);

        //Log the api request
        Log::info('PayDunya request', [
            'endpoint' => $url,
            'headers' => $headers,
        ]);

        $response->throw(); // launch exception if 4xx/5xx

        return $response;
    }

    public function charge(int $amount, string $description, GiftCard $gift_card , array $customer = []) : ?PaymentResponseDTO
    {
        $this->url = config('services.paydunya.urlSandBox');
        $endpoint = $this->url . '/checkout-invoice/create';

        $payload = [
            'invoice' => [
            'total_amount' => $amount,
            'description' => $description,
            ],
            'store' => [
                'name' => $this->shop_name,
            ],
            'custom_data' => [
                'gift_card_id' => $gift_card->id
            ],
            'actions' => $this->actions
        ];

        //add customer information through the invoice
        if(!empty($customer))
            $payload['invoice']['customer'] =  $customer ;

        try
        {
            $response = $this->post_callout($endpoint, $this->headers, $payload);
        }
        catch (RequestException $e)
        {
            //Log the api request failed
            Log::error('Paydunya request failed', [
                'url' => $endpoint,
                'status' => $e->response?->status(),
            ]);

            return null;
        }

        Log::info('PayDunya response', $response->json());

        return PaymentResponseDTO::fromArray($response->json());

    }

    public function quick_pay(int $amount,string $recipient_email, GiftCard $gift_card, string $recipient_phone = null,  int $support_fees = 1, int $send_notification = 0)
    {
        $endpoint = $this->url . '/dmp-api';

        $payload = [
            'recipient_email' => $recipient_email,
            'amount' => $amount,
            'support_fees' => $support_fees,
            'send_notification' => $send_notification,
            'custom_data' => [
                'gift_card_id' => $gift_card->id
            ],
            'actions' => $this->actions,
        ];

        if($recipient_phone)
            $payload['recipient_phone'] = $recipient_phone;

        try
        {
            $response = $this->post_callout($endpoint, $this->headers, $payload);
        }
        catch (RequestException $e)
        {
            //Log the api request failed
            Log::error('Paydunya request failed', [
                'url' => $endpoint,
                'status' => $e->response?->status(),
            ]);

            return null;
        }

        Log::info('PayDunya response', $response->json());

        return PaymentResponseDTO::fromArray($response->json());
    }

    public function status_pay(string $reference_number, string $type_endpoint)
    {
        $payload = [
            'reference_number' => (int)$reference_number,
        ];

        $invoice = Invoice::where('reference_number', $reference_number)->first();

        try
        {
            $endpoint = $this->url . '/dmp-api/check-status';

            if($type_endpoint == "checkout"):
                $this->url = config('services.paydunya.urlSandBox');
                $endpoint = $this->url . "/checkout-invoice/confirm/" . $invoice?->reference_number;
            endif;

            $response = ($type_endpoint == "checkout") ? $this->get_callout($endpoint, $this->headers) : $this->post_callout($endpoint, $this->headers, $payload);
            Log::info('PayDunya response', (array)$response->json());
        }
        catch (RequestException $e)
        {
            //Log the api request failed
            Log::error('Paydunya request failed', [
                'url' => $endpoint,
                'status' => $e->response?->status(),
            ]);

            return null;
        }

        Log::info('PayDunya response', (array)$response->json());

        return PaymentResponseDTO::fromArray($response->json());
    }

    public function initiate_refund(string $phone_number, int $amount, string $withdraw_mode)
    {
        $this->url = config('services.paydunya.urlV2');
        $endpoint = $this->url . '/disburse/get-invoice';

        $payload = [
            'account_alias' => $phone_number,
            'amount' => $amount,
            'withdraw_mode' => $withdraw_mode,
            'callback_url' => $this->actions['callback_url'],
        ];

        try
        {
            $response = $this->post_callout($endpoint, $this->headers, $payload);
        }
        catch (RequestException $e)
        {
            //Log the api request failed
            Log::error('Paydunya request failed', [
                'url' => $endpoint,
                'status' => $e->response?->status(),
            ]);

            return null;
        }

        Log::info('PayDunya response', (array)$response->json());

        return PaymentResponseDTO::fromArray($response->json());
    }

    public function submit_refund(string $disburse_token, string $disburse_id = null)
    {
        $this->url = config('services.paydunya.urlV2');
        $endpoint = $this->url . '/disburse/submit-invoice';

        $payload = [
            'disburse_invoice' => $disburse_token,            
        ];

        if($disburse_id)
            $payload['disburse_id'] = $disburse_id;

        try
        {
            $response = $this->post_callout($endpoint, $this->headers, $payload);
        }
        catch (RequestException $e)
        {
            //Log the api request failed
            Log::error('Paydunya service failed', [
                'url' => $endpoint,
                'status' => $e->response?->status(),
            ]);

            return null;
        }

        Log::info('PayDunya response', (array)$response->json());

        return PaymentResponseDTO::fromArray($response->json());
    }


}
