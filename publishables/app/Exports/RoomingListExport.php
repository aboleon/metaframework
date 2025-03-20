<?php

namespace App\Exports;

use App\Accessors\Order\RoomingListAccessor;
use App\Models\EventManager\Accommodation;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

;

class RoomingListExport implements FromView, WithStyles
{

    private RoomingListAccessor $roomingList;

    public function __construct(public Accommodation $accommodation)
    {
        $this->roomingList = (new RoomingListAccessor($this->accommodation));
    }

    public function view(): View
    {
        return view('reports.shared.rooming-list', ['data' => $this->roomingList->getData(), 'roomingList' => $this->roomingList]);
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            1 => ['font' => ['bold' => true]], // Bold header
        ];

        // Apply alternating row styles
        $rowCount = $this->roomingList->getCounter();
        for ($i = 2; $i <= $rowCount + 1; $i++) {
            if ($i % 2 == 0) {
                $styles[$i] = ['fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFEFEFEF']
                ]];
            }
        }

        return $styles;
    }
}
