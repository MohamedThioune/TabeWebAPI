<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\NotificationResource;
use App\Infrastructure\Persistence\NotificationRepository;
use App\Models\Notification;
use App\Models\User;
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
            $search,
            $request->get('skip'),
            $request->get('limit')
        );

        $notif = isset($notifs[0]) ? $notifs[0] : null;

        if (empty($notif)) {
            return ['errors' => "No notification found matching your search terms."];
        }

        $input = ['is_read' => 1, 'read_at' => now()];

        return $this->notificationRepository->update($input, $id);
    }

    public function readAuth(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $notif = $this->sample_read($request, $user->id, $id);

        return $this->sendResponse(new NotificationResource($notif),  'Notification updated successfully');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(notification $notification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(notification $notification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, notification $notification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(notification $notification)
    {
        //
    }
}
