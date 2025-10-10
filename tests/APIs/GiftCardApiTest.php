<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\GiftCard;

class GiftCardApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_gift_card()
    {
        $giftCard = GiftCard::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/gift-cards', $giftCard
        );

        $this->assertApiResponse($giftCard);
    }

    /**
     * @test
     */
    public function test_read_gift_card()
    {
        $giftCard = GiftCard::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/gift-cards/'.$giftCard->id
        );

        $this->assertApiResponse($giftCard->toArray());
    }

    /**
     * @test
     */
    public function test_update_gift_card()
    {
        $giftCard = GiftCard::factory()->create();
        $editedGiftCard = GiftCard::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/gift-cards/'.$giftCard->id,
            $editedGiftCard
        );

        $this->assertApiResponse($editedGiftCard);
    }

    /**
     * @test
     */
    public function test_delete_gift_card()
    {
        $giftCard = GiftCard::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/gift-cards/'.$giftCard->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/gift-cards/'.$giftCard->id
        );

        $this->response->assertStatus(404);
    }
}
