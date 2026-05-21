<?php

namespace App\Jobs;

use App\Domain\GiftCards\Services\Payout as PayoutService;
use App\Domain\Users\DTO\Node;
use App\Events\ReimburseProcessed;
use App\Events\SubmitPayoutProcessed;
use App\Models\Payout;
use App\Models\User;
use App\Notifications\TransactionNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReimbursePayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user, public Payout $payout, public ?string $disburse_id, private PayoutService $payoutSes) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $reponse = $this->payoutSes->processPayout(
            payout: $this->payout,
            disburse_id: $this->disburse_id
        );

        Log::info('Payout process response : ', (array) $reponse);

        // Notify shop and admin via WhatsApp
        $admin = User::role('admin')->first();
        $content = 'Remboursement effectué pour le montant de '.$this->payout->amount.'!';
        $node_shop = new Node(content: $content, contentVariables: null, level: 'Important', model: 'transaction', title: 'Remboursement effectué !', body: $content);
        $this->user->notify(new TransactionNotification(node: $node_shop, channel: 'whatsApp'));

        $content = 'Remboursement effectué pour le montant de '.$this->payout->amount.'!';
        $node_shop = new Node(content: $content, contentVariables: null, level: 'Important', model: 'transaction', title: 'Remboursement effectué !', body: $content);
        $admin->notify(new TransactionNotification(node: $node_shop, channel: 'whatsApp'));
        /* End of notifications */

        /* Broadcast event to update notifications in real time for the different parties (partner, admin) */
        event(new ReimburseProcessed($this->payout, $admin)); // broadcast event for the admin
        event(new SubmitPayoutProcessed($this->payout, $this->user)); // broadcast event for the partner
    }
}
