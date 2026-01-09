<?php

namespace Tests\Repositories;

use App\Models\Payout;
use App\Infrastructure\Persistence\PayoutRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class PayoutRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected PayoutRepository $payoutRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->payoutRepo = app(PayoutRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_payout()
    {
        $payout = Payout::factory()->make()->toArray();

        $createdPayout = $this->payoutRepo->create($payout);

        $createdPayout = $createdPayout->toArray();
        $this->assertArrayHasKey('id', $createdPayout);
        $this->assertNotNull($createdPayout['id'], 'Created Payout must have id specified');
        $this->assertNotNull(Payout::find($createdPayout['id']), 'Payout with given id must be in DB');
        $this->assertModelData($payout, $createdPayout);
    }

    /**
     * @test read
     */
    public function test_read_payout()
    {
        $payout = Payout::factory()->create();

        $dbPayout = $this->payoutRepo->find($payout->id);

        $dbPayout = $dbPayout->toArray();
        $this->assertModelData($payout->toArray(), $dbPayout);
    }

    /**
     * @test update
     */
    public function test_update_payout()
    {
        $payout = Payout::factory()->create();
        $fakePayout = Payout::factory()->make()->toArray();

        $updatedPayout = $this->payoutRepo->update($fakePayout, $payout->id);

        $this->assertModelData($fakePayout, $updatedPayout->toArray());
        $dbPayout = $this->payoutRepo->find($payout->id);
        $this->assertModelData($fakePayout, $dbPayout->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_payout()
    {
        $payout = Payout::factory()->create();

        $resp = $this->payoutRepo->delete($payout->id);

        $this->assertTrue($resp);
        $this->assertNull(Payout::find($payout->id), 'Payout should not exist in DB');
    }
}
