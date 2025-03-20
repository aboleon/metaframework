<?php

namespace App\MailTemplates\Templates;

use App\MailTemplates\Contracts\Template;
use App\MailTemplates\Traits\MailTemplate;
use App\Models\Order;
use Illuminate\Support\Facades\View;

class Invoice implements Template
{

    use MailTemplate;

    private Order $order;


    public function signature(): string
    {
        return 'invoice';
    }

    public function params(Order $order):static
    {
        $this->order = $order;
        return $this;
    }

    public function variables(): array
    {
        return [
            'names' => 'Noms',
            'summary' => 'Recapitulatif'
        ];
    }

    public function names(): string
    {
        return $this->order->client->names();
    }

    public function summary(): string
    {
        return View::make('invoices.shared.cart_mail', ['order' => $this->order])->render();
    }

    public function setFilePath($file): static
    {
        $this->attachment['file'] = $file;
        return $this;
    }

    public function setFileOptions(array $options): static
    {
        $this->attachment['options'] = $options;
        return $this;
    }



}
