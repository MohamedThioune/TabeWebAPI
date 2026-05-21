<?php

namespace App\Domain\GiftCards\Services;

use App\Infrastructure\External\Payment\PaymentGateway;
use App\Infrastructure\Persistence\PayoutRepository;
use App\Models\Payout as PayoutModel;
use App\Models\PayoutLine;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Payout
{
    public const SUCCESS_TEXT = 'Transaction completed successfully';

    public const SUCCESS = 'completed';

    private const FEES = 0;

    public function __construct(private PaymentGateway $gateway, private PayoutRepository $payoutRepo) {}

    public function initiatePayout(string $phone_number, int $gross_amount, string $withdraw_mode, User $user, Collection $transactions): ?PayoutModel
    {
        $gross_amount = 200;
        try {
            // Initiate payout refund
            $initiateResponse = null;
            $initiateResponse = $this->gateway->initiate_refund(
                phone_number: $phone_number,
                amount: $gross_amount,
                withdraw_mode: $withdraw_mode
            );

            if (! $initiateResponse || ! $initiateResponse->disburse_token) {
                Log::error('Payout initiation failed in service : ', (array) $initiateResponse);

                return null;
            }

            // Register the payout
            $fees = self::FEES; // Calculate fees if any
            $net_amount = $gross_amount - ($gross_amount * $fees);
            $payout = $this->payoutRepo->create([
                'gross_amount' => $gross_amount,
                'net_amount' => $net_amount,
                'fees' => $fees,
                'status' => 'authorized',
                'reference_number' => $initiateResponse->disburse_token,
                'user_id' => $user->id,
            ]);

            // Bulk insert payout lines
            DB::transaction(function () use ($transactions, $payout) {
                $payoutLines = $transactions->map(fn ($transaction) => [
                    'id' => Str::uuid()->toString(),
                    'transaction_id' => $transaction->id,
                    'payout_id' => $payout->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray();
                PayoutLine::insert($payoutLines);
            });

        } catch (\Exception $e) {
            Log::error('Payout initiation failed in service : ', (array) $e->getMessage());

            return null;
        }

        return $payout;
    }

    public function processPayout(PayoutModel $payout, ?string $disburse_id = null): ?object
    {
        try {
            // Submit payout refund
            $submitResponse = $this->gateway->submit_refund(
                disburse_token: $payout?->reference_number,
                disburse_id: null
            );

            if (! $submitResponse || ! $submitResponse->response_text || $submitResponse->response_text != self::SUCCESS_TEXT) {
                Log::error('Payout submit failed : ', (array) $submitResponse);

                return null;
            }

            $transactions = $payout->transactions;
            // Update Transaction & Payout status
            DB::transaction(function () use ($payout, $transactions) {
                // Create payout with completed status
                $new_payout = $payout->replicate([
                    'id',
                    'status',
                    'parent_payout_id',
                    'next_payout_id',
                    'reference_number',
                ]);
                $new_payout->status = self::SUCCESS;
                $new_payout->parent_payout_id = $payout->id;
                $new_payout->reference_number = $payout->reference_number.'-REFUND';
                $new_payout->save();

                // Update original payout
                $payout->next_payout_id = $new_payout->id;
                $payout->save();

                // Bulk create refunded transactions
                $refundedTransactions = $transactions->map(fn ($transaction) => [
                    'id' => Str::uuid()->toString(),
                    'user_id' => $transaction->user_id,
                    'gift_card_id' => $transaction->gift_card_id,
                    'amount' => $transaction->amount,
                    'status' => 'refunded',
                    'parent_transaction_id' => $transaction->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Transaction::insert($refundedTransactions->toArray());
                // Bulk update original transactions
                $refundMap = $refundedTransactions->keyBy('parent_transaction_id');
                foreach ($transactions as $transaction) {
                    $transaction->update([
                        'next_transaction_id' => $refundMap[$transaction->id]['id'],
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('Payout submit failed in service', (array) $e->getMessage());

            return null;
        }

        // try {
        //     // Register the payouts
        //     $user->invoices()->create([
        //         'id' => Str::uuid()->toString(),
        //         'type' => 'Remboursement de carte',
        //         'amount' => $amount,
        //         'reference_number' => $reference,
        //         'status' => $submitResponse->status ?: 'pending',
        //         'endpoint' => 'checkout',
        //         'gift_card_id' => $gift_card->id
        //     ]);

        // } catch (\Exception $e) {
        //     Log::error('Error logging payment response: ' . $e->getMessage());
        // }

        return (object) ['reference' => $payout->reference_number, 'transaction' => $submitResponse->transaction_id, 'status' => $submitResponse->response_text];
    }
}
