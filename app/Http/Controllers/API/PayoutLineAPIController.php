<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreatePayoutLineAPIRequest;
use App\Http\Requests\API\UpdatePayoutLineAPIRequest;
use App\Models\PayoutLine;
use App\Infrastructure\Persistence\PayoutLineRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\PayoutLineResource;

/**
 * Class PayoutLineController
 */

class PayoutLineAPIController extends AppBaseController
{
    /** @var  PayoutLineRepository */
    private $payoutLineRepository;

    public function __construct(PayoutLineRepository $payoutLineRepo)
    {
        $this->payoutLineRepository = $payoutLineRepo;
    }

    /**
     * @OA\Get(
     *      path="/payout-lines",
     *      summary="getPayoutLineList",
     *      tags={"PayoutLine"},
     *      description="Get all PayoutLines",
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
     *                  @OA\Items(ref="#/components/schemas/PayoutLine")
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
        $payoutLines = $this->payoutLineRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(PayoutLineResource::collection($payoutLines), 'Payout Lines retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/payout-lines",
     *      summary="createPayoutLine",
     *      tags={"PayoutLine"},
     *      description="Create PayoutLine",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/PayoutLine")
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
     *                  ref="#/components/schemas/PayoutLine"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreatePayoutLineAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $payoutLine = $this->payoutLineRepository->create($input);

        return $this->sendResponse(new PayoutLineResource($payoutLine), 'Payout Line saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/payout-lines/{id}",
     *      summary="getPayoutLineItem",
     *      tags={"PayoutLine"},
     *      description="Get PayoutLine",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of PayoutLine",
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
     *                  ref="#/components/schemas/PayoutLine"
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
        /** @var PayoutLine $payoutLine */
        $payoutLine = $this->payoutLineRepository->find($id);

        if (empty($payoutLine)) {
            return $this->sendError('Payout Line not found');
        }

        return $this->sendResponse(new PayoutLineResource($payoutLine), 'Payout Line retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/payout-lines/{id}",
     *      summary="updatePayoutLine",
     *      tags={"PayoutLine"},
     *      description="Update PayoutLine",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of PayoutLine",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/PayoutLine")
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
     *                  ref="#/components/schemas/PayoutLine"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdatePayoutLineAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var PayoutLine $payoutLine */
        $payoutLine = $this->payoutLineRepository->find($id);

        if (empty($payoutLine)) {
            return $this->sendError('Payout Line not found');
        }

        $payoutLine = $this->payoutLineRepository->update($input, $id);

        return $this->sendResponse(new PayoutLineResource($payoutLine), 'PayoutLine updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/payout-lines/{id}",
     *      summary="deletePayoutLine",
     *      tags={"PayoutLine"},
     *      description="Delete PayoutLine",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of PayoutLine",
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
        /** @var PayoutLine $payoutLine */
        $payoutLine = $this->payoutLineRepository->find($id);

        if (empty($payoutLine)) {
            return $this->sendError('Payout Line not found');
        }

        $payoutLine->delete();

        return $this->sendSuccess('Payout Line deleted successfully');
    }
}
