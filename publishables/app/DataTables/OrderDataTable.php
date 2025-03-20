<?php

namespace App\DataTables;

use App\DataTables\View\OrderView;
use App\Enum\CancellationStatus;
use App\Enum\OrderClientType;
use App\Enum\OrderMarker;
use App\Models\Event;
use App\Traits\DataTables\Common;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OrderDataTable extends DataTable
{
    use Common;

    protected array $pool;

    public function __construct(public Event $event)
    {
        parent::__construct();
        $this->pool = [OrderClientType::CONTACT->value, OrderClientType::GROUP->value];
    }

    public function setPool(array $pool): void
    {
        $this->pool = $pool;
    }

    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('checkbox', function ($row) {
                return '<div class="form-check"><input type="checkbox" class="form-check-input row-checkbox" value="'.$row->id.'"></div>';
            })
            ->addColumn('order_cancellation', function ($data) {
                $html = '';
                if (($data->order_cancellation or $data->cancellation_status)) {
                    $html .= view('components.back.order-cancellation-pill', [
                        'class'   => 'd-inline-block',
                        'bgcolor' => ($data->order_cancellation or $data->cancellation_status == CancellationStatus::FULL->value) ? '#b42757' : 'orange',
                        'text'    => $data->cancellation_status_display,
                    ])->render();
                }

                $html .= '<small class="text-secondary fw-bold d-block text-nowrap">'.$data->amended_by_order.'</small>';

                return $html;
            })
            ->addColumn('action', function ($data) {
                return view('orders.datatable.action')->with([
                    'data' => $data,
                ])->render();
            })
            ->rawColumns(['action', 'checkbox', 'order_cancellation', 'payer']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(OrderView $model): QueryBuilder
    {
        return $model
            ->newQuery()
            ->where('event_id', $this->event->id)
            ->whereIn('client_type', $this->pool)
            ->whereNot('marker', OrderMarker::GHOST->value)
            ->with('invoices')//            ->with(['group', 'account', 'payments'])
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
            Column::computed('checkbox')->title('<div class="form-check"><input type="checkbox" class="form-check-input" id="datatable-select-all"/></div>')->orderable(false)->searchable(false)->width('50'),
            Column::make('id')->title('ID'),
            Column::make('invoice_number')->title('Num Fact'),
            Column::make('date_display')->title('Date'),
            Column::make('client_type_display')->title('Payeur')->addClass('fw-bold small'),
            Column::make('name')->title('Affectation'),
            Column::make('total')->title('Prix TTC'),
            Column::make('payments_total')->title('Prix Payé'),
            Column::make('total_pec')->title('Total PEC'),
            Column::make('status_display')->title('État'),
            Column::make('order_cancellation')->title('Annulation')->className('text-center'),
            Column::make('has_invoice_display')->title('Facturée'),
            Column::make('paybox_num_trans')->title('n°Paybox'),
            Column::make('origin')->title('Origine'),
            Column::make('contains')->title('Contient'),
            Column::make('has_pec')->title('PEC'),
            Column::computed('action')->addClass('text-end')->title('Actions'),
        ];
    }


    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Establishment_'.date('YmdHis');
    }
}
