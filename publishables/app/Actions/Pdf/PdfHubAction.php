<?php

namespace App\Actions\Pdf;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use MetaFramework\Traits\Responses;
use Throwable;

class PdfHubAction
{
    use Responses;

    public function printPdf(string $view, array $params = []): Response
    {
        try {
            if (view()->exists($view)) {
                return $this->snappyDownload($view, $params);
            } else {
                $this->responseError("Identifiant pdf inconnu: " . $view);
            }
        } catch (Throwable $e) {
            $this->responseError("Une erreur est survenue: " . $e->getMessage());
        }
        return new Response(json_encode($this->fetchResponse()), 200, ['Content-Type' => 'application/json']);
    }



    //--------------------------------------------
    //
    //--------------------------------------------
    private function snappyDownload(string $view, array $params = []): Response
    {
        $pdfName = $params['pdfName'] ?? 'download-' . date("YmdHis") . ' .pdf';
//        $pdfWrapper = app('snappy.pdf.wrapper');
        return Pdf::loadView($view)
            ->setOption('page-size', "A4")
            ->setOption('margin-top', 4)
            ->setOption('margin-bottom', 4)
            ->setOption('margin-left', 4)
            ->setOption('margin-right', 4)
            ->download($pdfName);
    }
}
