<?php

namespace App\DataTables;

use App\DataTables\View\AccountView;
use App\Enum\ClientType;
use App\Enum\SavedSearches;
use App\Helpers\AdvancedSearch\ContactAdvancedSearchHelper;
use App\Traits\DataTables\Common;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class AccountDataTable extends BaseDataTable
{

    use Common;

    /**
     * Build the DataTable class.
     *
     * @param EloquentBuilder $query Results from query() method.
     */
    public function dataTable(EloquentBuilder $query): EloquentDataTable
    {
        $datatable = (new EloquentDataTable($query));
        $role = request('role');

        if ($role == 'all') {
            $datatable->addColumn('account_type', function ($data) {
                return ClientType::translated($data->account_type);
            });
        }

        return $datatable
            ->addColumn('checkbox', function ($row) {
                return '<div class="form-check"><input type="checkbox" class="form-check-input row-checkbox" value="' . $row->id . '"></div>';
            })
            ->addColumn('action', fn($data) => view('accounts.datatable.action')->with([
                'data' => $data,
                'role' => $role,
            ])->render())
            ->rawColumns(['action', 'checkbox']);

    }

    /**
     * Get the query source of dataTable.
     */
    public function query(): EloquentBuilder
    {
        $searchData = session('savedSearch.' . SavedSearches::CONTACTS->value . '.filters');
        $user_ids = null;
        if($searchData){
            $user_ids = ContactAdvancedSearchHelper::getUserIdsBySearchFilters($searchData);
        }

        $query = AccountView::query()->when(request('role') != 'all', function ($q) {
            return $q->where('account_type', request('role'));
        });

        if(null !== $user_ids){
            $query->whereIn('id', $user_ids);
        }

        if (request()->route()->getName() == 'panel.accounts.archived') {
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
        return $this->setHtml('account', [
            'language' => [
                'search' => 'Recherche sur toutes les valeurs du tableau SAUF la colonne Type de compte',
            ]
        ]);
    }

    /**
     * Get the dataTable columns definition
     */
    public function getColumns(): array
    {
        $columns = [
            Column::computed('checkbox')->title('<div class="form-check"><input type="checkbox" class="form-check-input" id="datatable-select-all"/></div>')->orderable(false)->searchable(false)->width('50')
        ];

        if (request('role') == 'all') {
            $columns = array_merge($columns, [
                Column::make('account_type')->title('Type de compte'),
            ]);
        }

        return array_merge($columns, [
            Column::make('domain')->title('Domaine')->searchable(),
            Column::make('last_name')->title('Nom'),
            Column::make('first_name')->title('Prénom'),
            Column::make('company')->title('Société')->searchable(),
            Column::make('email')->title('E-mail'),
            Column::make('locality')->title('Ville'),
            Column::make('country')->title('Pays'),
            Column::make('phone')->title('Numéro de téléphone'),
            Column::make('notes')->title('Notes'),
            Column::make('blacklisted')->title('Blacklist'),
            Column::computed('action')->addClass('text-end')->title('Actions')->searchable(false)
        ]);
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Account_' . date('YmdHis');
    }
}
