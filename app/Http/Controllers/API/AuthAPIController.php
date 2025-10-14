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
use App\Http\Requests\API\UserRequest;
use App\Http\Resources\UserResource;
use App\Infrastructure\Persistence\OTPRequestRepository;
use App\Models\OTPRequest;
use App\Models\User;
use App\Notifications\PushSMSNotification;
use App\Notifications\PushWhatsAppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domain\Users\DTO\Node;
use Illuminate\Support\Facades\Hash;
use phpseclib3\Math\PrimeField\Integer;

class AuthAPIController extends Controller
{
    use ResponseController;

    public function __construct(private RegisterUser $registerUser, private OTPRequestRepository $otpRequestRepository){}

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
//       var_dump($model);
//       die();

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
            $node = new Node($otp_code, null);
            $user->notify(new PushSMSNotification($node, $input['channel']));
        endif;

        if($input['channel'] == "whatsapp"):
            //Notify via whatsApp
            $content_variables = json_encode(["1" => (String)$otp_code]);

            $body = "{{1}} est votre code de vérification. Pour votre sécurité, ne communiquez ce code à personne.";
            $node = new Node($body, $content_variables);

            $user->notify(new PushWhatsAppNotification($node, $input['channel']));
        endif;

        //Store OTP requests on DB
        $dtoOTP = [
            'user_id' => $user->id,
            'channel' => $input['channel'],
            'otp_code' => bcrypt($otp_code),
            'purpose' => $input['purpose'],
            'status' => 'pending',
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
        ];
        $instanceOTP = $this->otpRequestRepository->save($dtoOTP);

        return $otp_code;

    }

    public function otp_request(User $user, OTPAPIRequest $request): JsonResponse
    {
        $input = $request->only('purpose', 'channel', );
        //Call OTP send
        $otp_code = $this->otp_send($user, $input);
        return $this->response($otp_code, 'OTP send successfully !', 200);
    }

    public function otp_verify(User $user, OTPAPIRequest $request): JsonResponse
    {
        $input = $request->only('purpose', 'otp_code');
        $fields = array('updated_at' => now());
        $otp_request = OTPRequest::where('user_id', $user->id)
                        ->where('purpose', $input['purpose'])
                        ->where('status', "pending")
                        ->orderBy('created_at', 'desc')
                        ->first();
        //No matches
        if(!$otp_request){
            return $this->error("No OTP request matches this record !", 401);
        }
        $fields['attempt_count'] = $otp_request->attempt_count + 1;
        $this->otpRequestRepository->update($otp_request, $fields);

        //Check expired at
        $today = now();
        if($today->greaterThan($otp_request->expires_at)){
            return $this->error("Expired OTP request !", 401);
        }

        //Check otp_code
        if (!Hash::check($input['otp_code'], $otp_request->otp_code)) {
            return $this->error("Invalid OTP !", 401);
        }

        $fields['status'] = "verified";
        $this->otpRequestRepository->update($otp_request, $fields);

        //Phone verified at
        $user->phone_verified_at = now();
        $user->save();

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
     *      summary="Me",
     *      tags={"Auth"},
     *      description="Logout",
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
     * @OA\Post(
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
     *                  property="success",
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

        return $this->success('Successfully logged out');
    }
}
