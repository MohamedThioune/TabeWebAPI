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

       if(isset($user['error'])){
          return $this->error($user['error'], 401);
       }

       $user = new UserResource($model);
        //Create token
        $token = $model->createToken('authToken')->accessToken;

        $infos = [
           'user' => $user,
           'token' => $token,
           'token_type' => 'Bearer'
       ];

       return $this->response($infos, 'User registered successfully !', 201);
    }

    public function otp_request(User $user, OTPAPIRequest $request): JsonResponse
    {
        $input = $request->only('purpose', 'channel', );

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
            'expires_at' => now()->addMinutes(30),
            'created_at' => now(),
        ];
        $instanceOTP = $this->otpRequestRepository->save($dtoOTP);

        return $this->success('OTP send successfully !', 200);
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
        return $this->response(new UserResource($user), 'OTP verify successfully !', 200);

    }

    public function me(Request $request){
        $user = $request->user();

        if(empty($user)){
            return $this->error("User not found", 401);
        }

        return $this->response(new UserResource($user), 'User successfully retrived !', 200);
    }
}
