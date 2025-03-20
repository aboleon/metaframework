<tr data-id="{{ $item->id }}">
    @php
        $tag = $level > 0 ? 'td':'th';
    @endphp
    {!! '<'.$tag .'>'. str_repeat('-', $level*2) . ' ' . $item->name .'</'. $tag .'>' !!}
    @if (!$dictionnary->id)
        <td>
            <a class="btn btn-xs btn-default" href="{!! route('panel.dictionnary.entries.index', $item->dictionnary) !!}">{{ $item->dictionnary->name }}</a>
        </td>
    @endif
    <td>
        <span class="btn btn-xs px-2 btn-{{ $item->entries->isNotEmpty() ? 'info' : 'secondary opacity-50' }} cursor-default">{{ $item->entries->count() }}</span>
    </td>
    <td>{{ $item->position }}</td>
    <td>
        <ul class="mfw-actions">
            <x-mfw::edit-link :route="route('panel.dictionnaryentry.edit', $item)"/>

            @if (!$item->parent)
                <li>
                    <a class="btn btn-sm btn-success" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ __('mfw.add') }}"
                       href="{{ route('panel.dictionnaryentry.subentry', $item) }}">
                        <i class="fas fa-plus"></i></a>
                </li>
            @endif
            <x-mfw::delete-modal-link reference="{{ $item->id }}"/>
        </ul>
        <x-mfw::modal :route="route('panel.dictionnaryentry.destroy', $item)"
                                :question="'Supprimer ' . $item->name. '?'"
                                reference="destroy_{{ $item->id }}"/>
    </td>
</tr>
