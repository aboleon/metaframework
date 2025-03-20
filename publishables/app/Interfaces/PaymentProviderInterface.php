<?php

namespace App\Interfaces;

use App\Accessors\Front\FrontCartAccessor;
use App\Models\CustomPaymentCall;
use App\Models\PaymentCall;

interface PaymentProviderInterface {

    public function signature(): array;
    public function providerId(): int;
    public function connect(): static;
    public function setOrderable(CustomPaymentCall|PaymentCall $orderable): static;
    public function config(array $config = []): static;
    public function request(): static;
    public function response(): static;
    public function processed(): array;
}
