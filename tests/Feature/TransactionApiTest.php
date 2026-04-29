<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TransactionNotification;
use Tests\TestCase;
use Tests\ApiTestTrait as ApiTest;
use App\Models\Transaction;
use App\Models\GiftCard;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TransactionApiTest extends TestCase
{
    use ApiTest, WithoutMiddleware, DatabaseTransactions;
    private static array $pattern_transaction = [
        'id',
        'status',
        'amount',
        'currency',
        'gift_card' => [
            'id',
            'code',
            'belonging_type',
            'type',
            'face_amount',
            'status',
            'expired_at',
        ],        
        'created_at',
    ];

    /**
     * @test start a gift card transaction
    */
    public function test_start_transaction()
    {
        //Mock notification
        Notification::fake();

        //Acting as a partner
        $partner = ApiTest::actingAsPartner();

        //Create a gift card  
        $giftCard = GiftCard::factory()->create();
        $owner = $giftCard->user;

        $data = [
            'amount' => $giftCard->face_amount,
            'gift_card_id' => $giftCard->id,
        ];

        $this->response = $this->json(
            'POST',
            '/api/transactions', $data
        );

        // Assert that a notification was sent to the given user...
        Notification::assertSentTo(
            [$owner],
            \App\Notifications\PushBeneficiarySMSNotification::class
        );

        // Assert database insertion 
        $this->assertDatabaseHas('transactions', [
            'status' => 'authorized',
            'amount' => $data['amount'],
            'gift_card_id' => $data['gift_card_id'],
            'user_id' => $partner->id,
        ]);
        $this->assertDatabaseHas('gift_cards', [
            'status' => 'inactive',
        ]);
        
        // Assert status(200) & the response data matches the correct structure
        $this->response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'transaction' => self::$pattern_transaction,
                ],
                'message',
            ]);                
    }

    /**
     * @test confirm a gift card transaction
    */
    // public function test_confirm_transaction()
    // {
    //     //Mock notification
    //     Notification::fake();

    //     //Acting as a partner
    //     $partner = ApiTest::actingAsPartner();

    //     //Create a gift card  
    //     $giftCard = GiftCard::factory()->create();
    //     $owner = $giftCard->user;

    //     //Create a transaction "authorized" status
    //     $transaction = Transaction::factory()->create([
    //         'status' => 'captured',
    //         'amount' => $giftCard->face_amount,
    //         'gift_card_id' => $giftCard->id,
    //         'user_id' => $partner->id,
    //     ]);

    //     //Cache store OTP
    //     $otp_code = rand(100000, 999999);
    //     Cache::put(
    //         'otp_code:' . $transaction->id, (string) $otp_code, now()->addMinutes(30)
    //     );
    //     $data = [
    //         'otp_code' => (string) $otp_code,
    //         'action' => "confirm"
    //     ];

    //     $this->response = $this->json(
    //         'POST',
    //         '/api/transactions/confirm/' . $transaction->id, $data
    //     );

    //     // Assert database insertion 
    //     $this->assertDatabaseHas('transactions', [
    //         'status' => 'captured',
    //         'amount' => $giftCard->face_amount,
    //         'gift_card_id' => $giftCard->id,
    //         'user_id' => $partner->id,
    //     ]);
    //     $this->assertDatabaseHas('gift_cards', [
    //         'status' => 'used',
    //     ]);

    //     // Assert that a notification was sent to the given user...
    //     Notification::assertSentTo(
    //         [$owner],
    //         \App\Notifications\TransactionNotification::class
    //     );
    //     Notification::assertSentTo(
    //         [$partner],
    //         \App\Notifications\TransactionNotification::class
    //     );
        
    //     // Assert status(200) & the response data matches the correct structure
    //     $this->response
    //         ->assertStatus(200)
    //         ->assertJsonStructure([
    //             'success',
    //             'data' => [
    //                 'transaction' => self::$pattern_transaction,
    //             ],
    //             'message',
    //         ]);                
    // }

    /**
     * @test list transactions
    */
    public function test_list_transaction()
    {
        //Acting as a partner
        $partner = ApiTest::actingAsPartner();

        $transactions = Transaction::factory()->count(3)->create([
            'user_id' => $partner->id,
            'status' => 'authorized',
        ]);

        $this->response = $this->json(
            'GET',
            '/api/transactions'
        );

        //assert status(200) & the response data matches the correct structure
        $this->response
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'transactions' => [
                        '*' => self::$pattern_transaction,
                    ],
                    'pagination' => ApiTest::$pagination, 
                ],
                'message',
            ]);  
    }

}
