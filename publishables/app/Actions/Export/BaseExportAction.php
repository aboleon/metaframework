<?php

namespace App\Actions\Export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BaseExportAction
{
    public function exportToXlsx(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->prepareSheet($sheet);

        $writer = new Xlsx($spreadsheet);


        $stream = fopen('php://temp', 'r+');
        $writer->save($stream);
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        return $content;
    }

    //--------------------------------------------
    //
    //--------------------------------------------
    protected function prepareSheet($sheet): void
    {
    }
}