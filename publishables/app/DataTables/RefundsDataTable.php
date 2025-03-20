<?php

namespace App\DataTables;

use App\DataTables\View\RefundsView;
use App\Models\Event;
use App\Traits\DataTables\Common;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class RefundsDataTable extends DataTable
{
    use Common;

    public function __construct(public Event $event)
    {
        parent::__construct();
    }

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($data) {
                return view('refunds.datatable.action')->with([
                    'data' => $data,
                ])->render();
            })
            ->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(RefundsView $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('event_id', $this->event->id);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->setHtml('refund');
    }

    /**
     * Get the dataTable columns definition
     */
    public function getColumns(): array
    {
        return [
            Column::make('refund_number')->title('Num Avoir'),
            Column::make('order_id')->title('Num Commande'),
            Column::make('created_at')->title('Date'),
            Column::make('client_name')->title('Nom'),
            Column::make('total')->title('Montant TTC'),
            Column::computed('action')->addClass('text-end')->title('Actions')
        ];
    }


    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Invoices_' . date('YmdHis');
    }
}
