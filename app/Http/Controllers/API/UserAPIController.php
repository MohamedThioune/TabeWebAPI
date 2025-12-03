<?php

namespace App\Http\Controllers\API;

use App\Domain\Users\DTO\Node;
use App\Domain\Users\ValueObjects\Type;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\API\GetUsersAPIRequest;
use App\Http\Requests\API\ModifyPasswordAPIRequest;
use App\Http\Requests\API\UpdateCustomerAPIRequest;
use App\Http\Requests\API\UpdateEnterpriseAPIRequest;
use App\Http\Requests\API\UpdatePartnerAPIRequest;
use App\Http\Requests\API\UserRequest;
use App\Http\Resources\UserResource;
use App\Infrastructure\Persistence\CustomerRepository;
use App\Infrastructure\Persistence\EnterpriseRepository;
use App\Infrastructure\Persistence\PartnerRepository;
use App\Infrastructure\Persistence\UserRepository;
use App\Infrastructure\Persistence\GiftCardRepository;
use App\Models\User;
use App\Notifications\ProfileUpdateNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserAPIController extends AppBaseController
{
    public function __construct(private UserRepository $userRepository, private EnterpriseRepository $enterpriseRepository, private PartnerRepository $partnerRepository, private CustomerRepository $customerRepository, private GiftCardRepository $giftCardRepository ){}

    public function detached_index(array $search, Request $request, int $perPage = 8): array
    {
        $query_users = $this->userRepository->allQuery(
            $search,
            $request->get('skip'),
            $request->get('limit')
        );

        $count_users = $this->userRepository->countQuery($query_users);
        $users = $this->userRepository->paginate($query_users, $perPage);

        return [
            'users' => UserResource::collection($users),
            'count' => $users->total(),
            'pagination' => [
                'previous_page' => $users->currentPage() - 1 > 0 ? $users->currentPage() - 1 : null,
                'current_page' => $users->currentPage(),
                'next_page' => $users->hasMorePages() ? $users->currentPage() + 1 : null,
                'total_pages' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total_items' => $users->total(),
            ]
        ];
    }
    /**
     * @OA\Get(
     *      path="/users",
     *      summary="listUsers",
     *      tags={"User"},
     *      description="List the users | Only for admin !!",
     *      security={{"passport":{}}},
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="Filter users by role (customer, partner)",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              enum={"customer", "partner"}
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="is_active",
     *          in="query",
     *          description="Filter users by status (0:inactive, 1:active)",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean",
     *              enum={"0","1"}
     *          )
     *      ),
     *      @OA\Parameter(
     *           name="is_phone_verified",
     *           in="query",
     *           description="Filter users by status (0:not_verified, 1:verified)",
     *           required=false,
     *           @OA\Schema(
     *               type="boolean",
     *               enum={"0","1"}
     *           )
     *       ),
     *      @OA\Parameter(
     *          name="skip",
     *          in="query",
     *          description="Skip",
     *          required=false,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *           name="limit",
     *           in="query",
     *           description="Limit",
     *           required=false,
     *           @OA\Schema(
     *               type="integer"
     *           )
     *       ),
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
     *                  ref="#/components/schemas/User"
     *              ),
     *              @OA\Property(
     *                   property="message",
     *                   type="string"
     *               ),
     *          )
     *      )
     * )
     */
    public function index(GetUsersAPIRequest $request): JsonResponse
    {
        $search = $request->except(['skip', 'limit']);

        $infos = $this->detached_index($search, $request);

        return $this->sendResponse($infos, 'Users retrieved successfully.');
    }
    /**
     * @OA\Get(
     *      path="/partners",
     *      summary="listPartners",
     *      tags={"Partner"},
     *      description="List the partners",
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="number items per page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *           name="page",
     *           in="query",
     *           description="page number",
     *           required=false,
     *           @OA\Schema(
     *               type="integer"
     *           )
     *       ),
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
     *                  ref="#/components/schemas/User"
     *              ),
     *              @OA\Property(
     *                   property="message",
     *                   type="string"
     *               ),
     *          )
     *      )
     * )
     */
    public function indexPartner(GetUsersAPIRequest $request): JsonResponse
    {
        $search = $request->except(['skip', 'limit', 'page', 'per_page']);
        $search['type'] = Type::Partner->value;
        $perPage = $request->get('per_page', 8);

        $infos = $this->detached_index($search, $request, $perPage);

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
    /**
     * @OA\Patch(
     *      path="/users",
     *      summary="updateUser",
     *      tags={"User"},
     *      description="Update the user",
     *      security={{"passport":{}}},
     *      @OA\RequestBody(
     *        @OA\MediaType(
     *          mediaType="multipart/form-data",
     *           @OA\Schema(
     *             @OA\Property(
     *                 property="first_name",
     *                 type="string",
     *                 description="if user a customer"
     *             ),
     *             @OA\Property(
     *                  property="last_name",
     *                  type="string",
     *                  description="if user a customer"
     *             ),
     *             @OA\Property(
     *                   property="gender",
     *                   type="string",
     *                   description="if user a customer",
     *                   enum={"male","female"}
     *             ),
     *             @OA\Property(
     *                    property="birthdate",
     *                    type="string",
     *                    description="if user a customer",
     *                    format="date-time"
     *              ),
     *              @OA\Property(
     *                    property="name",
     *                    type="string",
     *                    description="if user a partner",
     *               ),
     *              @OA\Property(
     *                    property="sector",
     *                    type="string",
     *                    description="if user a partner",
     *                    enum={"Mode","Beauté","Gastronomie","Technologie","Bien-être","Décoration","Sport","Librairie"}
     *               ),
     *              @OA\Property(
     *                  property="office_phone",
     *                  type="string",
     *                  description="if user a partner",
     *              ),
     *             @OA\Property(
     *                    property="country",
     *                    type="string",
     *              ),
     *             @OA\Property(
     *                    property="city",
     *                    type="string",
     *             ),
     *             @OA\Property(
     *                     property="address",
     *                     type="string",
     *              ),
     *           ),
     *        ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation !",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="Success",
     *              ),
     *          ),
     *     ),
     * )
     */
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

    /**
     * @OA\Delete(
     *      path="/me",
     *      summary="deletePermanentlyAccount",
     *      tags={"User"},
     *      description="Delete permanently account user",
     *      security={{"passport":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="status",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     * @throws \Throwable
     */
    public function destroy(Request $request): JsonResponse{
        $user = $request->user();

        if (empty($user)) {
            return $this->sendError('User not found, invalid');
        }

        $role = $user->roles->pluck('name')->toArray()[0] ?? Type::Customer->value;

        DB::beginTransaction();
        try{
            //Delete entity depending on the user roles
            match ($role) {
                Type::Customer->value   => $user->customer()->delete(),
                Type::Enterprise->value => $user->enterprise()->delete(),
                Type::Partner->value    => $user->partner()->delete(),

                default => null,
            };

            $user->files()?->delete(); //delete user files
            $user->user_categories()?->delete(); //delete user categories
            $user->qr_sessions()?->delete(); //delete user qr_sessions
            $user->gift_cards()?->delete();  //delete user gift cards

            $user->token()->revoke(); //disconnect
            $user->roles()->detach(); //detach all roles of the user

            //Finally delete the user
            $user->delete();
            DB::commit();
        }
        catch(\Throwable $e){
            DB::rollBack();
            throw $e;
        }
        return $this->sendSuccess('Account deleted successfully.');

    }

    /**
     * @OA\Patch(
     *      path="/update/password",
     *      summary="UpdatePassword",
     *      tags={"User"},
     *      description="Update password",
     *      security={{"passport":{}}},
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *               @OA\Property(
     *                    property="password",
     *                    type="string",
     *                    format="password"
     *               ),
     *              @OA\Property(
     *                   property="new_password",
     *                   type="string",
     *                   format="password"
     *              ),
     *              @OA\Property(
     *                   property="new_password_confirmation",
     *                   type="string",
     *                   format="password"
     *              ),
     *           ),
     *         ),
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="status",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *         )
     *     )
     * )
     */

    public function update_password(ModifyPasswordAPIRequest $request): JsonResponse
    {

        $user = $request->user();
        $input = $request->only('password', 'new_password', 'new_password_confirmation');

        //Check password
        if (!Hash::check($input['password'], $user->password)) {
            return $this->sendError("Password does not match !", 401);
        }

        //Change the password
        $user->password = bcrypt($input['new_password']);
        $user->save();

        return $this->sendSuccess('Password successfully changed !', 200);
    }

    /**
     * @OA\Get(
     *      path="/customer/stats",
     *      summary="statsCustomer",
     *      tags={"Customer"},
     *      description="Get the stats of the customers",
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
     *              ),
     *              @OA\Property(
     *                   property="message",
     *                   type="string"
     *               ),
     *          )
     *      )
     * )
     */
    public function statsCustomer(Request $request): JsonResponse{
        $user = $request->user();

        $infos =
            [
                'used_month_gift_cards' => $this->giftCardRepository->usedMonthly($user),
                'total_available_gift_cards' => $this->giftCardRepository->countQueryTotal('active', $user),
                'total_used_gift_cards' => $this->giftCardRepository->countQueryTotal('used', $user),
                'total_expired_gift_cards' => $this->giftCardRepository->countQueryTotal('expired', $user),
                'total_inactive_gift_cards' => $this->giftCardRepository->countQueryTotal('inactive', $user),
                'total_pending_gift_cards' => $this->giftCardRepository->countQueryTotal('pending', $user),
                'total_gift_cards' => $this->giftCardRepository->countQueryTotal(null, $user),
                'total_available_gift_cards_amount' => $this->giftCardRepository->countQueryAmount('active', $user),
                'total_gift_cards_amount' => $this->giftCardRepository->countQueryAmount(null, $user),

            ];

        return $this->sendResponse($infos, 'Users retrieved stats successfully !');
    }

}
