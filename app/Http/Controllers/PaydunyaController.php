<?php

namespace App\Http\Controllers;

use App\Domain\GiftCards\Services\CheckStatusPaymentCard;
use App\Infrastructure\External\Payment\ValueObjects\PayDunyaStatus;
use App\Infrastructure\Persistence\GiftCardRepository;
use App\Models\GiftCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaydunyaController extends AppBaseController
{

    public function __construct(private GiftCardRepository $giftCardRepository, private CheckStatusPaymentCard $checkStatus){}

    public function success_pay(mixed $data, string $type_endpoint): void
    {
        // Change the status card to active
        $gift_card = tap($this->giftCardRepository->find($data->custom_data['gift_card_id']), function($gift_card) {
            $gift_card->status = "active";
            $gift_card->save();
        });

        // Find & update the status invoice to completed
        $invoice = tap($gift_card->latest_invoice($type_endpoint),
            function($invoice) use ($data) {
                $invoice->status = PayDunyaStatus::Completed->value;
                $invoice->receipt_url = $data->receipt_url;
                $invoice->updated_at = now();
                $invoice->save();
        });

    }

    public function ipn_handle(Request $request){
        $input = tap($request->all(), function($input) {
            Log::info('PaydunyaIPN::handle', $input);
        });

        $data = $input['data'] ?? null;

        try{
            DB::beginTransaction();
            if(!hash_equals(hash('sha512', config("services.paydunya.masterKey")), $data['hash'])):
                if($data['status'] !== PayDunyaStatus::Completed->value){
                    $this->success_pay($data, 'checkout');
                    DB::commit();
                }
            else
                Log::error('Invalid signature provider');
            endif;

        }catch (\Exception $exception){
            Log::error('Failed IPN :', (array)$exception);
            DB::rollBack();
        }

        return $this->sendSuccess('PayDunya IPN activated');

    }

    /**
     * @OA\Post(
     *      path="/verify/{giftCard}",
     *      summary="VerifyPayment",
     *      tags={"Payment"},
     *      description="Verify status of payment",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *           name="giftCard",
     *           description="Gift card ID",
     *            @OA\Schema(
     *              type="string"
     *           ),
     *           required=true,
     *           in="path"
     *      ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *           mediaType="multipart/form-data",
     *            @OA\Schema(
     *              @OA\Property(
     *                    property="endpoint",
     *                    type="string",
     *              ),
     *            ),
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/GiftCard"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function verify(Request $request, GiftCard $giftCard){
        $user = $request->user();
        $type_endpoint = $request->get('endpoint', 'checkout');
        $data = $this->checkStatus->execute($giftCard, $type_endpoint);
        $status = $data->status ?? null;
        $message = "Status : {$status}";

        // Check ownership of the actual card
        $hasCard = $user->gift_cards()->where('id', $giftCard->id)->exists();
        if(!$hasCard)
            return $this->sendError('Invalid authorization !', 401);

        try{
            DB::beginTransaction();
            if(!hash_equals($data->hash, hash('sha512', config("services.paydunya.masterKey")) )){
                return $this->sendError('Invalid signature provider');
            }
            if(!$status || $status !== PayDunyaStatus::Pending->value){
                Log::error($data->fail_reason ?? null);
                return $this->sendError($data->fail_reason ?? $message);
            }
            $this->success_pay($data, $type_endpoint);
            DB::commit();

        }catch (\Exception $exception){
            Log::error('Failed IPN :', (array)$exception);
            DB::rollBack();
        }

        return $this->sendSuccess("{$message}, payment processed successfully !");
    }

}
