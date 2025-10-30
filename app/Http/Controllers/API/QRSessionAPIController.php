<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateQRSessionAPIRequest;
use App\Http\Requests\API\UpdateQRSessionAPIRequest;
use App\Models\QrSession;
use App\Infrastructure\Persistence\QRSessionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\QRSessionResource;
use Illuminate\Support\Facades\Hash;
use App\Domain\GiftCards\UseCases\CardFullyGenerated;
use Illuminate\Support\Str;

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
     *      description="Get all QRSessions | Only for admin",
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
     *      summary="refreshQRSession",
     *      tags={"QRSession"},
     *      description="Refresh QR Session",
     *      security={{"passport":{}}},
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
        $uuid_qr = Str::uuid()->toString();
        $qr_hashed_url = CardFullyGenerated::qr_url($uuid_qr);
        $payload = $qr_hashed_url['payload'] ?? null;
        $url = $qr_hashed_url['url'] ?? null;

        $former_qr = QRSession::where('gift_card_id', $request->gift_card_id)->first();
        if($former_qr) $former_qr->delete(); //safe delete on the last state of this QR

        $dto = [
            'id' => $uuid_qr,
            'status' => "pending",
            'token' => $payload,
            'url' => $url,
            'expired_at' => now()->addDays(2),
            'gift_card_id' => $request->gift_card_id
        ];

        $qRSession = $this->qRSessionRepository->create($dto);

        return $this->sendResponse(new QRSessionResource($qRSession), 'QR Session saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/qr-sessions/{id}",
     *      summary="getQRSessionItem",
     *      tags={"QRSession"},
     *      description="Get QR Session",
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
        /** @var QrSession $qRSession */
        $qRSession = $this->qRSessionRepository->find($id);

        if (empty($qRSession)) {
            return $this->sendError('QR Session not found, invalid or expired');
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

        /** @var QrSession $qRSession */
        $qRSession = $this->qRSessionRepository->find($id);

        if (empty($qRSession)) {
            return $this->sendError('QR Session not found, invalid or expired');
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
        /** @var QrSession $qRSession */
        $qRSession = $this->qRSessionRepository->find($id);

        if (empty($qRSession)) {
            return $this->sendError('QR Session not found, invalid or expired');
        }

        $qRSession->delete();

        return $this->sendSuccess('QR Session deleted successfully');
    }

    public function check($payload): ?string
    {
        $decoded = base64_decode($payload);
        list($uuid, $signature) = explode('.', $decoded, 2);
        if (! hash_equals(hash_hmac('sha256', $uuid, config('app.key')), $signature)) {
            return null;
        }

        return (string)$uuid;
    }

    public function verify(UpdateQRSessionAPIRequest $request): JsonResponse
    {
        /** @var QrSession $qRSession */
        $uuid = $this->check($request->payload); //return uuid or null

        $qrSession = $this->qRSessionRepository->find($uuid);
        if (empty($qrSession)) {
            return $this->sendError('QR Session or Payload not found/invalid, Refresh the QR !');
        }

        //Logging the use
        $qrSession->status = 'used';
        $qrSession->updated_at = now();

        /*
         * Dispatch the transaction
        */
        // Instructions code here !

        $qrSession->save();
        $qrSession->delete();

        return $this->sendResponse(new QRSessionResource($qrSession), 'QR Session verified successfully');
    }


}
