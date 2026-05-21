<?php

namespace Tests\APIs;

use App\Models\Beneficiary;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\ApiTestTrait;
use Tests\TestCase;

class BeneficiaryApiTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions, WithoutMiddleware;

    /**
     * @test
     */
    public function test_create_beneficiary()
    {
        $beneficiary = Beneficiary::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/beneficiaries', $beneficiary
        );

        $this->assertApiResponse($beneficiary);
    }

    /**
     * @test
     */
    public function test_read_beneficiary()
    {
        $beneficiary = Beneficiary::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/beneficiaries/'.$beneficiary->id
        );

        $this->assertApiResponse($beneficiary->toArray());
    }

    /**
     * @test
     */
    public function test_update_beneficiary()
    {
        $beneficiary = Beneficiary::factory()->create();
        $editedBeneficiary = Beneficiary::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/beneficiaries/'.$beneficiary->id,
            $editedBeneficiary
        );

        $this->assertApiResponse($editedBeneficiary);
    }

    /**
     * @test
     */
    public function test_delete_beneficiary()
    {
        $beneficiary = Beneficiary::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/beneficiaries/'.$beneficiary->id
        );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/beneficiaries/'.$beneficiary->id
        );

        $this->response->assertStatus(404);
    }
}
