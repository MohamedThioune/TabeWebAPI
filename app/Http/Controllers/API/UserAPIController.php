<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\GetUsersAPIRequest;
use App\Http\Resources\UserResource;
use App\Infrastructure\Persistence\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAPIController extends AppBaseController
{
    public function __construct(private UserRepository $userRepository){}

    public function index(GetUsersAPIRequest $request): JsonResponse
    {
        $users = $this->userRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        //Load relations
//        $users->load('customer');
//        $users->load('partner');
//        $users->load('enterprise');

        $infos = [
            'users' => UserResource::collection($users),
            'count' => !empty($users) ? count($users) : 0
        ];

        return $this->sendResponse($infos, 'Users retrieved successfully.');
    }
}
