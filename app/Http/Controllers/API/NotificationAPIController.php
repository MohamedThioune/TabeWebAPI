<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\NotificationResource;
use App\Infrastructure\Persistence\NotificationRepository;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationAPIController extends AppBaseController
{
    private $notificationRepository;

    public function __construct(NotificationRepository $notifRepo)
    {
        $this->notificationRepository = $notifRepo;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $notifs = $this->notificationRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(NotificationResource::collection($notifs), 'Your Notifications retrieved successfully');
    }

    public function sample_read(Request $request, string $user_id,  string $id): Mixed
    {
        $search = [
            'id' => $id,
            'notifiable_id' => $user_id,
            'is_read' => 0
        ];

        $notifs = $this->notificationRepository->all(
                $search
        );

        $notif = isset($notifs[0]) ? $notifs[0] : null;

        if (empty($notif)) {
            return ['error' => "No notification found matching your search terms."];
        }

        $input = ['is_read' => 1, 'read_at' => now()];

        return $this->notificationRepository->update($input, $id);
    }

    public function readAuth(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notif = $this->sample_read($request, $user->id, $id);
        if (isset($notif['error']))
            return $this->sendError($notif['error']);

        return $this->sendResponse(new NotificationResource($notif),  'Notification read successfully !');
    }

    public function readAll(Request $request): JsonResponse
    {
        $user = $request->user();

        //Update all matching queries
        $affectedRows = Notification::where('notifiable_id', $user->id)->where('is_read', 0)->update(['is_read' => 1]);
        $message = ($affectedRows > 0) ? 'All notifications read successfully !' : 'No changes were made.';

//        $notifs = $this->notificationRepository->all(
//            ['notifiable_id' => $user->id],
//            $request->get('skip'),
//            $request->get('limit')
//        );
//
//        $notif = isset($notifs[0]) ? $notifs[0] : null;

        return $this->sendSuccess($message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notifs = $this->notificationRepository->all(
            ['notifiable_id' => $user->id, 'id' => $id],
        );

        $notif = isset($notifs[0]) ? $notifs[0] : null;

        if (empty($notif)) {
            return $this->sendError('No notification found matching your search terms.');
        }

        $notif->delete();

        return $this->sendSuccess('Notif deleted successfully');
    }
}
