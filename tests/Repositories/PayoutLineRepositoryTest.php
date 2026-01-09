<?php

namespace Tests\Repositories;

use App\Models\PayoutLine;
use App\Infrastructure\Persistence\PayoutLineRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class PayoutLineRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected PayoutLineRepository $payoutLineRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->payoutLineRepo = app(PayoutLineRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_payout_line()
    {
        $payoutLine = PayoutLine::factory()->make()->toArray();

        $createdPayoutLine = $this->payoutLineRepo->create($payoutLine);

        $createdPayoutLine = $createdPayoutLine->toArray();
        $this->assertArrayHasKey('id', $createdPayoutLine);
        $this->assertNotNull($createdPayoutLine['id'], 'Created PayoutLine must have id specified');
        $this->assertNotNull(PayoutLine::find($createdPayoutLine['id']), 'PayoutLine with given id must be in DB');
        $this->assertModelData($payoutLine, $createdPayoutLine);
    }

    /**
     * @test read
     */
    public function test_read_payout_line()
    {
        $payoutLine = PayoutLine::factory()->create();

        $dbPayoutLine = $this->payoutLineRepo->find($payoutLine->id);

        $dbPayoutLine = $dbPayoutLine->toArray();
        $this->assertModelData($payoutLine->toArray(), $dbPayoutLine);
    }

    /**
     * @test update
     */
    public function test_update_payout_line()
    {
        $payoutLine = PayoutLine::factory()->create();
        $fakePayoutLine = PayoutLine::factory()->make()->toArray();

        $updatedPayoutLine = $this->payoutLineRepo->update($fakePayoutLine, $payoutLine->id);

        $this->assertModelData($fakePayoutLine, $updatedPayoutLine->toArray());
        $dbPayoutLine = $this->payoutLineRepo->find($payoutLine->id);
        $this->assertModelData($fakePayoutLine, $dbPayoutLine->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_payout_line()
    {
        $payoutLine = PayoutLine::factory()->create();

        $resp = $this->payoutLineRepo->delete($payoutLine->id);

        $this->assertTrue($resp);
        $this->assertNull(PayoutLine::find($payoutLine->id), 'PayoutLine should not exist in DB');
    }
}
