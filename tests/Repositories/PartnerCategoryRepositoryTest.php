<?php

namespace Tests\Repositories;

use App\Models\UserCategory;
use App\Infrastructure\Persistence\PartnerCategoryRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class PartnerCategoryRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected PartnerCategoryRepository $partnerCategoryRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->partnerCategoryRepo = app(PartnerCategoryRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_partner_category()
    {
        $partnerCategory = UserCategory::factory()->make()->toArray();

        $createdPartnerCategory = $this->partnerCategoryRepo->create($partnerCategory);

        $createdPartnerCategory = $createdPartnerCategory->toArray();
        $this->assertArrayHasKey('id', $createdPartnerCategory);
        $this->assertNotNull($createdPartnerCategory['id'], 'Created UserCategory must have id specified');
        $this->assertNotNull(UserCategory::find($createdPartnerCategory['id']), 'UserCategory with given id must be in DB');
        $this->assertModelData($partnerCategory, $createdPartnerCategory);
    }

    /**
     * @test read
     */
    public function test_read_partner_category()
    {
        $partnerCategory = UserCategory::factory()->create();

        $dbPartnerCategory = $this->partnerCategoryRepo->find($partnerCategory->id);

        $dbPartnerCategory = $dbPartnerCategory->toArray();
        $this->assertModelData($partnerCategory->toArray(), $dbPartnerCategory);
    }

    /**
     * @test update
     */
    public function test_update_partner_category()
    {
        $partnerCategory = UserCategory::factory()->create();
        $fakePartnerCategory = UserCategory::factory()->make()->toArray();

        $updatedPartnerCategory = $this->partnerCategoryRepo->update($fakePartnerCategory, $partnerCategory->id);

        $this->assertModelData($fakePartnerCategory, $updatedPartnerCategory->toArray());
        $dbPartnerCategory = $this->partnerCategoryRepo->find($partnerCategory->id);
        $this->assertModelData($fakePartnerCategory, $dbPartnerCategory->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_partner_category()
    {
        $partnerCategory = UserCategory::factory()->create();

        $resp = $this->partnerCategoryRepo->delete($partnerCategory->id);

        $this->assertTrue($resp);
        $this->assertNull(UserCategory::find($partnerCategory->id), 'UserCategory should not exist in DB');
    }
}
