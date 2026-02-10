<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SharedCardNotification;
use App\Infrastructure\External\Payment\PaymentGateway;
use Tests\TestCase;
use Tests\ApiTestTrait as ApiTest;
use App\Models\GiftCard;
use App\Models\Design;
use App\Models\QRSession;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;


class GiftCardAPITest extends TestCase
{
    use ApiTest, DatabaseTransactions;
    private static array $pattern_card = [
            'id',
            'code',
            'belonging_type',
            'type',
            'face_amount',
            'status',
            'expired_at',
            'qr' => [
                'id',
                'status',
                'payload',
                'url',
                'expired_at',
            ],
            'owner' => [
                'customer' => [
                    'first_name',
                    'last_name',
                ],
            ],
            'beneficiary' => [
                'full_name',
                'phone',
                'email',
            ],
            'design' => [
                'id',
                'name',
            ],
            'created_at',
    ];
    private static array $pattern_card_without_beneficiary = [
            'id',
            'code',
            'belonging_type',
            'type',
            'face_amount',
            'status',
            'expired_at',
            'qr' => [
                'id',
                'status',
                'payload',
                'url',
                'expired_at',
            ],
            'owner' => [
                'customer' => [
                    'first_name',
                    'last_name',
                ],
            ],
            'design' => [
                'id',
                'name',
            ],
            'created_at',
    ];
   
    /**
     * @test create gift card with/without beneficiary
    */
    public function test_create_gift_card_with_beneficiary()
    {
        //Acting as : Customer
        ApiTest::actingAsCustomer();

        $headers = [
            'Idempotency-Key' => fake()->uuid(),
        ];

        $data = [
            'belonging_type' => "others",
            'type' => fake()->randomElement(['physical', 'digital']),  
            'face_amount' => fake()->numberBetween(10000, 150000),
            'full_name' => fake()->name(),
            'phone' => fake()->unique()->phoneNumber(),
            'design_id' => fake()->randomElement(Design::pluck('id')),
        ];

        //mock paydunya call
        $reference_number = fake()->regexify('^[A-Za-z0-9]{10}$');
        $this->mock(PaymentGateway::class, function ($mock) use ($reference_number) {
            $mock->shouldReceive('charge')
                ->once()
                ->andReturn((object)[
                    'reference_number' => 'test_' . $reference_number,
                    'response_text' => 'https://paydunya.com/sandbox-checkout/invoice/'. $reference_number,
                    'status' => 'pending',
                ]);
        });

        $this->response = $this->json(
            'POST',
            '/api/gift-cards', $data, $headers
        );

        //assert database insertion
        $this->assertDatabaseHas('invoices', [
            'type' => 'Achat de carte',
            'reference_number' => 'test_' . $reference_number,
            'amount' => $data['face_amount'],
        ]);

        //assert status(200) & the response data matches the correct structure
        $this->response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'gift_card' => self::$pattern_card,
                    'checkout' => [
                        'reference',
                        'url',
                    ],
                ],
                'message',
            ]);

    }
    public function test_create_gift_card_without_beneficiary()
    {
        //Acting as : Customer
        ApiTest::actingAsCustomer();

        $headers = [
            'Idempotency-Key' => fake()->uuid(),
        ];

        $data = [
            'belonging_type' => "myself",
            'type' => fake()->randomElement(['physical', 'digital']),  
            'face_amount' => fake()->numberBetween(10000, 150000),
            'design_id' => fake()->randomElement(Design::pluck('id')),
        ];

        //mock paydunya call
        $reference_number = fake()->regexify('^[A-Za-z0-9]{10}$');
        $this->mock(PaymentGateway::class, function ($mock) use ($reference_number) {
            $mock->shouldReceive('charge')
                ->once()
                ->andReturn((object)[
                    'reference_number' => 'test_' . $reference_number,
                    'response_text' => 'https://paydunya.com/sandbox-checkout/invoice/'. $reference_number,
                    'status' => 'pending',
                ]);
        });
        $this->response = $this->json(
            'POST',
            '/api/gift-cards', $data, $headers
        );

        //assert database insertion
        $this->assertDatabaseHas('invoices', [
            'type' => 'Achat de carte',
            'reference_number' => 'test_' . $reference_number,
            'amount' => $data['face_amount'],
        ]);

        //assert status(200) & the response data matches the correct structure
        $this->response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'gift_card' => self::$pattern_card_without_beneficiary,
                    'checkout' => [
                        'reference',
                        'url',
                    ],
                ],
                'message',
            ]);

    }

    /**
     * @test lists gift card
    */
    public function test_list_gift_card()
    {
        //Acting as : Customer
        $user = ApiTest::actingAsCustomer();

        $giftCard = GiftCard::factory()->create([
            'owner_user_id' => $user?->id,
            'belonging_type' => 'others',
        ]);

        QRSession::factory()->create([
            'gift_card_id' => $giftCard?->id,
        ]);

        $this->response = $this->json(
            'GET',
            '/api/gift-cards?per_page=1&page=1'
        );

        //var_dump($this->response->getContent());

        //assert status(200) & the response data matches the correct structure
        $this->response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'gift_cards' => [
                        '*' => self::$pattern_card,
                    ],
                    'pagination' => ApiTest::$pagination, 
                ],
                'message',
            ]);    
    }

    /**
     * @test share gift card
    */
    public function test_share_gift_card()
    {
        //Mock notification
        Notification::fake();

        //Acting as : Customer
        $user = ApiTest::actingAsCustomer();

        $giftCard = GiftCard::factory()->create([
            'owner_user_id' => $user?->id,
            'belonging_type' => 'others',
        ]);

        $this->response = $this->json(
            'PUT',
            '/api/gift-cards/share/'. $giftCard?->id,
        );

        $this->assertApiSuccess();

        // Assert that a notification was sent to the given user...
        Notification::assertSentTo(
            [$user],
            \App\Notifications\SharedCardNotification::class
        );

    }

    /**
     * test verify code gift card 
    */
    public function test_verify_code_gift_card(){
        //Customer owner
        $customer = Customer::factory()->create();

        //Acting as : Partner
        ApiTest::actingAsPartner();

        $giftCard = GiftCard::factory()->create([
            'owner_user_id' => $customer->user_id,
            'belonging_type' => 'others',
        ]);

        QRSession::factory()->create([
            'gift_card_id' => $giftCard->id,
        ]);

        $data = [
            'code' => $giftCard->code
        ];

        $this->response = $this->json(
            'POST',
            '/api/users/verify/card', 
            $data
        );

        //assert status(200) & the response data matches the correct structure
        $this->response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => self::$pattern_card,
                'message',
            ]);
    }

    /**
     * test verify qr gift card 
    */
    public function test_verify_qr_gift_card(){
        //Customer owner
        $customer = Customer::factory()->create();

        //Acting as : Partner
        ApiTest::actingAsPartner();

        $giftCard = GiftCard::factory()->create([
            'owner_user_id' => $customer->user_id,
            'belonging_type' => 'others',
        ]);

        $qr = QRSession::factory()->create([
            'gift_card_id' => $giftCard->id,
        ]);

        $data = [
            'payload' => $qr->token
        ];

        $this->response = $this->json(
            'PATCH',
            '/api/qr-sessions', 
            $data
        );

        // var_dump($this->response->getContent());

        //assert status(200) & the response data matches the correct structure
        $this->response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => self::$pattern_card,
                'message',
            ]);

    }
   
}
