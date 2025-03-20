<?php

namespace App\DataTables;

use App\DataTables\View\UserAdministrateurView;
use App\Traits\DataTables\Common;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
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
                return view('users.datatable.action')->with([
                    'data' => $data,
                ])->render();
            })
            ->rawColumns(['action', 'checkbox']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(UserAdministrateurView $model): QueryBuilder
    {
        $query = $model->newQuery();
        if (request()->route()->getName() == 'panel.users.archived') {
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
        return $this->setHtml('users');
    }

    /**
     * Get the dataTable columns definition
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title("Nom"),
            Column::make('email')->title("Email"),
            Column::make('mobile')->title("Tél. mobile"),
            Column::computed('action')->addClass('text-end')->title('Actions'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'User_' . date('YmdHis');
    }
}
