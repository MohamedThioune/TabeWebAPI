<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateBeneficiaryAPIRequest;
use App\Http\Requests\API\UpdateBeneficiaryAPIRequest;
use App\Models\Beneficiary;
use App\Infrastructure\Persistence\BeneficiaryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\BeneficiaryResource;

/**
 * Class BeneficiaryController
 */

class BeneficiaryAPIController extends AppBaseController
{
    /** @var  BeneficiaryRepository */
    private $beneficiaryRepository;

    public function __construct(BeneficiaryRepository $beneficiaryRepo)
    {
        $this->beneficiaryRepository = $beneficiaryRepo;
    }

    /**
     * @OA\Get(
     *      path="/beneficiaries",
     *      summary="getBeneficiaryList",
     *      tags={"Beneficiary"},
     *      description="Get all Beneficiaries",
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
     *                  @OA\Items(ref="#/components/schemas/Beneficiary")
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
        $beneficiaries = $this->beneficiaryRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(BeneficiaryResource::collection($beneficiaries), 'Beneficiaries retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/beneficiaries",
     *      summary="createBeneficiary",
     *      tags={"Beneficiary"},
     *      description="Create Beneficiary",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Beneficiary")
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
     *                  ref="#/components/schemas/Beneficiary"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateBeneficiaryAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $beneficiary = $this->beneficiaryRepository->create($input);

        return $this->sendResponse(new BeneficiaryResource($beneficiary), 'Beneficiary saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/beneficiaries/{id}",
     *      summary="getBeneficiaryItem",
     *      tags={"Beneficiary"},
     *      description="Get Beneficiary",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Beneficiary",
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
     *                  ref="#/components/schemas/Beneficiary"
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
        /** @var Beneficiary $beneficiary */
        $beneficiary = $this->beneficiaryRepository->find($id);

        if (empty($beneficiary)) {
            return $this->sendError('Beneficiary not found');
        }

        return $this->sendResponse(new BeneficiaryResource($beneficiary), 'Beneficiary retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/beneficiaries/{id}",
     *      summary="updateBeneficiary",
     *      tags={"Beneficiary"},
     *      description="Update Beneficiary",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Beneficiary",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Beneficiary")
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
     *                  ref="#/components/schemas/Beneficiary"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateBeneficiaryAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Beneficiary $beneficiary */
        $beneficiary = $this->beneficiaryRepository->find($id);

        if (empty($beneficiary)) {
            return $this->sendError('Beneficiary not found');
        }

        $beneficiary = $this->beneficiaryRepository->update($input, $id);

        return $this->sendResponse(new BeneficiaryResource($beneficiary), 'Beneficiary updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/beneficiaries/{id}",
     *      summary="deleteBeneficiary",
     *      tags={"Beneficiary"},
     *      description="Delete Beneficiary",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Beneficiary",
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
        /** @var Beneficiary $beneficiary */
        $beneficiary = $this->beneficiaryRepository->find($id);

        if (empty($beneficiary)) {
            return $this->sendError('Beneficiary not found');
        }

        $beneficiary->delete();

        return $this->sendSuccess('Beneficiary deleted successfully');
    }
}
