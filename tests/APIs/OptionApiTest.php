<?php

namespace Tests\APIs;

use App\Models\Option;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\ApiTestTrait;
use Tests\TestCase;

class OptionApiTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions, WithoutMiddleware;

    /**
     * @test
     */
    public function test_create_option()
    {
        $option = Option::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/options', $option
        );

        $this->assertApiResponse($option);
    }

    /**
     * @test
     */
    public function test_read_option()
    {
        $option = Option::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/options/'.$option->id
        );

        $this->assertApiResponse($option->toArray());
    }

    /**
     * @test
     */
    public function test_update_option()
    {
        $option = Option::factory()->create();
        $editedOption = Option::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/options/'.$option->id,
            $editedOption
        );

        $this->assertApiResponse($editedOption);
    }

    /**
     * @test
     */
    public function test_delete_option()
    {
        $option = Option::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/options/'.$option->id
        );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/options/'.$option->id
        );

        $this->response->assertStatus(404);
    }
}
