<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Design;

class DesignApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_design()
    {
        $design = Design::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/designs', $design
        );

        $this->assertApiResponse($design);
    }

    /**
     * @test
     */
    public function test_read_design()
    {
        $design = Design::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/designs/'.$design->id
        );

        $this->assertApiResponse($design->toArray());
    }

    /**
     * @test
     */
    public function test_update_design()
    {
        $design = Design::factory()->create();
        $editedDesign = Design::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/designs/'.$design->id,
            $editedDesign
        );

        $this->assertApiResponse($editedDesign);
    }

    /**
     * @test
     */
    public function test_delete_design()
    {
        $design = Design::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/designs/'.$design->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/designs/'.$design->id
        );

        $this->response->assertStatus(404);
    }
}
