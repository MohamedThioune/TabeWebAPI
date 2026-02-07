<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateTransactionAPIRequest;
use App\Http\Requests\API\UpdateTransactionAPIRequest;
use App\Http\Requests\API\ConfirmTransactionAPIRequest;
use App\Http\Requests\API\GetTransactionAPIRequest;
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
use App\Notifications\PushBeneficiarySMSNotification;
use App\Notifications\PushSMSNotification;
use App\Domain\Users\DTO\Node;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

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

    public function collect(array $search = [], Request $request, int $perPage = 9): array
    {
        $query_transaction = $this->transactionRepository->allQuery(
            $search,
            $request->get('skip'),
            $request->get('limit')
        );

        $transactions = $query_transaction->paginate($perPage);

        return [
            'transactions' => TransactionResource::collection($transactions->load(['gift_card'])),
            'count' => $transactions->total(),
            'pagination' => [
                'previous_page' => $transactions->currentPage() - 1 > 0 ? $transactions->currentPage() - 1 : null,
                'current_page' => $transactions->currentPage(),
                'next_page' => $transactions->hasMorePages() ? $transactions->currentPage() + 1 : null,
                'total_pages' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total_items' => $transactions->total(),
            ]
        ];
       
    }

    /**
     * @OA\Get(
     *      path="/transactions",
     *      summary="getTransactionListPerUser",
     *      tags={"Transaction"},
     *      description="Get all Transactions per user",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *            name="page",
     *            in="query",
     *            description="Page",
     *            required=false,
     *            @OA\Schema(
     *                type="integer"
     *            )
     *       ),
     *       @OA\Parameter(
     *            name="per_page",
     *            in="query",
     *            description="Per Page",
     *            required=false,
     *            @OA\Schema(
     *                type="integer"
     *            )
     *       ),
     *       @OA\Parameter(
     *            name="skip",
     *            in="query",
     *            description="Skip",
     *            required=false,
     *            @OA\Schema(
     *                type="integer"
     *            )
     *       ),
     *       @OA\Parameter(
     *             name="limit",
     *             in="query",
     *             description="Limit",
     *             required=false,
     *             @OA\Schema(
     *                 type="integer"
     *             )
     *      ),
     *      @OA\Parameter(
     *              name="status",
     *              in="query",
     *              description="Filter by status",
     *              required=false,
     *              @OA\Schema(
     *                  type="string",
     *                  enum={"authorized", "captured", "cancelled", "refunded", "failed"}
     *              )
     *      ),
     *      @OA\Parameter(
     *              name="filter_by_date",
     *              in="query",
     *              description="Filter by date",
     *              required=false,
     *              @OA\Schema(
     *                  type="string",
     *                  enum={"today", "week", "month", "year"}
     *              )
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
    public function index(GetTransactionAPIRequest $request): JsonResponse
    {
        $user = $request->user();
        $search = $request->except(['skip', 'limit', 'page', 'per_page']);
        $search['user_id'] = $user->id;
        $perPage = $request->get('per_page') ?: 9;

        $infoTransactions = $this->collect($search, $request, $perPage);

        return $this->sendResponse($infoTransactions, 'Transactions retrieved successfully !');
    }

    /**
     * @OA\Get(
     *      path="/transactions/all",
     *      summary="getAllTransactionList",
     *      tags={"Transaction"},
     *      description="Get all Transactions | Only for admin !!",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *            name="page",
     *            in="query",
     *            description="Page",
     *            required=false,
     *            @OA\Schema(
     *                type="integer"
     *            )
     *       ),
     *       @OA\Parameter(
     *            name="per_page",
     *            in="query",
     *            description="Per Page",
     *            required=false,
     *            @OA\Schema(
     *                type="integer"
     *            )
     *       ),
     *       @OA\Parameter(
     *            name="skip",
     *            in="query",
     *            description="Skip",
     *            required=false,
     *            @OA\Schema(
     *                type="integer"
     *            )
     *       ),
     *       @OA\Parameter(
     *             name="limit",
     *             in="query",
     *             description="Limit",
     *             required=false,
     *             @OA\Schema(
     *                 type="integer"
     *             )
     *      ),
     *      @OA\Parameter(
     *              name="status",
     *              in="query",
     *              description="Filter by status",
     *              required=false,
     *              @OA\Schema(
     *                  type="string",
     *                  enum={"authorized", "captured", "cancelled", "refunded", "failed"}
     *              )
     *      ),
     *      @OA\Parameter(
     *              name="filter_by_date",
     *              in="query",
     *              description="Filter by date",
     *              required=false,
     *              @OA\Schema(
     *                  type="string",
     *                  enum={"today", "week", "month", "year"}
     *              )
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
    public function indexAll(GetTransactionAPIRequest $request): JsonResponse
    {
        $search = $request->except(['skip', 'limit', 'page', 'per_page']);
        $perPage = $request->get('per_page') ?: 9;

        $infoTransactions = $this->collect($search, $request, $perPage);
        $infoTransactions['stats'] = [
            'authorized' => $this->transactionRepository->getAuthorizedTransactionsByUser()->count(),
            'captured' => $this->transactionRepository->getCapturedTransactionsByUser()->count(),
            'refunded' => $this->transactionRepository->getRefundedTransactionsByUser()->count(),
            'failed' => $this->transactionRepository->getFailedTransactionsByUser()->count()
        ];
        return $this->sendResponse($infoTransactions, 'Transactions retrieved successfully !');
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
            if ($formerTransaction->status == 'captured' || $formerTransaction->status == 'refunded') {
                return $this->sendError('A transaction is already captured/refunded for this card, contact support !');
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

            //Update gift card status to inactive
            $used_status = "used";
            $this->giftCardRepository->update(['status' => $used_status, 'updated_at' => now()], $gift_card->id);
            //Log updated gift card
            $this->cardEventRepository->create(['type' => $used_status, 'gift_card_id' => $gift_card->id]);

            //Delete former authorized transaction / Create a new one
            $deleteAuthorizedTransaction = $this->transactionRepository->getAuthorizedTransactionsByUser()->delete();
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
        $content = "Votre code OTP pour confirmer la transaction avec le magasin partenaire est : {$otp_code}.";
        // $node = new Node(content: $content, contentVariables: $content_variables, level: null, model: null, title: null, body: null);
        $node_customer = new Node(content: $content, contentVariables: $content_variables, level: null, model: null, title: null, body: null);
        $beneficiary = $gift_card->beneficiary;
        $owner = $gift_card->user;
        //Notify customer via WhatsApp
        if ($beneficiary) {
            $owner->notify(new PushBeneficiarySMSNotification(
                node: $node_customer,
                beneficiary_phone: $beneficiary->phone,
                channel: 'sms'
            ));        
        }
        else {
            $owner->notify(new PushSMSNotification(
                node: $node_customer,
                channel: 'sms'
            ));

        // Notify partner 
        /** Instructions code here ! */ 
        }

        //Cache store OTP
        Cache::put('otp_code:' . $transaction->id, bcrypt($otp_code), now()->addMinutes(30));

        //Prepare response
        $transaction->load(['gift_card']);
        $infos['otp_code'] = $otp_code;
        $infos['transaction'] = new TransactionResource($transaction);

        return $this->sendResponse($infos, 'Transaction saved successfully');
    }

    /**
     * @OA\Post(
     *      path="/transactions/confirm/{transaction}",
     *      summary="confirmTransaction",
     *      tags={"Transaction"},
     *      description="Confirm Transaction",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *            name="transaction",
     *            in="path",
     *            description="id of Transaction",
     *            required=true,
     *            @OA\Schema(
     *                type="string"
     *            )
     *      ),
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
    public function confirm(Transaction $transaction, ConfirmTransactionAPIRequest $request): JsonResponse 
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
            if($formerTransaction->status == 'captured' || $formerTransaction->status == 'refunded') {
                return $this->sendError('A transaction is already captured/refunded for this card, contact support !');
            }
        }
        
        //Confirm transaction
        $bcrypt_otp_code = Cache::get('otp_code:' . $transaction->id);
        if (!Hash::check($otp_code, $bcrypt_otp_code)) {
            return $this->sendError('Invalid OTP code !');
        }  
        
        DB::beginTransaction();
        try{
            //Create a transaction with a status 'captured'
            $new_transaction = $transaction->replicate([
                'status',
                'amount',
                'currency',
                'user_id',
                'gift_card_id',
            ]);
            $new_transaction->status = 'captured';
            $new_transaction->parent_transaction_id = $transaction->id;
            $new_transaction->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirming transaction : ' . $e->getMessage());
            return $this->sendError('Something went wrong while confirming the transaction !');
        }

        /* Notify parties (owner, beneficiary, shop) */
        $content_variables = json_encode(["1" => '']);
        $content = "";
        $node = new Node(content: $content, contentVariables: $content_variables, level: null, model: null, title: null, body: null);
        //Notify owner via WhatsApp
     
        //Notify beneficiary via WhatsApp
 
        //Notify shop via WhatsApp      

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
