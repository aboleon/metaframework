<?php

namespace App\DataTables;

use App\DataTables\View\SellableView;
use App\Traits\DataTables\Common;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SellableDataTable extends DataTable
{

    use Common;

    /**
     * Build the DataTable class.
     *
     * @param EloquentBuilder $query Results from query() method.
     */
    public function dataTable(EloquentBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', fn($data) => view('sellable.datatable.action')->with([
                'data' => $data,
            ])->render())
            ->rawColumns(['action']);

    }

    /**
     * Get the query source of dataTable.
     */
    public function query(): EloquentBuilder
    {
        $query = SellableView::query();

        if (request()->route()->getName() == 'panel.sellables.archived') {
            $query->whereNotNull('deleted_at');
        } else {
            $query->whereNull('deleted_at');
        }

        return $query;

    }

    /**
     * Optional method if you want to use the html builder.
     */
    public
    function html(): HtmlBuilder
    {
        return $this->setHtml('sellable');
    }

    /**
     * Get the dataTable columns definition
     */
    public
    function getColumns(): array
    {
        return [
            Column::make('category_fr')->title('Catégorie FR'),
            Column::make('category_en')->title('Catégorie EN'),
            Column::make('title_fr')->title('Intitulé FR'),
            Column::make('title_en')->title('Intitulé EN'),
            Column::make('price')->title('Prix'),
            Column::make('sold_per')->title('Unité'),
            Column::computed('action')->addClass('text-end')->title('Actions')->searchable(false)
        ];
    }

    /**
     * Get the filename for export.
     */
    protected
    function filename(): string
    {
        return 'Sellable_' . date('YmdHis');
    }
}
