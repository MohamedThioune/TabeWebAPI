<?php

namespace App\Domain\GiftCards\Services;

use App\Infrastructure\External\Payment\PaymentGateway;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Transaction;
use App\Models\PayoutLine;
use App\Models\Payout as PayoutModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Str;
use App\Infrastructure\External\Payment\DTO\PaymentResponseDTO;
use App\Infrastructure\Persistence\PayoutRepository;

class Payout
{
    public const SUCCESS_TEXT = 'Transaction completed successfully';
    private const FEES = 0;

    public function __construct(private PaymentGateway $gateway, private PayoutRepository $payoutRepo){}

    public function initiatePayout(string $phone_number, int $gross_amount, string $withdraw_mode, User $user, Collection $transactions): ?PayoutModel
    {
        try {
            //Iniatiate payout refund
            $initiateResponse = null;
            // $initiateResponse = $this->gateway->initiate_refund(
            //     phone_number: $phone_number,
            //     amount: $gross_amount,
            //     withdraw_mode: $withdraw_mode
            // );

            // if(!$initiateResponse || !$initiateResponse->disburse_token):
            //     Log::error('Payout initiation failed in service : ', (array)$initiateResponse);
            //     return null;
            // endif;

            // Register the payout
            $fees = self::FEES; //Calculate fees if any
            $net_amount = $gross_amount - ($gross_amount * $fees);
            $payout = $this->payoutRepo->create([
                'gross_amount' => $gross_amount,
                'net_amount' => $net_amount,
                'fees' => $fees,
                'status' => 'authorized',
                // 'reference_number' => $initiateResponse->disburse_token,
                'user_id' => $user->id
            ]);

            DB::transaction(function () use ($transactions, $payout) {
                // Bulk insert payout lines
                $payoutLines = $transactions->map(fn ($transaction) => [
                    'id' => Str::uuid()->toString(),
                    'transaction_id' => $transaction->id,
                    'payout_id'      => $payout->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray();
                PayoutLine::insert($payoutLines);
            });

        } catch (\Exception $e) {
            Log::error('Payout initiation failed in service : ', (array)$e->getMessage());
            return null;
        }

        return $payout;
    }

    public function processPayout(PayoutModel $payout, string $disburse_id = null): ?object
    {  
        try{
            // Submit payout refund
            $submitResponse = $this->gateway->submit_refund(
                disburse_token: $payout->reference_number,
                disburse_id: null
            );  

            // if(!$submitResponse || !$submitResponse->response_text || $submitResponse->response_text != self::SUCCESS_TEXT):
            if(!$submitResponse || !$submitResponse->response_text):
                Log::error('Payout submit failed : ' . $submitResponse);
                return null;
            endif;

            // Update Transaction & Payout status
            DB::transaction(function () use ($payout, $transactions) {
                // Create payout with completed status
                $new_payout = $payout->replicate([
                    'gross_amount',
                    'net_amount',
                    'commenntary',
                    'fees',
                    'currency',
                    'withdraw_mode',
                    'reference_number',
                    'transaction_id',
                    'user_id',
                ]);
                $new_payout->status = 'completed';
                $new_payout->parent_payout_id = $payout->id;
                $new_payout->save();
                // Update original payout
                $payout->next_payout_id = $new_payout->id;
                $payout->save();
                // var_dump($transactions);
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
            Log::error('Payout submit failed in service', (array)$e->getMessage());
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
    

        return (Object)['reference' => $reference, 'transaction' => $submitResponse->transaction_id, 'status' => $submitResponse->response_text];
    }
}