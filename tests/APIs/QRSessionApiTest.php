<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\QRSession;

class QRSessionApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_q_r_session()
    {
        $qRSession = QRSession::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/q-r-sessions', $qRSession
        );

        $this->assertApiResponse($qRSession);
    }

    /**
     * @test
     */
    public function test_read_q_r_session()
    {
        $qRSession = QRSession::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/q-r-sessions/'.$qRSession->id
        );

        $this->assertApiResponse($qRSession->toArray());
    }

    /**
     * @test
     */
    public function test_update_q_r_session()
    {
        $qRSession = QRSession::factory()->create();
        $editedQRSession = QRSession::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/q-r-sessions/'.$qRSession->id,
            $editedQRSession
        );

        $this->assertApiResponse($editedQRSession);
    }

    /**
     * @test
     */
    public function test_delete_q_r_session()
    {
        $qRSession = QRSession::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/q-r-sessions/'.$qRSession->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/q-r-sessions/'.$qRSession->id
        );

        $this->response->assertStatus(404);
    }
}
