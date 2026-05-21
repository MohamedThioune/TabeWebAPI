<?php

namespace Tests\APIs;

use App\Models\QrSession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\ApiTestTrait;
use Tests\TestCase;

class QRSessionApiTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions, WithoutMiddleware;

    /**
     * @test
     */
    public function test_create_q_r_session()
    {
        $qRSession = QrSession::factory()->make()->toArray();

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
        $qRSession = QrSession::factory()->create();

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
        $qRSession = QrSession::factory()->create();
        $editedQrSession = QrSession::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/q-r-sessions/'.$qRSession->id,
            $editedQrSession
        );

        $this->assertApiResponse($editedQrSession);
    }

    /**
     * @test
     */
    public function test_delete_q_r_session()
    {
        $qRSession = QrSession::factory()->create();

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
