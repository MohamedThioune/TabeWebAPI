<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Payout;

class PayoutApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_payout()
    {
        $payout = Payout::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/payouts', $payout
        );

        $this->assertApiResponse($payout);
    }

    /**
     * @test
     */
    public function test_read_payout()
    {
        $payout = Payout::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/payouts/'.$payout->id
        );

        $this->assertApiResponse($payout->toArray());
    }

    /**
     * @test
     */
    public function test_update_payout()
    {
        $payout = Payout::factory()->create();
        $editedPayout = Payout::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/payouts/'.$payout->id,
            $editedPayout
        );

        $this->assertApiResponse($editedPayout);
    }

    /**
     * @test
     */
    public function test_delete_payout()
    {
        $payout = Payout::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/payouts/'.$payout->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/payouts/'.$payout->id
        );

        $this->response->assertStatus(404);
    }
}
