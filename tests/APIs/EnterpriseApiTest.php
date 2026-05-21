<?php

namespace Tests\APIs;

use App\Models\Enterprise;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\ApiTestTrait;
use Tests\TestCase;

class EnterpriseApiTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions, WithoutMiddleware;

    /**
     * @test
     */
    public function test_create_enterprise()
    {
        $enterprise = Enterprise::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/enterprises', $enterprise
        );

        $this->assertApiResponse($enterprise);
    }

    /**
     * @test
     */
    public function test_read_enterprise()
    {
        $enterprise = Enterprise::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/enterprises/'.$enterprise->id
        );

        $this->assertApiResponse($enterprise->toArray());
    }

    /**
     * @test
     */
    public function test_update_enterprise()
    {
        $enterprise = Enterprise::factory()->create();
        $editedEnterprise = Enterprise::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/enterprises/'.$enterprise->id,
            $editedEnterprise
        );

        $this->assertApiResponse($editedEnterprise);
    }

    /**
     * @test
     */
    public function test_delete_enterprise()
    {
        $enterprise = Enterprise::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/enterprises/'.$enterprise->id
        );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/enterprises/'.$enterprise->id
        );

        $this->response->assertStatus(404);
    }
}
