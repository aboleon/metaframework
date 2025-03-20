<?php

namespace App\Actions\Account\Order;

use App\Models\EventManager\InvoiceCancel\InvoiceCancel;
use MetaFramework\Traits\Responses;

class OrderInvoiceCancelAction
{

    use Responses;

    public function save(array $data, int $id): array
    {
        $this->enableAjaxMode();

        if (!array_key_exists('invoice_id', $data)) {
            $this->responseError('Numéro de facture manquant');
            return $this->fetchResponse();
        }

        $vatId = $data['vat_id'] ?? 0;
        if(0 === (int)$vatId){
            $this->responseError('Taux de TVA manquant');
            return $this->fetchResponse();
        }


        if (0 !== $id) {
            $invoiceCancel = InvoiceCancel::find($id);
        } else {
            $invoiceCancel = new InvoiceCancel();
        }
        $invoiceCancel->invoice_id = $data['invoice_id'];
        $invoiceCancel->vat_id = $data['vat_id'];
        $invoiceCancel->name = $data['name'] ?? "";
        $invoiceCancel->date = $data['date'] ?? date('Y-m-d');
        $invoiceCancel->price_before_tax = $data['price_before_tax'] ?? 0;
        $invoiceCancel->price_after_tax = $data['price_after_tax'] ?? 0;


        $invoiceCancel->save();
        $this->responseSuccess('Avoir sauvegardé');
        return $this->fetchResponse();
    }

    public function delete(int $id)
    {
        $this->enableAjaxMode();
        $invoiceCancel = InvoiceCancel::find($id);

        if ($invoiceCancel) {
            $invoiceCancel->delete();
            $this->responseSuccess('Avoir supprimé');
        } else {
            $this->responseError('Avoir introuvable');
        }
        return $this->fetchResponse();

    }
}
