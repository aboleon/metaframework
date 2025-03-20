<?php

namespace App\Mail;

use App\Accessors\EventDepositAccessor;
use App\Interfaces\Mailer;
use App\Mailer\MailerAbstract;
use App\Models\CustomPaymentCall;
use Illuminate\Support\Facades\Mail;
use MetaFramework\Traits\Responses;

class EventMailDeposit extends MailerAbstract
{
    use Responses;
    public string $locale;

    public EventDepositAccessor $accessor;

    public function __construct(public CustomPaymentCall $paymentCall)
    {
        $this->accessor = new EventDepositAccessor($this->paymentCall->shoppable);
        $this->locale = $this->accessor->locale();
    }

    public function email(): string
    {
        return $this->accessor->accountEmail();
    }

    public function subject(): string
    {
        $subject = [
            'fr' => "Demande de paiement d'une caution de prise en charge",
            'en' => "Payment request for a security deposit for coverage",
        ];


        return $subject[$this->locale];
    }

    public function view(): string
    {
        return 'mails.mailer.event-deposit';
    }
}
