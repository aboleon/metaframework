<?php

namespace App\DataTables;

use App\DataTables\View\OrderPaymentView;
use App\Models\Event;
use App\Traits\DataTables\Common;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OrderPaymentDataTable extends DataTable
{
    use Common;


    public function __construct(
        private readonly Event $event,
    )
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
                return view('events.manager.payment.datatable.action')->with([
                    'data' => $data,
                    'event' => $this->event,
                ])->render();
            })
            ->addColumn('payment_purpose', '')
            ->rawColumns(['action']); // code-notes 7334
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(OrderPaymentView $model): QueryBuilder
    {
        return $model->newQuery()->where('event_id', $this->event->id);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->setHtml('eventmanager-payment-datatable');
    }

    /**
     * Get the dataTable columns definition
     */
    public function getColumns(): array
    {
        return [
            Column::make('invoice_number')->title("Num Fact"),
            Column::make('payer')->title("Payeur"),
            Column::make('date')->title("Date"),
            Column::make('payment_method_translated')->title("Paiement"),
            Column::make('authorization_number')->title("Autorisation"),
            Column::make('card_number')->title("Carte"),
            Column::make('bank')->title("Banque"),
            Column::make('issuer')->title("Emetteur"),
            Column::make('check_number')->title("Chèque"),
            Column::make('amount')->title("Montant"),
            Column::computed('action')->addClass('text-end')->title('Actions')
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'EventOrderPayment_' . date('YmdHis');
    }
}
