<?php

namespace App\Mailer;

use App\Accessors\OrderAccessor;
use App\Mail\MailerMail;
use App\Models\Order;
use App\Traits\EventCommons;
use Illuminate\Support\Facades\Mail;

class Invoice extends MailerAbstract
{
    use EventCommons;

    public array $data = [];
    private ?Order $order = null;
    private ?\App\Models\Invoice $invoice = null;
    private ?OrderAccessor $orderAccessor = null;

    public function send()
    {
        return Mail::send(new MailerMail($this));
    }

    public function setData(): void
    {
        $this->order = $this->model ?: Order::where('uuid', $this->identifier)->first();

        if ($this->order) {
            $this->invoice       = $this->order->invoice();
            $this->orderAccessor = (new OrderAccessor($this->order));
        } else {
            abort(404, "Order not found with uuid ".$this->identifier);
        }

        if ( ! $this->invoice) {
            abort(404, "Aucune facture n'est éditée pour la commande N° ".$this->order->id);
        }

        $this->data = [
            'invoice' => $this->invoice,
            'order'   => $this->order,
            'banner'  => $this->getBanner($this->order->event),
        ];
    }

    public function addressee(): string
    {
        return $this->order->invoiceable->first_name.' '.$this->order->invoiceable->last_name;
    }

    public function email(): string|array
    {
        return $this->orderAccessor->invoiceableMail();
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function subject(): string
    {
        return 'Votre facture Divine ID';
    }

    public function view(): string
    {
        return 'mails.mailer.invoice';
    }

    public function attachments(): array
    {
        return [
            [
                'type' => 'binary',
                'file' => (new \App\Printers\PDF\Invoice($this->identifier))->binary(),
                'as'   => 'invoice.pdf',
                'mime' => 'application/pdf',
            ],
        ];
    }

}
