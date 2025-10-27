<?php

namespace App\Http\Controllers\API;

use App\Domain\Users\DTO\Node;
use App\Domain\Users\ValueObjects\Type;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\GetUsersAPIRequest;
use App\Http\Requests\API\UpdateCustomerAPIRequest;
use App\Http\Requests\API\UpdateEnterpriseAPIRequest;
use App\Http\Requests\API\UpdatePartnerAPIRequest;
use App\Http\Requests\API\UserRequest;
use App\Http\Resources\UserResource;
use App\Infrastructure\Persistence\CustomerRepository;
use App\Infrastructure\Persistence\EnterpriseRepository;
use App\Infrastructure\Persistence\PartnerRepository;
use App\Infrastructure\Persistence\UserRepository;
use App\Models\User;
use App\Notifications\ProfileUpdateNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAPIController extends AppBaseController
{
    public function __construct(private UserRepository $userRepository, private EnterpriseRepository $enterpriseRepository, private PartnerRepository $partnerRepository, private CustomerRepository $customerRepository){}

    public function detached_index(array $search, Request $request): array
    {
        $users = $this->userRepository->all(
            $search,
            $request->get('skip'),
            $request->get('limit')
        );

        $infos = [
            'users' => UserResource::collection($users),
            'count' => !empty($users) ? count($users) : 0
        ];

        return $infos;
    }
    public function index(GetUsersAPIRequest $request): JsonResponse
    {
        $search = $request->except(['skip', 'limit']);

        $infos = $this->detached_index($search, $request);

        return $this->sendResponse($infos, 'Users retrieved successfully.');
    }
    public function indexPartner(GetUsersAPIRequest $request): JsonResponse
    {
        $search = $request->except(['skip', 'limit']);
        $search['type'] = Type::Partner->value;

        $infos = $this->detached_index($search, $request);

        return $this->sendResponse($infos, 'Partners retrieved successfully.');
    }

    public function sample_update_child(string $role, mixed $user, array $input): mixed
    {
        $input = [];
        $id = null;
        if ($role == Type::Customer->value):
            $input = app(UpdateCustomerAPIRequest::class)->validated();
            $id = $user->customer()->first() ? $user->customer()->first()->id : null;
        elseif ($role == Type::Enterprise->value):
            $input = app(UpdateEnterpriseAPIRequest::class)->validated();
            $id = $user->enterprise()->first() ? $user->enterprise()->first()->id : null;
        elseif ($role == Type::Partner->value):
            $input = app(UpdatePartnerAPIRequest::class)->validated();
            $id = $user->partner()->first() ? $user->partner()->first()->id : null;
        endif;

        return match ($role) {
            Type::Customer->value => $this->customerRepository->update($input, $id),
            Type::Enterprise->value => $this->enterpriseRepository->update($input, $id),
            Type::Partner->value  => $this->partnerRepository->update($input, $id),
        };

    }
    public function update(User $user, UserRequest $request): JsonResponse
    {
        $input = $request->all();

        if (empty($user)) {
            return $this->sendError('User not found, invalid');
        }

        $user = $this->userRepository->update($input, $user->id);

        $role = $user->roles->pluck('name')->toArray()[0] ?? Type::Customer->value;

        $this->sample_update_child($role, $user, $input);

        return $this->sendResponse(new UserResource($user), 'Users retrieved successfully.');
    }
    public function updateAuth(UserRequest $request): JsonResponse
    {
        $user = $request->user();
        $input = $request->all();

        if (empty($user)) {
            return $this->sendError('User not found, invalid');
        }

        $user = $this->userRepository->update($input, $user->id);

        $role = $user->roles->pluck('name')->toArray()[0] ?? Type::Customer->value;

        $this->sample_update_child($role, $user, $input);

        $node = new Node(
            content: null,
            contentVariables: null,
            level: "Info",
            model: "profile", //
            title: "Profil mis à jour",
            body: "Vos informations de profil ont été modifiées avec succès"
        );
        $user->notify(new ProfileUpdateNotification($node,  "whatsapp"));

        return $this->sendResponse(new UserResource($user), 'Users retrieved successfully.');
    }
}
