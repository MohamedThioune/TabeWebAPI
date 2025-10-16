<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreatePartnerCategoryAPIRequest;
use App\Http\Requests\API\UpdatePartnerCategoryAPIRequest;
use App\Models\UserCategory;
use App\Infrastructure\Persistence\PartnerCategoryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\UserCategoryResource;

/**
 * Class PartnerCategoryController
 */

class UserCategoryAPIController extends AppBaseController
{
    /** @var  PartnerCategoryRepository */
    private $partnerCategoryRepository;

    public function __construct(PartnerCategoryRepository $partnerCategoryRepo)
    {
        $this->partnerCategoryRepository = $partnerCategoryRepo;
    }

    /**
     * @OA\Get(
     *      path="/partner-categories",
     *      summary="getPartnerCategoryList",
     *      tags={"UserCategory"},
     *      description="Get all PartnerCategories",
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
     *                  @OA\Items(ref="#/components/schemas/UserCategory")
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
        $partnerCategories = $this->partnerCategoryRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(UserCategoryResource::collection($partnerCategories), 'Partner Categories retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/partner-categories",
     *      summary="createPartnerCategory",
     *      tags={"UserCategory"},
     *      description="Create UserCategory",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/UserCategory")
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
     *                  ref="#/components/schemas/UserCategory"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreatePartnerCategoryAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $partnerCategory = $this->partnerCategoryRepository->create($input);

        return $this->sendResponse(new UserCategoryResource($partnerCategory), 'Partner Category saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/partner-categories/{id}",
     *      summary="getPartnerCategoryItem",
     *      tags={"UserCategory"},
     *      description="Get UserCategory",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of UserCategory",
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
     *                  ref="#/components/schemas/UserCategory"
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
        /** @var UserCategory $partnerCategory */
        $partnerCategory = $this->partnerCategoryRepository->find($id);

        if (empty($partnerCategory)) {
            return $this->sendError('Partner Category not found');
        }

        return $this->sendResponse(new UserCategoryResource($partnerCategory), 'Partner Category retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/partner-categories/{id}",
     *      summary="updatePartnerCategory",
     *      tags={"UserCategory"},
     *      description="Update UserCategory",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of UserCategory",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/UserCategory")
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
     *                  ref="#/components/schemas/UserCategory"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdatePartnerCategoryAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var UserCategory $partnerCategory */
        $partnerCategory = $this->partnerCategoryRepository->find($id);

        if (empty($partnerCategory)) {
            return $this->sendError('Partner Category not found');
        }

        $partnerCategory = $this->partnerCategoryRepository->update($input, $id);

        return $this->sendResponse(new UserCategoryResource($partnerCategory), 'UserCategory updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/partner-categories/{id}",
     *      summary="deletePartnerCategory",
     *      tags={"UserCategory"},
     *      description="Delete UserCategory",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of UserCategory",
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
        /** @var UserCategory $partnerCategory */
        $partnerCategory = $this->partnerCategoryRepository->find($id);

        if (empty($partnerCategory)) {
            return $this->sendError('Partner Category not found');
        }

        $partnerCategory->delete();

        return $this->sendSuccess('Partner Category deleted successfully');
    }
}
