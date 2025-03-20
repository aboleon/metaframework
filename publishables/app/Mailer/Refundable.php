<?php

namespace App\Mailer;

use App\Accessors\OrderAccessor;
use App\Mail\MailerMail;
use App\Models\Order;
use App\Models\Order\Refund;
use App\Traits\EventCommons;
use Illuminate\Support\Facades\Mail;

class Refundable extends MailerAbstract
{
    use EventCommons;

    public array $data = [];
    private Order $order;
    private ?Refund $refund = null;
    private ?OrderAccessor $orderAccessor = null;

    public function send()
    {
        return Mail::send(new MailerMail($this));
    }

    public function setData(): void
    {

        $this->refund = Refund::firstWhere('uuid', $this->identifier);

        if ($this->refund) {

            $this->order = $this->refund->order;
            $this->orderAccessor = (new OrderAccessor($this->order));

        } else {
            abort(404, "Aucun avoir trouvé pour uuid " . $this->identifier);
        }

        $this->data = [
            'refund' => $this->refund,
            'order' => $this->order,
            'banner' => $this->getBanner($this->order->event)
        ];
    }

    public function getData()
    {
        return $this->data;
    }

    public function addressee(): string
    {
        return $this->order->invoiceable->first_name . ' ' . $this->order->invoiceable->last_name;
    }

    public function email(): string
    {
        return $this->orderAccessor->invoiceableMail();
    }

    public function subject(): string
    {
        return 'Votre avoir Divine ID';
    }

    public function view(): string
    {
        return 'mails.mailer.refund';
    }

    public function attachments(): array
    {
        return [
            [
                'type' => 'binary',
                'file' => (new \App\Printers\PDF\Refundable($this->identifier))->binary(),
                'as' => 'refund.pdf',
                'mime' => 'application/pdf',
            ]
        ];
    }

}
