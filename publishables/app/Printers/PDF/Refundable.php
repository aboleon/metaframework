<?php

namespace App\Printers\PDF;

use App\Models\Order;
use App\Models\Order\Refund;
use App\Traits\EventCommons;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use MetaFramework\Traits\DomPdf;

class Refundable
{
    use DomPdf;
    use EventCommons;
    private array $data = [];
    private Order $order;
    private ?Refund $refund = null;
    private ?\App\Accessors\OrderAccessor $orderAccessor = null;

    public function __construct(public string $identifier)
    {
        $this->setData();
        $this->pdf = Pdf::loadView('pdf.refundable', $this->data);
    }

    public function setData(): void
    {
        $this->refund = Refund::firstWhere('uuid', $this->identifier);

        if ($this->refund) {

            $this->order = $this->refund->order;
            $this->orderAccessor = (new \App\Accessors\OrderAccessor($this->order));

        } else {
            abort(404, "Avoir not trouvé pour la requête " . $this->identifier);
        }


        $this->data = [
            'refund' => $this->refund->load('items'),
            'order' => $this->order,
            'address' => implode('<br>', $this->orderAccessor->invoiceableAddress()),
            'orderAccessor' => $this->orderAccessor,
            'banner' => $this->getBanner($this->order->event)
        ];
    }

    public function __invoke(): Response
    {
        if (request()->has('download')) {
            return $this->download("Avoir DivineID N-" . $this->refund->refund_number . ".pdf");
        }
        return $this->stream();
    }
}
