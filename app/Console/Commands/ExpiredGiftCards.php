<?php

namespace App\Console\Commands;

use App\Models\GiftCard;
use Illuminate\Console\Command;

class ExpiredGiftCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'giftcards:expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to expire gift cards';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        GiftCard::where('status', 'active')
            ->where('expired_at', '<', now())
            ->update(['status' => 'expired']);
    }
}
