<tr class="sortable" data-id="{{$item->id}}">
    <td>{!! str_repeat('-', $level).' '. ($bold ? '<b>' : '') . $item->title . ' ' . ($bold ? '</b>' : '') !!}</td>
    <td>{{ $item->type }}</td>
    <td>{{ $item->url }}</td>
    <td>
        <div class="dropdown ui-actions">
            <button class="btn btn-xs btn-danger dropdown-toggle" type="button"
                    id="dropdownMenuLink_submenu_actions"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">Actions
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink_actions">
                <li>
                    <a class="dropdown-item"
                       href="{!! route('mfw.nav.create', ['parent' => $item->id]) !!}">
                        <i class="text-dark fas fa-plus"></i>Sous-menu</a>
                </li>
                <li>
                    <a class="dropdown-item"
                       href="{!! route('mfw.nav.edit', $item->id) !!}">
                        <i class="text-dark fas fa-pen"></i>Éditer</a>
                </li>
                <x-mfw::modal-actions
                    reference="destroy_{{ $item->id }}"
                    icon='<i class="text-danger fas fa-trash"></i>' title="Supprimer"/>
            </ul>
        </div>

        <x-mfw::modal :route="route('mfw.nav.destroy', $item->id)"
                 :question="__('mfw::mfw.should_i_delete_record')"
                 reference="destroy_{{ $item->id }}"/>
    </td>
</tr>
