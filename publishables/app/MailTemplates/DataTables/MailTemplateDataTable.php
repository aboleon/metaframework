<?php

namespace App\MailTemplates\DataTables;

use App\DataTables\View\SellableView;
use App\MailTemplates\Models\MailTemplate;
use App\Traits\DataTables\Common;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MailTemplateDataTable extends DataTable
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
            ->addColumn('subject', fn($data) => $data->subject)
            ->addColumn('created_at', fn($data) => $data->created_at->format('d/m/Y'))
            ->addColumn('action', fn($data) => view('mailtemplates.datatable.action')->with([
                'data' => $data,
            ])->render())
            ->filterColumn('subject', function ($query, $keyword) {
                $query->whereRaw("LOWER(JSON_EXTRACT(subject, '$.fr')) LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(created_at, '%d/%m/%Y') LIKE ?", ["%{$keyword}%"]);
            })
            ->rawColumns(['action']);

    }

    /**
     * Get the query source of dataTable.
     */
    public function query(): EloquentBuilder
    {
        $query = MailTemplate::query();

        if (request()->route()->getName() == 'panel.mailtemplates.archived') {
            $query->whereNotNull('deleted_at');
        } else {
            $query->whereNull('deleted_at');
        }

        return $query;

    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->setHtml('mailtemplates');
    }

    /**
     * Get the dataTable columns definition
     */
    public function getColumns(): array
    {
        return [
            Column::make('subject')->title('Sujet'),
            Column::make('identifier')->title('ID'),
            Column::make('format')->title('Format'),
            Column::make('orientation')->title('Orientation'),
            Column::make('created_at')->title('Créé'),
            Column::computed('action')->addClass('text-end')->title('Actions')->searchable(false)
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Mailtemplate' . date('YmdHis');
    }
}
