<ul class="mfw-actions">
    <x-mfw::edit-link :route="route('panel.mailtemplates.edit', $data)"/>
    <li data-bs-toggle="modal" data-bs-target="#duplicate_{{ $data->id }}">
        <a href="#" class="btn btn-sm btn-warning" data-bs-placement="top" data-bs-title="Dupliquer" data-bs-toggle="tooltip"><i class="fa-solid fa-shuffle"></i></a>
    </li>
    @foreach(config('mfw.translatable.locales') as $locale)
        <li>
            <span class="border border-1 border-dark-subtle rounded-1 lg-link">
                <img src="{!! asset('vendor/flags/4x3/'.$locale.'.svg') !!}" width="26" height="18" alt="{{ trans('lang.'.$locale.'.label') }}" class="d-inline-block" data-bs-placement="top" data-bs-title="{{ trans('lang.'.$locale.'.label') }}" data-bs-toggle="tooltip"/>
                <a target="_blank" href="{{ route('panel.mailtemplates.show', ['mailtemplate' => $data, 'locale' => $locale, 'as'=>'mail']) }}" data-bs-placement="top" data-bs-title="Version Mail" data-bs-toggle="tooltip">Mail</a> |
                <a target="_blank" href="{{ route('panel.mailtemplates.show', ['mailtemplate' => $data, 'locale' => $locale, 'as'=>'pdf']) }}" data-bs-placement="top" data-bs-title="Version PDF" data-bs-toggle="tooltip">PDF</a>
            </span>
        </li>
    @endforeach
    <x-mfw::delete-modal-link reference="{{ $data->id }}"/>
</ul>
<x-mfw::modal :route="route('panel.mailtemplates.destroy', $data->id)"
              title="{{__('ui.delete')}}"
              question="Supprimer {{ $data->subject }} ?"
              reference="destroy_{{ $data->id }}"/>

<x-mfw::modal :route="route('panel.mailtemplates.duplicate', $data->id)"
              title="Duplication d'un courrier type"
              question="Dupliquer <b>{{ $data->subject }}</b> ?"
              reference="duplicate_{{ $data->id }}"/>
