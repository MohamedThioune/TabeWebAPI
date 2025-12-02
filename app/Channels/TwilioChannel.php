<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class TwilioChannel
{
    public function send($notifiable, Notification $notification){
        // Config
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $twilio = new Client($sid, $token);

        $to = ($notification->channel == "sms") ? $notifiable->phone : $notifiable->whatsApp;
        $payload = $notification->toTwilio($notifiable);

        $message = $twilio->messages
            ->create($to,
                $payload
            );

        Log::info($message);
    }
}
