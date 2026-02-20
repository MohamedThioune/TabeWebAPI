<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateEnterpriseAPIRequest;
use App\Http\Requests\API\UpdateEnterpriseAPIRequest;
use App\Models\Enterprise;
use App\Infrastructure\Persistence\EnterpriseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\EnterpriseResource;

/**
 * Class EnterpriseController
 */

class EnterpriseAPIController extends AppBaseController
{
    /** @var  EnterpriseRepository */
    private $enterpriseRepository;

    public function __construct(EnterpriseRepository $enterpriseRepo)
    {
        $this->enterpriseRepository = $enterpriseRepo;
    }

    public function collect(array $search = [], Request $request, int $perPage = 9): array
    {
        $query_enterprise = $this->enterpriseRepository->allQuery(
            $search,
            $request->get('skip'),
            $request->get('limit')
        );

        $enterprises = $query_enterprise->paginate($perPage);

        return [
            'enterprises' => EnterpriseResource::collection($enterprises),
            'count' => $enterprises->total(),
            'pagination' => [
                'previous_page' => $enterprises->currentPage() - 1 > 0 ? $enterprises->currentPage() - 1 : null,
                'current_page' => $enterprises->currentPage(),
                'next_page' => $enterprises->hasMorePages() ? $enterprises->currentPage() + 1 : null,
                'total_pages' => $enterprises->lastPage(),
                'per_page' => $enterprises->perPage(),
                'total_items' => $enterprises->total(),
            ]
        ];
       
    }

    /**
     * @OA\Get(
     *      path="/enterprises",
     *      summary="getEnterpriseList",
     *      tags={"Enterprise"},
     *      description="Get all Enterprises | only for admin !!",
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
     *                  @OA\Items(ref="#/components/schemas/Enterprise")
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
        $search = $request->except(['skip', 'limit', 'page', 'per_page']);
        $perPage = $request->get('per_page') ?: 9;

        $infoEnterprises = $this->collect($search, $request, $perPage);

        return $this->sendResponse($infoEnterprises, 'Enterprises retrieved successfully !');
    }

    /**
     * @OA\Post(
     *      path="/enterprises",
     *      summary="createEnterprise",
     *      tags={"Enterprise"},
     *      description="Create Enterprise | only for admin !!",
     *      security={{"passport":{}}},
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Enterprise")
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
     *                  ref="#/components/schemas/Enterprise"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateEnterpriseAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $enterprise = $this->enterpriseRepository->create($input);

        return $this->sendResponse(new EnterpriseResource($enterprise), 'Enterprise saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/enterprises/{id}",
     *      summary="getEnterpriseItem",
     *      tags={"Enterprise"},
     *      description="Get Enterprise",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Enterprise",
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
     *                  ref="#/components/schemas/Enterprise"
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
        /** @var Enterprise $enterprise */
        $enterprise = $this->enterpriseRepository->find($id);

        if (empty($enterprise)) {
            return $this->sendError('Enterprise not found');
        }

        return $this->sendResponse(new EnterpriseResource($enterprise), 'Enterprise retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/enterprises/{id}",
     *      summary="updateEnterprise",
     *      tags={"Enterprise"},
     *      description="Update Enterprise",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Enterprise",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Enterprise")
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
     *                  ref="#/components/schemas/Enterprise"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateEnterpriseAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Enterprise $enterprise */
        $enterprise = $this->enterpriseRepository->find($id);

        if (empty($enterprise)) {
            return $this->sendError('Enterprise not found');
        }

        $enterprise = $this->enterpriseRepository->update($input, $id);

        return $this->sendResponse(new EnterpriseResource($enterprise), 'Enterprise updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/enterprises/{id}",
     *      summary="deleteEnterprise",
     *      tags={"Enterprise"},
     *      description="Delete Enterprise",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Enterprise",
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
        /** @var Enterprise $enterprise */
        $enterprise = $this->enterpriseRepository->find($id);

        if (empty($enterprise)) {
            return $this->sendError('Enterprise not found');
        }

        $enterprise->delete();

        return $this->sendSuccess('Enterprise deleted successfully');
    }
}
