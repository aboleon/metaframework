<?php

namespace App\Printers\PDF;

use App\Accessors\Accounts;
use App\Accessors\OrderAccessor;
use App\Models\Order;
use App\Traits\EventCommons;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use MetaFramework\Traits\DomPdf;

class Invoice
{
    use DomPdf;
    use EventCommons;

    private bool $isReceipt = false;
    private array $data = [];
    private ?Order $order = null;
    private ?Collection $services = null;
    private ?OrderAccessor $orderAccessor = null;
    private array $hotels = [];
    private int $paid = 0;

    protected ?\App\Models\Invoice $invoice = null;
    private bool $proforma = false;

    public function __construct(public string $identifier)
    {
        $this->setData();
        $this->pdf = Pdf::loadView('pdf.invoice', $this->data);
    }


    public function setAsReceipt(): self
    {
        $this->isReceipt = true;

        return $this;
    }

    public function setData(): void
    {
        $this->proforma = request()->filled('proforma');

        $this->order = Order::where('uuid', $this->identifier)->first();

        if ($this->order) {
            $this->services      = $this->order->event->sellableService->load('event.services');
            $this->hotels        = $this->order->event->accommodation->load('hotel')->mapWithKeys(fn($item) => [$item->id => $item->hotel->name.' '.($item->hotel->stars ? $item->hotel->stars.'*' : '').$item->title])->toArray();
            $this->orderAccessor = (new OrderAccessor($this->order));
            $this->paid          = $this->order->payments->sum('amount');

            if ($this->proforma) {
                $this->checkProforma();
            } else {
                $this->invoice = $this->order->invoice();
            }
        } else {
            abort(404, "Order not found with uuid ".$this->identifier);
        }


        $documentTtitle = $this->isReceipt ? __('front/order.receipt')
            : (($this->proforma or request()->has('proforma')) ? 'Proforma '.$this->order->id.($this->invoice?->id ? '-'.$this->invoice?->id : '') : __('front/order.invoice'));

        $accountAccessor = (new Accounts($this->order->account));

        $this->data = [
            'invoice'               => $this->invoice,
            'proforma'              => $this->proforma,
            'order'                 => $this->order,
            'address'               => implode('<br>', $this->orderAccessor->invoiceableAddress()),
            'services'              => $this->services,
            'orderAccessor'         => $this->orderAccessor,
            'hotels'                => $this->hotels,
            'totalsFromCarts'       => $this->orderAccessor->computeOrderTotalsFromCarts(),
            'totalsFromOrder'       => $this->orderAccessor->getOrderTotals(),
            'amendedOrder'          => $this->order->amendedOrder,
            'paid'                  => $this->paid,
            'vatSubtotalsFromCarts' => $this->orderAccessor->vatSubtotalsByVat(),
            'banner'                => $this->getBanner($this->order->event),
            'documentTitle'         => $documentTtitle,
            'isReceipt'             => $this->isReceipt,
            'lg'                    => $accountAccessor->getLocale(),
        ];

        $this->data['invoiceTotals'] = ($this->data['amendedOrder'] or $this->orderAccessor->isFrontGroupOrder()) ? $this->data['totalsFromOrder'] : $this->data['totalsFromCarts'];

        // Amended orders concern only Bookings (Accommodation)
        $this->data['invoiceVatTotals'] = $this->data['amendedOrder']
            ? [$this->order->accommodation->first()?->vat_id => $this->order->total_vat]
            : $this->data['vatSubtotalsFromCarts'];
        //de($this->data);
    }

    private function checkProforma(): void
    {
        if (request()->filled('proforma')) {
            $this->invoice = \App\Models\Invoice::query()->where(['order_id' => $this->order->id, 'id' => (int)request('proforma')])->first();
        }
    }

    public function __invoke(): Response
    {
        if (request()->has('download')) {
            return $this->download("Facture ".($this->proforma ? 'proforma ' : '')."DivineID N-".$this->invoice->invoice_number.".pdf");
        }

        return $this->stream();
    }
}
