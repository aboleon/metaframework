<?php

namespace App\MailTemplates;

use App\MailTemplates\Templates\Courrier;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\Pdf as Document;
use Illuminate\Http\Response;

class PdfPrinter
{
    private Document $document;

    public function __construct(
        public Courrier $parsed,
    )
    {
        $this->document = Pdf::loadView('mailtemplates.pdf', ['parsed' => $parsed])->setPaper($this->parsed->template()->format, $this->parsed->template()->orientation);
    }

    public function __invoke(): Response
    {
        return $this->stream();
    }

    public function stream(): Response
    {
        return $this->document->stream();
    }

    public function output(): string
    {
        return $this->document->output();
    }
}
