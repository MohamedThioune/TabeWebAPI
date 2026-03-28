<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateOptionAPIRequest;
use App\Http\Requests\API\UpdateOptionAPIRequest;
use App\Models\Option;
use App\Infrastructure\Persistence\OptionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\OptionResource;
use App\Helpers\Parameter;

/**
 * Class OptionController
 */

class OptionAPIController extends AppBaseController
{
    /** @var  OptionRepository */
    private $optionRepository;

    public function __construct(OptionRepository $optionRepo)
    {
        $this->optionRepository = $optionRepo;
    }

    /**
     * @OA\Get(
     *      path="/options",
     *      summary="getOptionList",
     *      tags={"Option"},
     *      description="Get all the app options",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *               @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/Option"
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
        $option = Parameter::option();

        return $this->sendResponse(new OptionResource($option), 'App Option retrieved successfully !');
    }

    public function show($id): JsonResponse
    {
        /** @var Option $option */
        $option = $this->optionRepository->find($id);

        if (empty($option)) {
            return $this->sendError('Option not found');
        }

        return $this->sendResponse(new OptionResource($option), 'Option retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/options",
     *      summary="updateOption",
     *      tags={"Option"},
     *      description="Update Options",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Option")
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
     *                  ref="#/components/schemas/Option"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
    */
    public function update(UpdateOptionAPIRequest $request): JsonResponse
    {
        $input = $request->all();
        $input = [
            'min_amount_card' => $input['min_amount_card'] ?? Parameter::minAmountCard(),
            'max_amount_card' => $input['max_amount_card'] ?? Parameter::maxAmountCard(),
            'period_validity_card' => $input['period_validity_card'] ?? Parameter::periodValidityCard()
        ];

        /** @var Option $option */        
        $option = $this->optionRepository->create($input);

        return $this->sendResponse(new OptionResource($option), 'App Option updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/options",
     *      summary="deleteOption",
     *      tags={"Option"},
     *      description="Delete Option",
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
    public function destroy(): JsonResponse
    {
        /** @var Option $option */
        $option = Parameter::option();

        if (empty($option)) {
            return $this->sendError('Option not found');
        }

        $option->delete();

        return $this->sendSuccess('Option deleted successfully');
    }
}
