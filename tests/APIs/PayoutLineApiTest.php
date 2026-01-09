<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\PayoutLine;

class PayoutLineApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_payout_line()
    {
        $payoutLine = PayoutLine::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/payout-lines', $payoutLine
        );

        $this->assertApiResponse($payoutLine);
    }

    /**
     * @test
     */
    public function test_read_payout_line()
    {
        $payoutLine = PayoutLine::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/payout-lines/'.$payoutLine->id
        );

        $this->assertApiResponse($payoutLine->toArray());
    }

    /**
     * @test
     */
    public function test_update_payout_line()
    {
        $payoutLine = PayoutLine::factory()->create();
        $editedPayoutLine = PayoutLine::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/payout-lines/'.$payoutLine->id,
            $editedPayoutLine
        );

        $this->assertApiResponse($editedPayoutLine);
    }

    /**
     * @test
     */
    public function test_delete_payout_line()
    {
        $payoutLine = PayoutLine::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/payout-lines/'.$payoutLine->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/payout-lines/'.$payoutLine->id
        );

        $this->response->assertStatus(404);
    }
}
