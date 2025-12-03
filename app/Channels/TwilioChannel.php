<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class TwilioChannel
{
    public function send($notifiable, Notification $notification){

        try{
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $twilio = new Client($sid, $token);
            $twilio->setLogLevel('debug');
            
            $to = ($notification->channel == "sms") ? $notifiable->phone : $notifiable->whatsApp;
            $payload = $notification->toTwilio($notifiable);

            $message = $twilio->messages
                ->create($to,
                    $payload
                );

            // Print details about the last request
            // echo $twilio->lastRequest->method;
            // echo $twilio->lastRequest->url;
            // echo $twilio->lastRequest->auth;
            // echo $twilio->lastRequest->params;
            // echo $twilio->lastRequest->headers;

            // Print details about the last response
            // echo($twilio->lastResponse->statusCode);
            Log::info("Sending notification via TwilioChannel to" . $notifiable->phone, (array)$message);

        } catch (\Twilio\Exceptions\TwilioException $e){
            Log::error("Error logging notification: " . $e->getMessage());
        }
    }
}
