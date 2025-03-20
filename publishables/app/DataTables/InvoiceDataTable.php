<?php

namespace App\DataTables;

use App\DataTables\View\InvoiceView;
use App\Models\Event;
use App\Traits\DataTables\Common;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class InvoiceDataTable extends DataTable
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
                return view('invoices.datatable.action')->with([
                    'data' => $data,
                ])->render();
            })
            ->rawColumns(['action', 'paid_status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(InvoiceView $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('event_id', $this->event->id)//            ->with(['group', 'account', 'payments'])
            ;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->setHtml('order');
    }

    /**
     * Get the dataTable columns definition
     */
    public function getColumns(): array
    {
        return [
            Column::make('invoice_number')->title('Num Fact'),
            Column::make('invoice_type')->title('Type'),
            Column::make('created_at')->title('Date'),
            Column::make('client_name')->title('Nom'),
            Column::make('total')->title('Prix TTC'),
            Column::make('total_paid')->title('Prix Payé'),
            Column::make('paid_status')->title('Statut'),
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
