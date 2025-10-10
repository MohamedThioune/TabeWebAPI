<?php

namespace App\Domain\GiftCards\Services;

use App\Domain\GiftCards\Events\CardGenerated;
use App\Infrastructure\Persistence\QRSessionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateQr
{
    public function __construct(private QRSessionRepository $qrSessionRepository){}
    /**
     * Handle the event.
     */
    public function handle(CardGenerated $event)
    {
        try {
            $this->qrSessionRepository->create($event->qrSession->toArray());
            // Log::info($event->qrSession->toArray());
            DB::commit();
        }
        catch (\Exception $e){
            DB::rollBack();
            $event->errorMessage['qr'] = $e->getMessage();
        }
    }
}
