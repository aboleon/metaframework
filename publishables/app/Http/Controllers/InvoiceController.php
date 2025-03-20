<?php

namespace App\Http\Controllers;

use App\DataTables\InvoiceDataTable;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use MetaFramework\Services\Validation\ValidationTrait;
use MetaFramework\Traits\Ajax;
use Throwable;

class InvoiceController extends Controller
{
    use Ajax;
    use ValidationTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Event $event): JsonResponse|View
    {
        $dataTable = new InvoiceDataTable($event);
        return $dataTable->render('invoices.datatable.index', ['event' => $event]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): self
    {
        $this->validation_rules = [
            'order_id' => 'required|exists:orders,id'
        ];
        $this->validation_messages = [
            'order_id.required' => __('validation.required', ['attribute' => "L'ID de la commande"]),
            'order_id.exists' => __('validation.exists', ['attribute' => "La commande"]),
        ];
        $this->validation();

        if (!request()->has('proforma') && Invoice::where('order_id', $this->validatedData('order_id'))->whereNull('proforma')->exists()) {
            $this->responseError("Une facture a déjà été éditée pour cette commande");
            return $this;
        }

        try {

            $data = [
                'order_id' => $this->validatedData('order_id'),
                'created_by' => auth()->id()
            ];

            if (request()->has('proforma')) {
                $data['proforma'] = true;
            }

            $invoice = Invoice::create($data);

            $this->responseSuccess("La facture " . (request()->has('proforma') ? 'proforma' : '') . " a été créée.");
            $this->responseElement("order_uuid",  Order::where('id', $this->validatedData('order_id'))->value('uuid'));
            $this->responseElement("invoice_id",  $invoice->id);
            $this->responseElement("invoice_date",  date('d/m/Y'));


        } catch (Throwable $e) {
            $this->responseException($e);
        }

        return $this;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
