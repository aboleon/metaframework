<?php

namespace App\Interfaces;

interface Mailer
{

    public function setModel(object $model): self;

    public function setIdentifier(string $identifier): self;

    public function send();

    public function email(): string|array;

    public function subject(): string;

    public function view(): string;

}
