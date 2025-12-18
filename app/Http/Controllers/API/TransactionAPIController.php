<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateTransactionAPIRequest;
use App\Http\Requests\API\UpdateTransactionAPIRequest;
use App\Models\Transaction;
use App\Infrastructure\Persistence\TransactionRepository;
use App\Infrastructure\Persistence\GiftCardRepository;
use App\Infrastructure\Persistence\CardEventRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\TransactionResource;
use App\Notifications\PushBeneficiaryWhatsAppNotification;
use App\Notifications\PushWhatsAppNotification;
use App\Domain\Users\DTO\Node;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class TransactionController
 */

class TransactionAPIController extends AppBaseController
{
    /** @var  TransactionRepository */
    private $transactionRepository;
    private $giftCardRepository;
    private $cardEventRepository;

    public function __construct(TransactionRepository $transactionRepo, GiftCardRepository $giftCardRepo, CardEventRepository $cardEventRepo)
    {
        $this->transactionRepository = $transactionRepo;
        $this->giftCardRepository = $giftCardRepo;
        $this->cardEventRepository = $cardEventRepo;
    }

    /**
     * @OA\Get(
     *      path="/transactions",
     *      summary="getTransactionList",
     *      tags={"Transaction"},
     *      description="Get all Transactions",
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
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/Transaction")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
    */
    public function index(Request $request): JsonResponse
    {
        $transactions = $this->transactionRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(TransactionResource::collection($transactions), 'Transactions retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/transactions",
     *      summary="startTransaction",
     *      tags={"Transaction"},
     *      description="Start Transaction",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *            name="Idempotency-Key",
     *            description="Idempotency Key",
     *             @OA\Schema(
     *               type="string"
     *            ),
     *            required=true,
     *            in="header"
     *      ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *           mediaType="multipart/form-data",
     *            @OA\Schema(
     *              @OA\Property(
     *                  property="amount",
     *                  type="integer",
     *                  description="amount of the transaction"
     *              ),
     *              @OA\Property(
     *                  property="gift_card_id",
     *                  type="integer",
     *                  description="gift card id associated with the transaction"
     *              ),
     *            ),
     *         ),
     *       ),
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
     *                  ref="#/components/schemas/Transaction"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
    */
    public function store(CreateTransactionAPIRequest $request): JsonResponse
    {
        $user = $request->user();

        //Input data
        $input = [
            'amount' => $request->get('amount'),
            'gift_card_id' => $request->get('gift_card_id'),
            'user_id' => $user->id
        ];
        $gift_card = $this->giftCardRepository->find($input['gift_card_id']);
            
        if (empty($gift_card) || empty($user)) {
            return $this->sendError('Gift Card or User not found');
        }

        //Get former transactions of this gift card to check limits
        $formerTransaction = $this->transactionRepository->last_transaction_for_gift_card($gift_card->id);
        if ($formerTransaction) {
            if($formerTransaction->status == 'authorized' || $formerTransaction->status == 'captured'){
                return $this->sendError('A transaction is already in progress for this gift card');
            }
        }

        //Creating the transaction
        DB::beginTransaction();
        try{
            //Check amount limit
            if ($input['amount'] > $gift_card->face_amount) {
                //cancel transaction
                $input['status'] = 'failed';

                /** @var Transaction $transaction */
                $transaction = $this->transactionRepository->create($input);
                return $this->sendError('Transaction amount exceeds gift card face amount');
            } 
            
            //Authorize transaction
            $input['status'] = 'authorized';

            /** @var Transaction $transaction */
            $transaction = $this->transactionRepository->create($input);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating transaction : ' . $e->getMessage());
            return $this->sendError('Something went wrong while creating the transaction !');
        }
        
        
        //Send otp code to customer for verification (cache for 30 minutes)
        $otp_code = rand(100000, 999999);
        $content_variables = json_encode(["1" => $otp_code]);
        $content = "";
        $node = new Node(content: $content, contentVariables: $content_variables, level: null, model: null, title: null, body: null);
        $beneficiary = $gift_card->beneficiary;
        //Notify customer via WhatsApp
        if ($beneficiary) {
            $user->notify(new PushBeneficiaryWhatsAppNotification(
                node: $node,
                beneficiary_phone: $beneficiary->phone,
                channel: 'whatsapp'
            ));        
        }
        $user->notify(new PushWhatsAppNotification(
            node: $node,
            channel: 'whatsapp'
        ));

        //Cache store OTP
        Cache::put('otp_code:' . $transaction->id, bcrypt($otp_code), now()->addMinutes(30));
        var_dump('OTP Code for testing purposes: ' . $otp_code);

        $transaction->load(['gift_card']);
        return $this->sendResponse(new TransactionResource($transaction), 'Transaction saved successfully');
    }

    /**
     * @OA\Post(
     *      path="/transactions/confirm/{transaction}",
     *      summary="confirmTransaction",
     *      tags={"Transaction"},
     *      description="Confirm Transaction",
     *      security={{"passport":{}}},
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *           mediaType="multipart/form-data",
     *            @OA\Schema(
     *              @OA\Property(
     *                  property="otp_code",
     *                  type="string",
     *                  description="OTP code for transaction confirmation"
     *              ),
     *              @OA\Property(
     *                  property="action",
     *                  type="string",
     *                  enum={"confirm","cancel"},
     *                  description="Action to perform (confirm or cancel)"
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
     *                  ref="#/components/schemas/Transaction"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
    */
    public function confirm(Transaction $transaction, Request $request): JsonResponse 
    {
        //Input data
        $otp_code = $request->get('otp_code') ?: null;
        $action = $request->get('action'); //confirm or cancel

        //Cancel transaction
        if($action == "cancel") {
            return $this->sendError("Transaction cancelled !");
        }

        //Get former transactions of this gift card to check limits
        $formerTransaction = $this->transactionRepository->last_transaction_for_gift_card($transaction->gift_card_id);
        if ($formerTransaction) {
            if($formerTransaction->status == 'captured'){
                return $this->sendError('This transaction is already captured, contact support !');
            }
        }
        
        //Confirm transaction
        $bcrypt_otp_code = Cache::get('otp_code:' . $transaction->id);
        if (!Hash::check($otp_code, $bcrypt_otp_code)) {
            return $this->sendError('Invalid OTP code !');
        }  
        
        DB::beginTransaction();
        try{
            //Update transaction status to captured
            $transaction->status = 'captured';
            $transaction->save();
            //Update gift card status
            $this->giftCardRepository->update(['status' => 'used'], $transaction->gift_card_id);
            //Log updated gift card
            $this->cardEventRepository->create(['type' => 'used', 'gift_card_id' => $transaction->gift_card_id]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirming transaction : ' . $e->getMessage());
            return $this->sendError('Something went wrong while confirming the transaction !');
        }
        $transaction->load('gift_card');
        return $this->sendResponse(new TransactionResource($transaction), 'Transaction confirmed successfully !');
    }

    /**
     * @OA\Get(
     *      path="/transactions/{id}",
     *      summary="getTransactionItem",
     *      tags={"Transaction"},
     *      description="Get Transaction",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Transaction",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
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
     *                  ref="#/components/schemas/Transaction"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
    */
    public function show($id): JsonResponse
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->find($id);

        if (empty($transaction)) {
            return $this->sendError('Transaction not found');
        }

        return $this->sendResponse(new TransactionResource($transaction), 'Transaction retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/transactions/{id}",
     *      summary="updateTransaction",
     *      tags={"Transaction"},
     *      description="Update Transaction",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Transaction",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Transaction")
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
     *                  ref="#/components/schemas/Transaction"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
    */
    public function update($id, UpdateTransactionAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->find($id);

        if (empty($transaction)) {
            return $this->sendError('Transaction not found');
        }

        $transaction = $this->transactionRepository->update($input, $id);

        return $this->sendResponse(new TransactionResource($transaction), 'Transaction updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/transactions/{id}",
     *      summary="deleteTransaction",
     *      tags={"Transaction"},
     *      description="Delete Transaction",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Transaction",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
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
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
    */
    public function destroy($id): JsonResponse
    {
        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->find($id);

        if (empty($transaction)) {
            return $this->sendError('Transaction not found');
        }

        $transaction->delete();

        return $this->sendSuccess('Transaction deleted successfully');
    }
}
