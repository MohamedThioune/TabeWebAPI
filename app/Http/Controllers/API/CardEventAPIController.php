<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateCardEventAPIRequest;
use App\Http\Requests\API\UpdateCardEventAPIRequest;
use App\Models\CardEvent;
use App\Infrastructure\Persistence\CardEventRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\CardEventResource;

/**
 * Class CardEventController
 */

class CardEventAPIController extends AppBaseController
{
    /** @var  CardEventRepository */
    private $cardEventRepository;

    public function __construct(CardEventRepository $cardEventRepo)
    {
        $this->cardEventRepository = $cardEventRepo;
    }

    /**
     * @OA\Get(
     *      path="/card-events",
     *      summary="getCardEventList",
     *      tags={"CardEvent"},
     *      description="Get all CardEvents",
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
     *                  @OA\Items(ref="#/components/schemas/CardEvent")
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
        $cardEvents = $this->cardEventRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(CardEventResource::collection($cardEvents), 'Card Events retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/card-events",
     *      summary="createCardEvent",
     *      tags={"CardEvent"},
     *      description="Create CardEvent",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/CardEvent")
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
     *                  ref="#/components/schemas/CardEvent"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateCardEventAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $cardEvent = $this->cardEventRepository->create($input);

        return $this->sendResponse(new CardEventResource($cardEvent), 'Card Event saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/card-events/{id}",
     *      summary="getCardEventItem",
     *      tags={"CardEvent"},
     *      description="Get CardEvent",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of CardEvent",
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
     *                  ref="#/components/schemas/CardEvent"
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
        /** @var CardEvent $cardEvent */
        $cardEvent = $this->cardEventRepository->find($id);

        if (empty($cardEvent)) {
            return $this->sendError('Card Event not found');
        }

        return $this->sendResponse(new CardEventResource($cardEvent), 'Card Event retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/card-events/{id}",
     *      summary="updateCardEvent",
     *      tags={"CardEvent"},
     *      description="Update CardEvent",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of CardEvent",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/CardEvent")
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
     *                  ref="#/components/schemas/CardEvent"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateCardEventAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var CardEvent $cardEvent */
        $cardEvent = $this->cardEventRepository->find($id);

        if (empty($cardEvent)) {
            return $this->sendError('Card Event not found');
        }

        $cardEvent = $this->cardEventRepository->update($input, $id);

        return $this->sendResponse(new CardEventResource($cardEvent), 'CardEvent updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/card-events/{id}",
     *      summary="deleteCardEvent",
     *      tags={"CardEvent"},
     *      description="Delete CardEvent",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of CardEvent",
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
        /** @var CardEvent $cardEvent */
        $cardEvent = $this->cardEventRepository->find($id);

        if (empty($cardEvent)) {
            return $this->sendError('Card Event not found');
        }

        $cardEvent->delete();

        return $this->sendSuccess('Card Event deleted successfully');
    }
}
