<?php

namespace App\Http\Controllers\API;

use App\Events\ReceivePayoutProcessed;
use App\Http\Requests\API\CreatePayoutAPIRequest;
use App\Http\Requests\API\UpdatePayoutAPIRequest;
use App\Http\Requests\API\GetPayoutAPIRequest;
use App\Models\Payout;
use App\Models\User;
use App\Infrastructure\Persistence\PayoutRepository;
use App\Infrastructure\Persistence\TransactionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\PayoutResource;
use App\Http\Resources\TransactionResource;
use App\Domain\GiftCards\Services\Payout as PayoutService;
use App\Infrastructure\External\Payment\DTO\PaymentResponseDTO;
use App\Infrastructure\External\Payment\PaymentGateway;
use App\Notifications\TransactionNotification;
use App\Domain\Users\DTO\Node;
use App\Jobs\ReimbursePayment;

/**
 * Class PayoutController
*/
class PayoutAPIController extends AppBaseController
{

    public function __construct(private PayoutRepository $payoutRepo, private TransactionRepository $transactionRepo, private PayoutService $payoutSes){ }

    public function collect(array $search = [], Request $request, int $perPage = 9): array
    {
        $query_payout = $this->payoutRepo->allQuery(
            $search,
            $request->get('skip'),
            $request->get('limit')
        );

        $payouts = $query_payout->paginate($perPage);
        $payouts->load('transactions');

        return [
            'payouts' => PayoutResource::collection($payouts),
            'count' => $payouts->total(),
            'pagination' => [
                'previous_page' => $payouts->currentPage() - 1 > 0 ? $payouts->currentPage() - 1 : null,
                'current_page' => $payouts->currentPage(),
                'next_page' => $payouts->hasMorePages() ? $payouts->currentPage() + 1 : null,
                'total_pages' => $payouts->lastPage(),
                'per_page' => $payouts->perPage(),
                'total_items' => $payouts->total(),
            ]
        ];
    }
    
    /**
     * @OA\Get(
     *      path="/payouts",
     *      summary="getPayoutList",
     *      tags={"Payout"},
     *      description="Get all Payouts by the user",
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
     *                  enum={"authorized", "completed", "cancelled", "failed"}
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
     *     @OA\Parameter(
     *             name="show_transactions",
     *             in="query",
     *             description="Show transactions in response",
     *             required=false,
     *             @OA\Schema(
     *                 type="integer",
     *                 enum={1, 0}
     *             )
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
    public function index(GetPayoutAPIRequest $request): JsonResponse
    {
        $user = $request->user();

        $search = $request->except(['skip', 'limit', 'page', 'per_page']);
        $search['user_id'] = $user->id;
        $perPage = $request->get('per_page') ?: 9;

        $infoPayouts = $this->collect($search, $request, $perPage);

        return $this->sendResponse($infoPayouts, 'Payouts retrieved successfully');
    }

    /**
     * @OA\Get(
     *      path="/payouts/all",
     *      summary="getAllPayoutList",
     *      tags={"Payout"},
     *      description="Get all Payouts | Only for admin !!",
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
     *                  enum={"authorized", "completed", "cancelled", "failed"}
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
     *     @OA\Parameter(
     *             name="show_transactions",
     *             in="query",
     *             description="Show transactions in response",
     *             required=false,
     *             @OA\Schema(
     *                 type="integer",
     *                 enum={1, 0}
     *             )
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
    public function indexAll(GetPayoutAPIRequest $request): JsonResponse
    {
        $search = $request->except(['skip', 'limit', 'page', 'per_page']);
        $perPage = $request->get('per_page') ?: 9;

        $infoPayouts = $this->collect($search, $request, $perPage);
        $infoPayouts['stats'] = [
            'authorized' => $this->payoutRepo->getPayoutInProgressByUser()->count(),
            'completed' => $this->payoutRepo->getPayoutCompletedByUser()->count(),
            'cancelled' => $this->payoutRepo->getPayoutCancelledByUser()->count(),
            'failed' => $this->payoutRepo->getPayoutFailedByUser()->count()
        ];

        return $this->sendResponse($infoPayouts, 'Payouts retrieved successfully');
    }
    
    /**
     * @OA\Post(
     *      path="/payouts/before/request",
     *      summary="beforeRequestPayout",
     *      tags={"Payout"},
     *      description="Before requesting a Payout",
     *      security={{"passport":{}}},
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
    public function beforeRequest(Request $request): JsonResponse
    {
        $user = $request->user();
        
        //Check if another payout is already in progress
        $existing_payout = $this->payoutRepo->getPayoutInProgressByUser($user->id);
        if ($existing_payout->exists()) {
            return $this->sendError('Another payout is already in progress...');
        }

        //List all transactions in progress for the user
        $query_transactions = $this->transactionRepo->getCapturedTransactionsByUser($user->id);
        if (!$query_transactions->exists()) 
            return $this->sendError('No transactions remaining for a payout !');

        $transactions_authorized = $query_transactions->get();
        $transactions_count = count($transactions_authorized);

        $data = [
            'total' => $transactions_count,
            'transactions' => TransactionResource::collection($transactions_authorized)
        ];

        return $this->sendResponse($data, 'Remaining transactions retrieved successfully !');
    }

    /**
     * @OA\Post(
     *      path="/payouts/request",
     *      summary="requestPayout",
     *      tags={"Payout"},
     *      description="Request a Payout",
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
     *      @OA\Parameter(
     *            name="show_transactions",
     *            description="Show transactions in response",
     *             @OA\Schema(
     *               type="integer",
     *               enum={0,1}
     *            ),
     *            required=false,
     *            in="query"
     *      ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *           mediaType="multipart/form-data",
     *            @OA\Schema(
     *              @OA\Property(
     *                  property="withdraw_mode",
     *                  type="string",
     *                  description="Withdraw mode for the payout",
     *                  enum={"paydunya","orange-money-senegal","wave-senegal","expresso-senegal","free-money-senegal"},
     *              ),
     *               @OA\Property(
     *                  property="commentary",
     *                  type="string",
     *                  description="Commentary made about the payout",
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
     *                  ref="#/components/schemas/Payout"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
    */
    public function request(CreatePayoutAPIRequest $request): JsonResponse
    {
        $user = $request->user();
        
        //Check if another payout is already in progress
        $existing_payout = $this->payoutRepo->getPayoutInProgressByUser($user->id); 
        if ($existing_payout->exists()) {
            return $this->sendError('Another payout is already in progress...');
        }

        //Get captured transactions (not yet refunded) for the user
        $query_transactions = $this->transactionRepo->getCapturedTransactionsByUser($user->id);
        if (!$query_transactions->exists()) 
            return $this->sendError('No transactions remaining for a payout !');
        
        $gross_amount = $query_transactions->sum('amount');
        $transactions = $query_transactions->get();
        $phone_number = substr($user->phone, -9); //Extract the last 9 digits of the phone number for the payout
        //Initiate the payout process
        $payout = $this->payoutSes->initiatePayout(
            phone_number: $phone_number,
            gross_amount: $gross_amount,
            withdraw_mode: $request->get('withdraw_mode'),
            user: $user,
            transactions: $transactions
        ); 

        if(!$payout)
            return $this->sendError('Something went wrong while initiating the payout, please try again later !');

        //Load relations
        if($request->get('show_transactions'))
            $payout->load('transactions');

        //Notify admin via WhatsApp
        $admin = User::role('admin')->first();
        $content = "Nouvelle demande de retrait de {$gross_amount} FCFA initiée par l'utilisateur avec le numéro de téléphone {$user->phone}.";
        $node_owner = new Node(content: $content, contentVariables: null, level: "Important", model: "transaction", title: "Nouvelle demande de remboursement !", body: $content);
        $admin->notify(new TransactionNotification(node: $node_owner, channel: 'whatsApp'));        
        //Broadcast event for the customer and admin
        event(new ReceivePayoutProcessed($payout, $admin));

        return $this->sendResponse(new PayoutResource($payout), 'Payout saved successfully');
    }

    public function submit(Request $request, Payout $payout): JsonResponse
    {
        $user = $request->user();
        $existing_payout = $this->payoutRepo->getPayoutInProgressByUser($user->id); 
        if ($existing_payout->exists()) {
            return $this->sendError('Another payout is already in progress...');
        }

        if ($payout->status !== 'authorized') {   
            return $this->sendError('Only authorized payouts can be processed !');
        }

        // Process the payout
        ReimbursePayment::dispatch($user, $payout, null, $this->payoutSes);
    
        return $this->sendSuccess('Payout is being processed !');
    }

    /**
     * @OA\Get(
     *      path="/payouts/{id}",
     *      summary="getPayoutItem",
     *      tags={"Payout"},
     *      description="Get Payout",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Payout",
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
     *                  ref="#/components/schemas/Payout"
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
        /** @var Payout $payout */
        $payout = $this->payoutRepo->find($id);

        if (empty($payout)) {
            return $this->sendError('Payout not found');
        }

        return $this->sendResponse(new PayoutResource($payout), 'Payout retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/payouts/{id}",
     *      summary="updatePayout",
     *      tags={"Payout"},
     *      description="Update Payout",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Payout",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Payout")
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
     *                  ref="#/components/schemas/Payout"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdatePayoutAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Payout $payout */
        $payout = $this->payoutRepo->find($id);

        if (empty($payout)) {
            return $this->sendError('Payout not found');
        }

        $payout = $this->payoutRepo->update($input, $id);

        return $this->sendResponse(new PayoutResource($payout), 'Payout updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/payouts/{id}",
     *      summary="deletePayout",
     *      tags={"Payout"},
     *      description="Delete Payout",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Payout",
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
        /** @var Payout $payout */
        $payout = $this->payoutRepo->find($id);

        if (empty($payout)) {
            return $this->sendError('Payout not found');
        }

        $payout->delete();

        return $this->sendSuccess('Payout deleted successfully');
    }
}
