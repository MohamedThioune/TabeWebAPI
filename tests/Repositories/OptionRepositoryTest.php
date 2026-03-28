<?php

namespace Tests\Repositories;

use App\Models\Option;
use App\Infrastructure\Persistence\OptionRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class OptionRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected OptionRepository $optionRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->optionRepo = app(OptionRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_option()
    {
        $option = Option::factory()->make()->toArray();

        $createdOption = $this->optionRepo->create($option);

        $createdOption = $createdOption->toArray();
        $this->assertArrayHasKey('id', $createdOption);
        $this->assertNotNull($createdOption['id'], 'Created Option must have id specified');
        $this->assertNotNull(Option::find($createdOption['id']), 'Option with given id must be in DB');
        $this->assertModelData($option, $createdOption);
    }

    /**
     * @test read
     */
    public function test_read_option()
    {
        $option = Option::factory()->create();

        $dbOption = $this->optionRepo->find($option->id);

        $dbOption = $dbOption->toArray();
        $this->assertModelData($option->toArray(), $dbOption);
    }

    /**
     * @test update
     */
    public function test_update_option()
    {
        $option = Option::factory()->create();
        $fakeOption = Option::factory()->make()->toArray();

        $updatedOption = $this->optionRepo->update($fakeOption, $option->id);

        $this->assertModelData($fakeOption, $updatedOption->toArray());
        $dbOption = $this->optionRepo->find($option->id);
        $this->assertModelData($fakeOption, $dbOption->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_option()
    {
        $option = Option::factory()->create();

        $resp = $this->optionRepo->delete($option->id);

        $this->assertTrue($resp);
        $this->assertNull(Option::find($option->id), 'Option should not exist in DB');
    }
}
