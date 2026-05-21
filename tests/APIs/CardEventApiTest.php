<?php

namespace Tests\APIs;

use App\Models\CardEvent;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\ApiTestTrait;
use Tests\TestCase;

class CardEventApiTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions, WithoutMiddleware;

    /**
     * @test
     */
    public function test_create_card_event()
    {
        $cardEvent = CardEvent::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/card-events', $cardEvent
        );

        $this->assertApiResponse($cardEvent);
    }

    /**
     * @test
     */
    public function test_read_card_event()
    {
        $cardEvent = CardEvent::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/card-events/'.$cardEvent->id
        );

        $this->assertApiResponse($cardEvent->toArray());
    }

    /**
     * @test
     */
    public function test_update_card_event()
    {
        $cardEvent = CardEvent::factory()->create();
        $editedCardEvent = CardEvent::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/card-events/'.$cardEvent->id,
            $editedCardEvent
        );

        $this->assertApiResponse($editedCardEvent);
    }

    /**
     * @test
     */
    public function test_delete_card_event()
    {
        $cardEvent = CardEvent::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/card-events/'.$cardEvent->id
        );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/card-events/'.$cardEvent->id
        );

        $this->response->assertStatus(404);
    }
}
