<?php

namespace App\Http\Controllers\API;

use App\Domain\GiftCards\UseCases\CardFullyGenerated;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\CreateBeneficiaryAPIRequest;
use App\Http\Requests\API\CreateGiftCardAPIRequest;
use App\Http\Requests\API\CustomerAPIRequest;
use App\Http\Requests\API\UpdateGiftCardAPIRequest;
use App\Http\Resources\GiftCardResource;
use App\Infrastructure\Persistence\BeneficiaryRepository;
use App\Infrastructure\Persistence\GiftCardRepository;
use App\Models\GiftCard;
use App\Models\User;
use App\Models\Beneficiary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
    public function index(Request $request): JsonResponse
    {
        $giftCards = $this->giftCardRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(GiftCardResource::collection($giftCards), 'Gift Cards retrieved successfully');
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
        $type = $request->get("belonging_type");
        if($type == 'others'):
            $dto_beneficiary = app(CreateBeneficiaryAPIRequest::class)->validated();
            $beneficiary = ( Beneficiary::where('phone', $dto_beneficiary['phone'])->first()) ?: $this->beneficiaryRepository->create($dto_beneficiary);
        endif;

        $dto = [
            'belonging_type' => $type,
            'pin_hash' => hash::make($request->pin),
            'face_amount' => $request->face_amount,
            'pin_mask' => substr($request->pin, 0, 2),
            'owner_user_id' => $user->id,
            'beneficiary_id' => $beneficiary instanceof Beneficiary ? $beneficiary->id : 0,
            'design_id' => 1
        ];

        //Make all the process here
       $event = $this->cardFullyGenerated->execute($dto);

       //Processing error
       if(!$event):
           return $this->sendError("Something went wrong on the process !", 400);
       endif;
       if(!empty($event->errorMessage)):
           Log::info('DB Process error :', $event->errorMessage);
           return $this->sendError("Error on persisting requests on database", 400);
       endif;

       $giftCard = GiftCard::findOrFail($event->card->getId());

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
