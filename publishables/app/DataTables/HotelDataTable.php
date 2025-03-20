<?php

namespace App\DataTables;

use App\DataTables\View\HotelView;
use App\Models\Hotel;
use App\Traits\DataTables\Common;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class HotelDataTable extends DataTable
{
    use Common;

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('checkbox', function ($row) {
                return '<div class="form-check"><input type="checkbox" class="form-check-input row-checkbox" value="' . $row->id . '"></div>';
            })
            ->addColumn('action', function ($data) {
                return view('hotels.datatable.action')->with([
                    'data' => $data,
                ])->render();
            })
            ->rawColumns(['action', 'checkbox']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(HotelView $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->setHtml('hotel');
    }

    /**
     * Get the dataTable columns definition
     */
    public function getColumns(): array
    {
        return [
            Column::computed('checkbox')->title('<div class="form-check"><input type="checkbox" class="form-check-input" id="datatable-select-all"/></div>')->orderable(false)->searchable(false)->width('50'),
            Column::make('name')->title('Nom'),
            Column::make('locality')->title('Ville'),
            Column::make('country')->title('Pays'),
            Column::make('email')->title('Mail commercial'),
            Column::make('phone')->title('Tel commercial'),
            Column::make('description')->title('Notes'),
            Column::computed('action')->addClass('text-end')->title('Actions')
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Hotel_' . date('YmdHis');
    }
}
