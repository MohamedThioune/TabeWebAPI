<?php

namespace App\Http\Controllers\API;

use App\Domain\GiftCards\Services\BuyCard;
use App\Domain\GiftCards\UseCases\CardFullyGenerated;
use App\Domain\Users\DTO\Node;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateBeneficiaryAPIRequest;
use App\Http\Requests\API\CreateGiftCardAPIRequest;
use App\Http\Requests\API\UpdateGiftCardAPIRequest;
use App\Http\Requests\API\GetGiftCardsAPIRequest;
use App\Http\Resources\GiftCardResource;
use App\Infrastructure\Persistence\BeneficiaryRepository;
use App\Infrastructure\Persistence\GiftCardRepository;
use App\Models\GiftCard;
use App\Models\User;
use App\Models\Beneficiary;
use App\Notifications\PushCardNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class GiftCardController
 */

class GiftCardAPIController extends AppBaseController
{
    /** @var  GiftCardRepository */
    private $giftCardRepository;

    public function __construct(GiftCardRepository $giftCardRepo, private BeneficiaryRepository $beneficiaryRepository, private CardFullyGenerated $cardFullyGenerated, private BuyCard $payment)
    {
        $this->giftCardRepository = $giftCardRepo;
        $this->cardFullyGenerated = $cardFullyGenerated;
    }

    public function detached_index(User $user, array $search, Request $request, int $perPage = 6) : array
    {
        $query_cards = $this->giftCardRepository->allQuery(
            $search,
            $request->get('skip'),
            $request->get('limit')
        );

        $count_cards = $this->giftCardRepository->countQuery($query_cards);
        $paginated_users = $this->giftCardRepository->paginate($query_cards, $perPage);
        $infos = [
            'gift_cards' => GiftCardResource::collection($paginated_users),
            'count' => $count_cards
        ];

        if($request->get('with_summary')){
            $infos['total_amount_user'] = $this->giftCardRepository->all($search,null, null)->sum('face_amount');
        }

        return $infos;
    }
    /**
     * @OA\Get(
     *      path="/gift-cards/users/{user_id}",
     *      summary="getGiftCardList",
     *      tags={"GiftCard"},
     *      description="Get all GiftCards | Only for admin !!",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *           name="status",
     *           in="query",
     *           description="Filter users by status ('active', 'inactive', 'used', 'expired')",
     *           required=false,
     *           @OA\Schema(
     *               type="string",
     *               enum={"inactive","active", "used", "expired"}
     *           )
     *      ),
     *      @OA\Parameter(
     *           name="belonging_type",
     *           in="query",
     *           description="Filter users by belonging type (myself, others)",
     *           required=false,
     *           @OA\Schema(
     *               type="string",
     *               enum={"myself", "others"}
     *           )
     *       ),
     *       @OA\Parameter(
     *            name="type",
     *            in="query",
     *            description="Filter users by type (physical, digital)",
     *            required=false,
     *            @OA\Schema(
     *                type="string",
     *                enum={"physical", "digital"}
     *            )
     *       ),
     *       @OA\Parameter(
     *            name="with_summary",
     *            in="query",
     *            description="Get the total amounts of the user (0:inactive, 1:active)",
     *            required=false,
     *            @OA\Schema(
     *               type="integer",
     *               enum={0,1}
     *            )
     *      ),
     *      @OA\Parameter(
     *           name="skip",
     *           in="query",
     *           description="Skip",
     *           required=false,
     *           @OA\Schema(
     *               type="integer"
     *           )
     *       ),
     *       @OA\Parameter(
     *            name="limit",
     *            in="query",
     *            description="Limit",
     *            required=false,
     *            @OA\Schema(
     *                type="integer"
     *            )
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
     *                  @OA\Items(ref="#/components/schemas/GiftCard")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function index(User $user, GetGiftCardsAPIRequest $request): JsonResponse
    {
        $search = $request->except(['skip', 'limit']);
        $search['owner_user_id'] = $user->id;
        $perPage = $request->get('per_page', 6);

        $infos = $this->detached_index($user, $search, $request, $perPage);

        return $this->sendResponse($infos, 'Gift Cards retrieved successfully');
    }
    /**
     * @OA\Get(
     *      path="/gift-cards",
     *      summary="getGiftCardListPerUser",
     *      tags={"GiftCard"},
     *      description="Get all GiftCards per user",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *            name="status",
     *            in="query",
     *            description="Filter users by status ('active', 'inactive', 'used', 'expired')",
     *            required=false,
     *            @OA\Schema(
     *                type="string",
     *                enum={"active", "inactive", "used", "expired"}
     *            )
     *       ),
     *       @OA\Parameter(
     *            name="belonging_type",
     *            in="query",
     *            description="Filter users by belonging type (myself, others)",
     *            required=false,
     *            @OA\Schema(
     *                type="string",
     *                enum={"myself", "others"}
     *            )
     *        ),
     *        @OA\Parameter(
     *             name="type",
     *             in="query",
     *             description="Filter users by type (physical, digital)",
     *             required=false,
     *             @OA\Schema(
     *                 type="string",
     *                 enum={"physical", "digital"}
     *             )
     *        ),
     *        @OA\Parameter(
     *             name="with_summary",
     *             in="query",
     *             description="Get the total amounts of the user (0:inactive, 1:active)",
     *             required=false,
     *             @OA\Schema(
     *                  type="integer",
     *                 enum={0,1}
     *             )
     *       ),
     *       @OA\Parameter(
     *            name="skip",
     *            in="query",
     *            description="Skip",
     *            required=false,
     *            @OA\Schema(
     *                type="integer"
     *            )
     *        ),
     *       @OA\Parameter(
     *             name="limit",
     *             in="query",
     *             description="Limit",
     *             required=false,
     *             @OA\Schema(
     *                 type="integer"
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
     *                  @OA\Items(ref="#/components/schemas/GiftCard")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function indexAuth(GetGiftCardsAPIRequest $request): JsonResponse
    {
        $user = $request->user();
        //Test user instance of model user
        $search = $request->except(['skip', 'limit']);
        $search['owner_user_id'] = $user->id;
        $perPage = $request->get('per_page', 6);

        $infos = $this->detached_index($user, $search, $request, $perPage);

        return $this->sendResponse($infos, 'Gift Cards retrieved successfully');
    }
    public function index_all(GetGiftCardsAPIRequest $request): JsonResponse
    {
        $giftCards = $this->giftCardRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        $infos = [
            'gift_cards' => GiftCardResource::collection($giftCards),
            'count' => !empty($giftCards) ? count($giftCards) : 0,
        ];

        return $this->sendResponse($infos, 'Gift Cards retrieved successfully');
    }

    public function detached_store(string $belonging_type, User $user, Request $request, ?Beneficiary $beneficiary): mixed
    {
        $dto = [
            'belonging_type' => $belonging_type,
            'type' => $request->type,
            'face_amount' => $request->face_amount,
            'owner_user_id' => $user->id,
            'beneficiary_id' => $beneficiary instanceof Beneficiary ? $beneficiary->id : null,
            'design_id' => $request->design_id,
        ];

        //Make all the process here
        $event = $this->cardFullyGenerated->execute($dto);

        //Processing error
        if(!$event):
            return ["error" => "Something went wrong on the process !"];
        endif;

        if(!empty($event->errorMessage)):
            Log::info('DB Process error :', $event->errorMessage);
            return ["error" => "Error on persisting requests on database"];
        endif;

        $giftCard = GiftCard::findOrFail($event->card->getId());

        return $giftCard;
    }

    public function store(User $user, CreateGiftCardAPIRequest $request): JsonResponse
    {
        $belonging_type = $request->get("belonging_type");
        $beneficiary = null;
        if($belonging_type == 'others'):
            $dto_beneficiary = app(CreateBeneficiaryAPIRequest::class)->validated();
            $beneficiary = $this->beneficiaryRepository->create($dto_beneficiary);
        endif;

        $giftCard = $this->detached_store($belonging_type, $user, $request, $beneficiary);

        //Catch errors
        if(isset($giftCard['error']))
            return $this->sendError($giftCard['error'], 401);

        $checkout = $this->payment->execute($giftCard);
        if(!$checkout)
            return $this->sendError("Something went wrong with the provider, please try again later !", 401);

        $infos = [
            'gift_card' => new GiftCardResource($giftCard),
            'checkout' => $checkout,
        ];

        return $this->sendResponse($infos, 'Gift Card saved successfully !');
    }

    /**
     * @OA\Post(
     *      path="/gift-cards",
     *      summary="createGiftCard",
     *      tags={"GiftCard"},
     *      description="Create GiftCard",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *            name="Idempotency-Key",
     *            description="Idempotency Key",
     *             @OA\Schema(
     *               type="string"
     *            ),
     *            required=true,
     *            in="header"
     *        ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *           mediaType="multipart/form-data",
     *            @OA\Schema(
     *              @OA\Property(
     *                  property="belonging_type",
     *                  type="string",
     *                  enum={"myself", "others"}
     *              ),
     *              @OA\Property(
     *                  property="type",
     *                  type="string",
     *                  enum={"physical", "digital"}
     *              ),
     *              @OA\Property(
     *                  property="face_amount",
     *                  type="integer",
     *              ),
     *              @OA\Property(
     *                    property="full_name",
     *                    type="string",
     *              ),
     *              @OA\Property(
     *                     property="phone",
     *                     type="string",
     *              ),
     *              @OA\Property(
     *                      property="design_id",
     *                      type="integer",
     *                      enum={1,2,3,4}
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
    public function storeAuth(CreateGiftCardAPIRequest $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->get("belonging_type");
        $beneficiary = null;
        if($type == 'others'):
            $dto_beneficiary = app(CreateBeneficiaryAPIRequest::class)->validated();
            $beneficiary = ( Beneficiary::where('phone', $dto_beneficiary['phone'])->first()) ?: $this->beneficiaryRepository->create($dto_beneficiary);
        endif;

        $giftCard = $this->detached_store($type, $user, $request, $beneficiary);

        //Catch errors
        if(isset($giftCard['error']))
            return $this->sendError($giftCard['error'], 401);

        $checkout = $this->payment->execute($giftCard);
        if(!$checkout)
            return $this->sendError("Something went wrong with the provider, please try again later !", 401);

        $infos = [
            'gift_card' => new GiftCardResource($giftCard),
            'checkout' => $checkout,
        ];

        return $this->sendResponse($infos, 'Gift Card saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/gift-cards/{id}",
     *      summary="getGiftCardItem",
     *      tags={"GiftCard"},
     *      description="Get GiftCard",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="id of GiftCard",
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
    public function show($id): JsonResponse
    {
        /** @var GiftCard $giftCard */
        $giftCard = $this->giftCardRepository->find($id);

        if (empty($giftCard)) {
            return $this->sendError('Gift Card not found');
        }

        return $this->sendResponse(new GiftCardResource($giftCard), 'Gift Card retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/gift-cards/{id}",
     *      summary="updateGiftCard",
     *      tags={"GiftCard"},
     *      description="Update GiftCard | Only for a admin !!",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="id of GiftCard",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/GiftCard")
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
    public function update($id, UpdateGiftCardAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var GiftCard $giftCard */
        $giftCard = $this->giftCardRepository->find($id);

        if (empty($giftCard)) {
            return $this->sendError('Gift Card not found');
        }

        $giftCard = $this->giftCardRepository->update($input, $id);

        return $this->sendResponse(new GiftCardResource($giftCard), 'GiftCard updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/gift-cards/{id}",
     *      summary="deleteGiftCard",
     *      tags={"GiftCard"},
     *      description="Delete GiftCard | Only for a admin !!",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="id of GiftCard",
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
        /** @var GiftCard $giftCard */
        $giftCard = $this->giftCardRepository->find($id);

        if (empty($giftCard)) {
            return $this->sendError('Gift Card not found');
        }

        $giftCard->delete();

        return $this->sendSuccess('Gift Card deleted successfully');
    }

    /**
     * @OA\Put(
     *      path="/gift-cards/share/{giftCard}",
     *      summary="ShareGiftCard",
     *      tags={"GiftCard"},
     *      description="Share GiftCard",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *          name="giftCard",
     *          description="id of GiftCard",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/GiftCard")
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
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function share(GiftCard $giftCard): JsonResponse
    {
        //Check card status 
        if($giftCard->status != 'active'){
            return $this->sendError('Gift Card is not active and cannot be shared', 401);
        }

        // Get beneficiary
        $beneficiary = $giftCard->beneficiary;
        if(!$beneficiary){
            return $this->sendError('Gift Card has no beneficiary to share with', 401);
        }

        // Get the owner and amount 
        $user = $giftCard->user;
        $customer_owner = $user?->customer()->first();
        $owner_full_name = $customer_owner->first_name . ' ' . $customer_owner->last_name;
        $amount = $giftCard->face_amount;
        
        $content_variables = json_encode(["1" => $beneficiary->full_name, "2" => $owner_full_name, "3" => (string)$amount]);

        $content = "";
        $node = new Node(
            content : $content,
            contentVariables: $content_variables,
            level: null,
            model: null,
            title: null,
            body: null
        );

        //Notify the user
        $user->notify(new PushCardNotification(
            node: $node,
            beneficiary_phone: $beneficiary->phone,
            channel: 'whatsapp'
        ));

        return $this->sendSuccess('Gift Card shared successfully !');
    }
}
