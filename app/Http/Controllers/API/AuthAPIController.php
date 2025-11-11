<?php

namespace App\Http\Controllers\API;

use App\Domain\Users\UseCases\RegisterUser;
use App\Domain\Users\ValueObjects\Type;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseController;
use App\Http\Requests\API\CustomerAPIRequest;
use App\Http\Requests\API\EnterpriseAPIRequest;
use App\Http\Requests\API\OTPAPIRequest;
use App\Http\Requests\API\PartnerAPIRequest;
use App\Http\Requests\API\ResetPasswordAPIRequest;
use App\Http\Requests\API\UserRequest;
use App\Http\Resources\UserResource;
use App\Infrastructure\Persistence\OTPRequestRepository;
use App\Models\User;
use App\Notifications\PushSMSNotification;
use App\Notifications\PushWhatsAppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domain\Users\DTO\Node;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Fluent;

class AuthAPIController extends Controller
{
    use ResponseController;

    public function __construct(private RegisterUser $registerUser, private OTPRequestRepository $otpRequestRepository){}

    /**
     * @OA\Post(
     *      path="/auth/register",
     *      summary="register",
     *      tags={"Auth"},
     *      description="Register a user",
     *      @OA\RequestBody(
     *        @OA\MediaType(
     *          mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(
     *                  property="type",
     *                  type="string",
     *                  description="type user(customer, partner, enterprise)",
     *                  enum={"customer", "partner", "enterprise"}
     *              ),
     *              @OA\Property(
     *                   property="first_name",
     *                   type="string",
     *                   description="first name | if a customer"
     *               ),
     *              @OA\Property(
     *                  property="last_name",
     *                  type="string",
     *                  description="first name | if a customer"
     *              ),
     *              @OA\Property(
     *                  property="email",
     *                  type="string",
     *                  description="nullable"
     *              ),
     *              @OA\Property(
     *                   property="phone",
     *                   type="string",
     *                   description="use it as a identifier(username)"
     *               ),
     *              @OA\Property(
     *                    property="whatsApp",
     *                    type="string",
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                   property="password_confirmation",
     *                   type="string",
     *               ),
     *           ),
     *        ),
     *      ),
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
     *                  property="data",
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function register(UserRequest $request): JsonResponse
    {
       $dto = array();
       $type = $request->get("type");
       if($type == "admin"){
           return $this->error("You cannot create admin by yourself !", 403);
       }

       match ($type) {
            Type::Customer->value   => app(CustomerAPIRequest::class)->validated(),
            Type::Enterprise->value => app(EnterpriseAPIRequest::class)->validated(),
            Type::Partner->value    => app(PartnerAPIRequest::class)->validated(),
       };

       $dto = $request->only("type", "first_name", "last_name", "gender", "email", "phone", "whatsApp", "password", "name");
       $model = $this->registerUser->execute($dto);

       if(!$model instanceof User){
          return $this->error("something went wrong, check your data(ex : whatsapp and phone must be unique and in format) !", 403);
       }

       $user = new UserResource($model);
        //Request OTP
        $input = [
            "purpose" => "login",
            "channel" => "whatsapp"
        ];
        $otp_code = $this->otp_send($model, $input);

       return $this->response(["code" => $otp_code, 'user' => $user], 'User registered successfully !', 201);
    }

    public function otp_send(User $user, array $input): int
    {
        $otp_code = random_int(100000, 999999);

        if($input['channel'] == "sms"):
            //Notify via sms
            $node = new Node(
                content : $otp_code,
                contentVariables: null,
                level: null,
                model: null,
                title: null,
                body: null
            );
            $user->notify(new PushSMSNotification($node, $input['channel']));
        endif;

        if($input['channel'] == "whatsapp"):
            //Notify via whatsApp
            $content_variables = json_encode(["1" => (String)$otp_code]);

            $body = "{{1}} est votre code de vérification. Pour votre sécurité, ne communiquez ce code à personne.";
            $node = new Node(
                content : $body,
                contentVariables: $content_variables,
                level: null,
                model: null,
                title: null,
                body: null
            );

            $user->notify(new PushWhatsAppNotification($node, $input['channel']));
        endif;

        //Cache store OTP
        Cache::put('otp_code_' . $user->phone, bcrypt($otp_code), now()->addMinutes(15));

        return $otp_code;
    }

    public function otp_check(String $phone, int $check_otp_code): array
    {
        $bcrypt_otp_code = Cache::get('otp_code_' . $phone); //get cached otp

        //No matches
        if(!$bcrypt_otp_code){
            return ["error" => "No OTP request matches this record it's expired !"];
        }

        //Check otp_code
        if (!Hash::check($check_otp_code, $bcrypt_otp_code)) {
            return ["error" => "Invalid OTP !"];
        }

        Cache::delete('otp_code_' . $phone);

        return ["success" => true];

    }

    /**
     * @OA\Post(
     *      path="/auth/otp/request/{phone}",
     *      summary="otpRequest",
     *      tags={"OTP"},
     *      description="OTP request",
     *      @OA\Parameter(
     *          name="phone",
     *          description="phone number to receive the OTP",
     *           @OA\Schema(
     *             type="string"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *           mediaType="multipart/form-data",
     *            @OA\Schema(
     *              @OA\Property(
     *                   property="purpose",
     *                   type="string",
     *                   description="purpose of this request ('login', 'reset_password', 'activate_card', 'verify_card', 'others')",
     *                   enum={"login", "reset_password", "activate_card", "verify_card", "others"}
     *              ),
     *              @OA\Property(
     *                    property="channel",
     *                    type="string",
     *                    description="channel used('whatsapp', 'sms')",
     *                    enum={"whatsapp", "sms"}
     *              ),
     *           ),
     *         ),
     *      ),
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
     *                  property="data",
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function otp_request(User $user, OTPAPIRequest $request): JsonResponse
    {
        $input = $request->only('purpose', 'channel', );
        //Call OTP send
        $otp_code = $this->otp_send($user, $input);
        return $this->response($otp_code, 'OTP send successfully !', 200);
    }

    /**
     * @OA\Put(
     *      path="/auth/otp/verify/{phone}",
     *      summary="otpVerify",
     *      tags={"OTP"},
     *      description="OTP verify",
     *      @OA\Parameter(
     *          name="phone",
     *          description="phone number to receive the OTP",
     *           @OA\Schema(
     *             type="string"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *           mediaType="multipart/form-data",
     *            @OA\Schema(
     *              @OA\Property(
     *                   property="purpose",
     *                   type="string",
     *                   description="purpose of this request ('login', 'reset_password', 'activate_card', 'verify_card', 'others')",
     *                   enum={"login", "reset_password", "activate_card", "verify_card", "others"}
     *              ),
     *              @OA\Property(
     *                    property="otp_code",
     *                    type="integer",
     *                    description="code otp to test"
     *              ),
     *           ),
     *         ),
     *      ),
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
     *                  property="data",
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function otp_verify(User $user, OTPAPIRequest $request): JsonResponse
    {
        $input = $request->only('purpose', 'otp_code');
        $fields = array('updated_at' => now());

        //Phone verified at
        $user->phone_verified_at = now();
        $user->save();

        //Checkin OTP
        $check_otp = new Fluent($this->otp_check($user->phone, $input['otp_code']));
        $error = $check_otp->error ?? null;
        if($error){
            return $this->error($error, 401);
        }

        //Create token
        $token = $user->createToken('Personal Access Token')->accessToken;
        $infos = [
            'user' => new UserResource($user),
            'token' => $token,
            'type' => 'Bearer'
        ];
        return $this->response($infos, 'OTP verify successfully !', 200);
    }

    /**
     * @OA\Get(
     *      path="/me",
     *      summary="me",
     *      tags={"User"},
     *      description="Auth user",
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
    public function me(Request $request){
        $user = $request->user();

        if(empty($user)){
            return $this->error("User not found", 401);
        }

        return $this->response(new UserResource($user), 'User successfully retrived !', 200);
    }

    /**
     * @OA\Delete(
     *      path="/logout",
     *      summary="logout",
     *      tags={"Auth"},
     *      description="Logout",
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
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();

        return $this->success('Successfully logged out', 200);
    }

    public function reset_password(User $user, ResetPasswordAPIRequest $request): JsonResponse
    {
        $input = $request->only('otp_code', 'new_password', 'new_password_confirmation');

        //Checkin OTP
        $check_otp = new Fluent($this->otp_check($user->phone, $input['otp_code']));
        $error = $check_otp->error ?? null;
        if($error){
            return $this->error($error, 401);
        }

        //Change the password
        $user->password = bcrypt($input['new_password']);
        $user->save();

        return $this->success('Password successfully changed !', 200);
    }

}
