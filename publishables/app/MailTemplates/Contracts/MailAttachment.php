<?php

namespace App\MailTemplates\Contracts;

interface MailAttachment
{
    public function setFilePath(string $file): static;
    public function setFileOptions(array $options): static;
}
