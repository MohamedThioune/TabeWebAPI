<?php

namespace Tests\Repositories;

use App\Models\Enterprise;
use App\Infrastructure\Persistence\EnterpriseRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class EnterpriseRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected EnterpriseRepository $enterpriseRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->enterpriseRepo = app(EnterpriseRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_enterprise()
    {
        $enterprise = Enterprise::factory()->make()->toArray();

        $createdEnterprise = $this->enterpriseRepo->create($enterprise);

        $createdEnterprise = $createdEnterprise->toArray();
        $this->assertArrayHasKey('id', $createdEnterprise);
        $this->assertNotNull($createdEnterprise['id'], 'Created Enterprise must have id specified');
        $this->assertNotNull(Enterprise::find($createdEnterprise['id']), 'Enterprise with given id must be in DB');
        $this->assertModelData($enterprise, $createdEnterprise);
    }

    /**
     * @test read
     */
    public function test_read_enterprise()
    {
        $enterprise = Enterprise::factory()->create();

        $dbEnterprise = $this->enterpriseRepo->find($enterprise->id);

        $dbEnterprise = $dbEnterprise->toArray();
        $this->assertModelData($enterprise->toArray(), $dbEnterprise);
    }

    /**
     * @test update
     */
    public function test_update_enterprise()
    {
        $enterprise = Enterprise::factory()->create();
        $fakeEnterprise = Enterprise::factory()->make()->toArray();

        $updatedEnterprise = $this->enterpriseRepo->update($fakeEnterprise, $enterprise->id);

        $this->assertModelData($fakeEnterprise, $updatedEnterprise->toArray());
        $dbEnterprise = $this->enterpriseRepo->find($enterprise->id);
        $this->assertModelData($fakeEnterprise, $dbEnterprise->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_enterprise()
    {
        $enterprise = Enterprise::factory()->create();

        $resp = $this->enterpriseRepo->delete($enterprise->id);

        $this->assertTrue($resp);
        $this->assertNull(Enterprise::find($enterprise->id), 'Enterprise should not exist in DB');
    }
}
