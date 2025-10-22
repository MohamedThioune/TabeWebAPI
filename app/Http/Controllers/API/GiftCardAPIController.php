<?php

namespace App\Http\Controllers\API;

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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

/**
 * Class GiftCardController
 */

class GiftCardAPIController extends AppBaseController
{
    /** @var  GiftCardRepository */
    private $giftCardRepository;

    public function __construct(GiftCardRepository $giftCardRepo, private BeneficiaryRepository $beneficiaryRepository, private CardFullyGenerated $cardFullyGenerated)
    {
        $this->giftCardRepository = $giftCardRepo;
        $this->cardFullyGenerated = $cardFullyGenerated;
    }

    public function detached_index(User $user, array $search, Request $request) : array
    {
        $giftCards = $this->giftCardRepository->all(
            $search,
            $request->get('skip'),
            $request->get('limit')
        );

        $infos = [
            'gift_cards' => GiftCardResource::collection($giftCards),
            'count' => !empty($giftCards) ? count($giftCards) : 0
        ];

        if($request->get('with_summary')){
            $infos['total_amount_user'] = $this->giftCardRepository->all(['owner_user_id', $user->id])->sum('face_amount');
        }

        return $infos;
    }
    /**
     * @OA\Get(
     *      path="/gift-cards/users/{user_id}",
     *      summary="getGiftCardListPerUser",
     *      tags={"GiftCard"},
     *      description="Get all GiftCards per user",
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

        $infos = $this->detached_index($user, $search, $request);

        return $this->sendResponse($infos, 'Gift Cards retrieved successfully');
    }
    public function indexAuth(GetGiftCardsAPIRequest $request): JsonResponse
    {
        $user = $request->user();
        //Test user instance of model user
        $search = $request->except(['skip', 'limit']);
        $search['owner_user_id'] = $user->id;

        $infos = $this->detached_index($user, $search, $request);

        return $this->sendResponse($infos, 'Gift Cards retrieved successfully');
    }

    /**
     * @OA\Get(
     *      path="/gift-cards",
     *      summary="getGiftCardList",
     *      tags={"GiftCard"},
     *      description="Get all GiftCards",
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

        //Notify via whatsApp
        if($belonging_type == "others"){
            $format_beneficiary = $beneficiary->full_name;
            $format_customer = !empty($user->customer) ? $user->customer[0]->first_name . ' ' . $user->customer[0]->last_name : '';
            $format_amount = Number::format($dto['face_amount'], locale: 'sv'); //Swedish format (ex: 10 000)
            $content_variables = json_encode(["1" => $format_beneficiary, "2" => $format_customer, "3" => $format_amount]);
            $body = "";
            $node = new Node(
                content: $body,
                contentVariables: $content_variables,
                level: "Info",
                model: "card",
                title: "Nouvelle carte créée",
                body: "Une carte-cadeau de {$format_amount} FCFA a été créée avec succès pour {$format_beneficiary}"
            );
            $user->notify(new PushCardNotification($node, $beneficiary->phone, "whatsapp"));
        }

        return $giftCard;
    }

    /**
     * @OA\Post(
     *      path="/gift-cards",
     *      summary="createGiftCard",
     *      tags={"GiftCard"},
     *      description="Create GiftCard",
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
    public function store(User $user, CreateGiftCardAPIRequest $request): JsonResponse
    {
        $belonging_type = $request->get("belonging_type");
        $beneficiary = null;
        if($belonging_type == 'others'):
            $dto_beneficiary = app(CreateBeneficiaryAPIRequest::class)->validated();
            $beneficiary = ( Beneficiary::where('phone', $dto_beneficiary['phone'])->first()) ?: $this->beneficiaryRepository->create($dto_beneficiary);
        endif;

        $giftCard = $this->detached_store($belonging_type, $user, $request, $beneficiary);

        //Catch errors
        if(isset($giftCard['error']))
            return $this->sendError($giftCard['error'], 401);

        return $this->sendResponse(new GiftCardResource($giftCard), 'Gift Card saved successfully');
    }

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

        return $this->sendResponse(new GiftCardResource($giftCard), 'Gift Card saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/gift-cards/{id}",
     *      summary="getGiftCardItem",
     *      tags={"GiftCard"},
     *      description="Get GiftCard",
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
     *      description="Update GiftCard",
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
     *      description="Delete GiftCard",
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
}
