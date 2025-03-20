<?php

namespace App\Printers\PDF;

use Illuminate\Http\Response;

class Receipt extends Invoice
{
    public function __construct(public string $identifier)
    {
        $this->setAsReceipt();

        parent::__construct($identifier);
    }


    public function __invoke(): Response
    {
        if (request()->has('download')) {
            return $this->download("Receipt DivineID N-".$this->invoice->invoice_number.".pdf");
        }

        return $this->stream();
    }
}
