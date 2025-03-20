<?php

namespace App\DataTables;

use MetaFramework\Accessors\Prices;
use Yajra\DataTables\Services\DataTable;

class BaseDataTable extends DataTable
{
    protected function addBsTooltip(array &$params): void
    {
        $params['drawCallback'] = <<<EEE
                function(settings) {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
EEE;
    }

    protected function addResponsive(array &$params): void
    {
        $params['responsive'] = true;
    }


    protected function price(int $priceInCents): string
    {
       return Prices::readableFormat(Prices::fromInteger($priceInCents));
    }

    protected function datetime(string $date)
    {
        return date('d/m/Y H\hi', strtotime($date));
    }
}
