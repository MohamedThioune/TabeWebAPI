<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateInvoiceAPIRequest;
use App\Http\Requests\API\UpdateInvoiceAPIRequest;
use App\Models\Invoice;
use App\Infrastructure\Persistence\InvoiceRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Http\Resources\InvoiceResource;

/**
 * Class InvoiceController
 */

class InvoiceAPIController extends AppBaseController
{
    /** @var  InvoiceRepository */
    private $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepo)
    {
        $this->invoiceRepository = $invoiceRepo;
    }

    /**
     * @OA\Get(
     *      path="/invoices",
     *      summary="getInvoiceList",
     *      tags={"Invoice"},
     *      description="Get all Invoices",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/Invoice")
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $invoices = $this->invoiceRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse(InvoiceResource::collection($invoices), 'Invoices retrieved successfully');
    }

    /**
     * @OA\Post(
     *      path="/invoices",
     *      summary="createInvoice",
     *      tags={"Invoice"},
     *      description="Create Invoice",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Invoice")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/Invoice"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateInvoiceAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $invoice = $this->invoiceRepository->create($input);

        return $this->sendResponse(new InvoiceResource($invoice), 'Invoice saved successfully');
    }

    /**
     * @OA\Get(
     *      path="/invoices/{id}",
     *      summary="getInvoiceItem",
     *      tags={"Invoice"},
     *      description="Get Invoice",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Invoice",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/Invoice"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function show($id): JsonResponse
    {
        /** @var Invoice $invoice */
        $invoice = $this->invoiceRepository->find($id);

        if (empty($invoice)) {
            return $this->sendError('Invoice not found');
        }

        return $this->sendResponse(new InvoiceResource($invoice), 'Invoice retrieved successfully');
    }

    /**
     * @OA\Put(
     *      path="/invoices/{id}",
     *      summary="updateInvoice",
     *      tags={"Invoice"},
     *      description="Update Invoice",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Invoice",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/Invoice")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  ref="#/components/schemas/Invoice"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateInvoiceAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var Invoice $invoice */
        $invoice = $this->invoiceRepository->find($id);

        if (empty($invoice)) {
            return $this->sendError('Invoice not found');
        }

        $invoice = $this->invoiceRepository->update($input, $id);

        return $this->sendResponse(new InvoiceResource($invoice), 'Invoice updated successfully');
    }

    /**
     * @OA\Delete(
     *      path="/invoices/{id}",
     *      summary="deleteInvoice",
     *      tags={"Invoice"},
     *      description="Delete Invoice",
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Invoice",
     *           @OA\Schema(
     *             type="integer"
     *          ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="string"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function destroy($id): JsonResponse
    {
        /** @var Invoice $invoice */
        $invoice = $this->invoiceRepository->find($id);

        if (empty($invoice)) {
            return $this->sendError('Invoice not found');
        }

        $invoice->delete();

        return $this->sendSuccess('Invoice deleted successfully');
    }
}
