<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateQRSessionAPIRequest;
use App\Http\Requests\API\UpdateQRSessionAPIRequest;
use App\Models\QRSession;
use App\Infrastructure\Persistence\QRSessionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\QRSessionResource;

/**
 * Class QRSessionController
 */

class QRSessionAPIController extends AppBaseController
{
    /** @var  QRSessionRepository */
    private QRSessionRepository $qRSessionRepository;

    public function __construct(QRSessionRepository $qRSessionRepo)
    {
        $this->qRSessionRepository = $qRSessionRepo;
    }

    /**
     * @OA\Get(
     *      path="/qr-sessions",
     *      summary="getQRSessionList",
     *      tags={"QRSession"},
     *      description="Get all QRSessions",
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
     *                  @OA\Items(ref="#/components/schemas/QRSession")
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
        $qRSessions = $this->qRSessionRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(QRSessionResource::collection($qRSessions), 'QR Sessions retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/qr-sessions",
     *      summary="createQRSession",
     *      tags={"QRSession"},
     *      description="Create QRSession",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/QRSession")
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
     *                  ref="#/components/schemas/QRSession"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateQRSessionAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $qRSession = $this->qRSessionRepository->create($input);

        return $this->sendResponse(new QRSessionResource($qRSession), 'QR Session saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/qr-sessions/{id}",
     *      summary="getQRSessionItem",
     *      tags={"QRSession"},
     *      description="Get QRSession",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of QRSession",
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
     *                  ref="#/components/schemas/QRSession"
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
        /** @var QRSession $qRSession */
        $qRSession = $this->qRSessionRepository->find($id);

        if (empty($qRSession)) {
            return $this->sendError('QR Session not found');
        }

        return $this->sendResponse(new QRSessionResource($qRSession), 'QR Session retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/qr-sessions/{id}",
     *      summary="updateQRSession",
     *      tags={"QRSession"},
     *      description="Update QRSession",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of QRSession",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/QRSession")
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
     *                  ref="#/components/schemas/QRSession"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateQRSessionAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var QRSession $qRSession */
        $qRSession = $this->qRSessionRepository->find($id);

        if (empty($qRSession)) {
            return $this->sendError('QR Session not found');
        }

        $qRSession = $this->qRSessionRepository->update($input, $id);

        return $this->sendResponse(new QRSessionResource($qRSession), 'QR Session updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/qr-sessions/{id}",
     *      summary="deleteQRSession",
     *      tags={"QRSession"},
     *      description="Delete QRSession",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of QRSession",
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
        /** @var QRSession $qRSession */
        $qRSession = $this->qRSessionRepository->find($id);

        if (empty($qRSession)) {
            return $this->sendError('QR Session not found');
        }

        $qRSession->delete();

        return $this->sendSuccess('QR Session deleted successfully');
    }
}
