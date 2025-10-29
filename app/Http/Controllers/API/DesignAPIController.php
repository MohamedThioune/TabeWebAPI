<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateDesignAPIRequest;
use App\Http\Requests\API\UpdateDesignAPIRequest;
use App\Models\Design;
use App\Infrastructure\Persistence\DesignRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\DesignResource;

/**
 * Class DesignController
 */

class DesignAPIController extends AppBaseController
{
    /** @var  DesignRepository */
    private $designRepository;

    public function __construct(DesignRepository $designRepo)
    {
        $this->designRepository = $designRepo;
    }

    /**
     * @OA\Get(
     *      path="/designs",
     *      summary="getDesignList",
     *      tags={"Design"},
     *      description="Get all Designs | Only for a admin !!",
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
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/Design")
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
        $designs = $this->designRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(DesignResource::collection($designs), 'Designs retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/designs",
     *      summary="createDesign",
     *      tags={"Design"},
     *      description="Create Design | Only for a admin !!",
     *      security={{"passport":{}}},
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Design")
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
     *                  ref="#/components/schemas/Design"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateDesignAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $design = $this->designRepository->create($input);

        return $this->sendResponse(new DesignResource($design), 'Design saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/designs/{id}",
     *      summary="getDesignItem",
     *      tags={"Design"},
     *      description="Get Design | Only for a admin !!",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Design",
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
     *                  ref="#/components/schemas/Design"
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
        /** @var Design $design */
        $design = $this->designRepository->find($id);

        if (empty($design)) {
            return $this->sendError('Design not found');
        }

        return $this->sendResponse(new DesignResource($design), 'Design retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/designs/{id}",
     *      summary="updateDesign",
     *      tags={"Design"},
     *      description="Update Design | Only for a admin !!",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Design",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Design")
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
     *                  ref="#/components/schemas/Design"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateDesignAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Design $design */
        $design = $this->designRepository->find($id);

        if (empty($design)) {
            return $this->sendError('Design not found');
        }

        $design = $this->designRepository->update($input, $id);

        return $this->sendResponse(new DesignResource($design), 'Design updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/designs/{id}",
     *      summary="deleteDesign",
     *      tags={"Design"},
     *      description="Delete Design | Only for a admin !!",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Design",
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
        /** @var Design $design */
        $design = $this->designRepository->find($id);

        if (empty($design)) {
            return $this->sendError('Design not found');
        }

        $design->delete();

        return $this->sendSuccess('Design deleted successfully');
    }
}
