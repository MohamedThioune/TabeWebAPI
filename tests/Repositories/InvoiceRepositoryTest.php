<?php

namespace Tests\Repositories;

use App\Models\Invoice;
use App\Infrastructure\Persistence\InvoiceRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class InvoiceRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    protected InvoiceRepository $invoiceRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->invoiceRepo = app(InvoiceRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_invoice()
    {
        $invoice = Invoice::factory()->make()->toArray();

        $createdInvoice = $this->invoiceRepo->create($invoice);

        $createdInvoice = $createdInvoice->toArray();
        $this->assertArrayHasKey('id', $createdInvoice);
        $this->assertNotNull($createdInvoice['id'], 'Created Invoice must have id specified');
        $this->assertNotNull(Invoice::find($createdInvoice['id']), 'Invoice with given id must be in DB');
        $this->assertModelData($invoice, $createdInvoice);
    }

    /**
     * @test read
     */
    public function test_read_invoice()
    {
        $invoice = Invoice::factory()->create();

        $dbInvoice = $this->invoiceRepo->find($invoice->id);

        $dbInvoice = $dbInvoice->toArray();
        $this->assertModelData($invoice->toArray(), $dbInvoice);
    }

    /**
     * @test update
     */
    public function test_update_invoice()
    {
        $invoice = Invoice::factory()->create();
        $fakeInvoice = Invoice::factory()->make()->toArray();

        $updatedInvoice = $this->invoiceRepo->update($fakeInvoice, $invoice->id);

        $this->assertModelData($fakeInvoice, $updatedInvoice->toArray());
        $dbInvoice = $this->invoiceRepo->find($invoice->id);
        $this->assertModelData($fakeInvoice, $dbInvoice->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_invoice()
    {
        $invoice = Invoice::factory()->create();

        $resp = $this->invoiceRepo->delete($invoice->id);

        $this->assertTrue($resp);
        $this->assertNull(Invoice::find($invoice->id), 'Invoice should not exist in DB');
    }
}
