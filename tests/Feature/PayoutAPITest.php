<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\ApiTestTrait as ApiTest;
use App\Models\Payout;
use App\Models\Transaction;
use App\Models\User;
use App\Models\GiftCard;
use Illuminate\Support\Facades\Log;
use App\Infrastructure\External\Payment\PaymentGateway;
use App\Infrastructure\External\Payment\DTO\PaymentResponseDTO;

class PayoutAPITest extends TestCase
{
    use ApiTest, DatabaseTransactions;
    public const SUCCESS_TEXT = 'Transaction completed successfully';
    private static array $pattern_payout = [
        'id',
        'gross_amount',
        'net_amount',
        'commentary',
        'fees',
        'currency',
        'status',
        'transactions' => [
            '*' => [
                'id',
                'status',
                'amount',
                'currency',
                'gift_card',
                'created_at',
                'updated_at'
            ]
        ],
        'total_transactions',
        'created_at',
        'updated_at'
    ];

    /**
     * @test request a fail payout 
    */
    public function test_request_fail_payout(): void
    {
        //Acting as a partner
        $user = ApiTest::actingAsPartner();

        //Inputs
        $headers = [
            'Idempotency-Key' => fake()->uuid(),
        ];
        $data = [
            'withdraw_mode' => "cash",
            'commentary' => "Hello, I want to withdraw my money [Testing]",
        ];

        //mock paydunya call
        $this->mock(PaymentGateway::class, function ($mock){
            $mock->shouldReceive('initiate_refund');
        });

        //callout 
        $this->response = $this->json(
            'POST',
            '/api/payouts/request?show_transactions=1', $data, $headers
        );

        // var_dump($this->response->getContent());

        //Assert failure response (No remaining transactions to payout)
        $this->response->assertStatus(404);
    }

    /**
     * @test request a success payout 
    */
    public function test_request_success_payout(): void
    {
        //Acting as a partner
        $user = ApiTest::actingAsPartner();

        //Inputs
        $headers = [
            'Idempotency-Key' => fake()->uuid(),
        ];
        $data = [
            'withdraw_mode' => "cash",
            'commentary' => "Hello, I want to withdraw my money [Testing]",
        ];

        //Create transactions to payout
        $giftCard = GiftCard::factory()->create();
        $transactions = Transaction::factory()->count(3)->create([
            'status' => 'captured',
            'user_id' => $user->id,
            'gift_card_id' => $giftCard->id
        ]);

        //mock paydunya call
        $this->mock(PaymentGateway::class, function ($mock){
            $mock->shouldReceive('initiate_refund');
            // ->once()
            // ->andReturn(new PaymentResponseDTO(
            //     disburse_token : 'test_hash_token',
            // ));
        });

        //callout 
        $this->response = $this->json(
            'POST',
            '/api/payouts/request?show_transactions=1', $data, $headers
        );

        // var_dump($this->response->getContent());

        //Assert success 
        $this->assertApiSuccess();

        //Assert the structure of the response
        $this->response->assertJsonStructure([
            'success',
            'data' => self::$pattern_payout,
            'message',
        ]);

    }

}
