<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\UserCategory;

class PartnerCategoryApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_partner_category()
    {
        $partnerCategory = UserCategory::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/partner-categories', $partnerCategory
        );

        $this->assertApiResponse($partnerCategory);
    }

    /**
     * @test
     */
    public function test_read_partner_category()
    {
        $partnerCategory = UserCategory::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/partner-categories/'.$partnerCategory->id
        );

        $this->assertApiResponse($partnerCategory->toArray());
    }

    /**
     * @test
     */
    public function test_update_partner_category()
    {
        $partnerCategory = UserCategory::factory()->create();
        $editedPartnerCategory = UserCategory::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/partner-categories/'.$partnerCategory->id,
            $editedPartnerCategory
        );

        $this->assertApiResponse($editedPartnerCategory);
    }

    /**
     * @test
     */
    public function test_delete_partner_category()
    {
        $partnerCategory = UserCategory::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/partner-categories/'.$partnerCategory->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/partner-categories/'.$partnerCategory->id
        );

        $this->response->assertStatus(404);
    }
}
