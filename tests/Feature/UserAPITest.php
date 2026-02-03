<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PushWhatsAppNotification;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Tests\ApiTestTrait as ApiTest;
use App\Models\User;
use App\Models\Customer;
use App\Models\Partner;
use App\Domain\Users\ValueObjects\Type;

class UserAPITest extends TestCase
{
    use ApiTest, DatabaseTransactions;
    private static array $pattern_customer = [
            'customer' => [
                'first_name',
                'last_name',
            ],
            'sigla',
            'phone',
            'country',
        ];

    private static array $pattern_partner = [
            'partner' => [
                'name',
                'sector',
            ],
            'sigla',
            'avatar',
            'banner',
            'phone',
            'country',
        ];
        
    /**
     * test register user
    */
    public function test_register_user(): void
    {
        Notification::fake();

        $gender = fake()->randomElement(['male', 'female']);
        $data = [
            'type' => Type::Customer->value,
            'first_name' => fake()->firstName($gender),
            'last_name' => fake()->lastName(),
            'gender' => $gender,
            'email' => fake()->unique()->safeEmail(),
            'phone' => "+221770000000",
            'whatsApp' => "+221770000000",
            'password' => "password123",
            'password_confirmation' => "password123",
        ];

        $this->response = $this->json(
            'POST',
            '/api/auth/register',
            $data
        );

        // var_dump($this->response->getContent());

        $this->response->assertStatus(201);

        // assert structure of response
        $this->response->assertJsonStructure([
            'data' => [
                'user' => self::$pattern_customer,
            ]
        ]);

        // assert database insertion
        $this->assertDatabaseHas('users', [
            'phone' => $data['phone'],
            'whatsapp' => 'whatsapp:' . $data['whatsApp'],
        ]);
        $this->assertDatabaseHas('customers', [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
        ]);

        // Assert that a notification was sent to the given user...
        $user = User::where('phone', $data['phone'])->first();
        Notification::assertSentTo(
            [$user],
            \App\Notifications\PushWhatsAppNotification::class
        );
    }

    /** 
     * test verify OTP code 
    */
    public function test_verify_otp_code(): void
    {
        $data =  [
                'otp_code' => '123456',
                'purpose' => 'login',
            ];
        //Create user
        $user = User::factory()->create([
            'phone' => '+221770000001',
        ]);
        $customer = Customer::factory()->create([
            'user_id' => $user->id,
        ]);

        //Cache store OTP
        $otp_code = '123456';
        Cache::put('otp_code_' . $user->phone, bcrypt($data['otp_code']), now()->addSeconds(120));

        //Request OTP
        $this->response = $this->json(
            'PUT',
            '/api/auth/otp/verify/' . $user->phone,
            $data
        );

        // var_dump($this->response->getContent());

        $this->response->assertStatus(200);

        // assert structure of response
        $this->response->assertJsonStructure([
            'status',
            'data' => [
                'user' => self::$pattern_customer,
                'token',
                'type',
            ],
            'message',
        ]);
    }

    /**
     * test get authenticated user info
    */
    public function test_get_authenticated_user(): void
    {
        //Acting as : Customer
        $customer = ApiTest::actingAsCustomer();

        $this->response = $this->json(
            'GET',
            '/api/me',
        );

        // var_dump($this->response->getContent());

        $this->response->assertStatus(200);

        // assert structure of response
        $this->response->assertJsonStructure([
            'status',
            'data' => self::$pattern_customer,
            'message',
        ]);
    }

    /** 
     * test get partner list
    */
    public function test_get_partner_list(): void
    {
        //Create partners
        Partner::factory()->count(8)->create();
        $this->response = $this->json(
            'GET',
            '/api/partners?per_page=8&page=1',
        );

        // var_dump($this->response->getContent());

        $this->assertApiSuccess();

        // assert structure of response
        $this->response->assertJsonStructure([
            'success',
            'data' => [
                'users' => [
                    '*' => self::$pattern_partner,
                ],
                'pagination' => ApiTest::$pagination, 
            ],
            'message',
        ]);
    }

}
