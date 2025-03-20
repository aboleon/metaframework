<?php

namespace App\Mail;

use App\Accessors\OrderAccessor;
use App\Mailer\MailerAbstract;
use App\Models\CustomPaymentCall;
use Illuminate\Support\Facades\Mail;
use MetaFramework\Traits\Responses;

class OrderPaymentResponse extends MailerAbstract
{

    use Responses;

    public string $locale;

    public OrderAccessor $accessor;

    public function __construct(public CustomPaymentCall $paymentCall)
    {
        $this->accessor = new OrderAccessor($this->paymentCall->shoppable);
    }

    public function paymentState(): string
    {
        return $this->paymentCall->state;
    }

    public function amount(): string
    {
        return $this->paymentCall->total;
    }

    public function send(): array
    {
        Mail::send(new MailerMail($this));
    }

    public function email(): string
    {
        return $this->accessor->eventAdminEmail();
    }

    public function subject(): string
    {
        return $this->accessor->accountNames()." a payé sa commande";
    }

    public function view(): string
    {
        return 'mails.mailer.order-payment-response';
    }
}
