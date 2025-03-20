<?php

namespace App\DataTables;

use App\DataTables\View\DictionaryView;
use App\Enum\DictionnaryType;
use App\Traits\DataTables\Common;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DictionnaryDataTable extends DataTable
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
            ->addColumn('entries', function ($data) {
                return '<span class="btn btn-xs px-2 btn-' . ($data->entries_count ? 'info' : 'secondary opacity-50') . ' cursor-default">' . $data->entries_count . '</span>
                        <a class="btn btn-xs btn-default action-entries-index" title="Éditer" href="' . route('panel.dictionnary.entries.index', $data->id) . '">
                    Gérer
                        </a>';
            })
            ->addColumn('type', function ($data) {
                return DictionnaryType::translated($data->type);
            })
            ->addColumn('action', function ($data) {
                return view('dictionnary.datatable.action')->with([
                    'item' => $data,
                ])->render();
            })
            ->rawColumns(['entries', 'action', 'checkbox']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(DictionaryView $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->setHtml('dictionnary', [
            "orderBy" => 1,
            "orderByDirection" => "asc",
        ]);
    }

    /**
     * Get the dataTable columns definition
     */
    public function getColumns(): array
    {
        $columns = [];

        if (auth()->user()->hasRole('dev')) {
            $columns[] = Column::computed('checkbox')->title('<div class="form-check"><input type="checkbox" class="form-check-input" id="datatable-select-all"/></div>')->orderable(false)->searchable(false)->width('50');
        }

        $columns[] = Column::make('name')->title('Intitulé');
        $columns[] = Column::computed('entries')->title('Entrées');

        if (auth()->user()->hasRole('dev')) {
            $columns[] = Column::computed('type')->title('Type')->addClass('type');
            $columns[] = Column::make('slug')->addClass('slug');
        }
        $columns[] = Column::computed('action')->addClass('text-end')->title('Actions');

        return $columns;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Dictionnary_' . date('YmdHis');
    }
}
