<ul class="mfw-actions">
    <x-mfw::edit-link :route="route('mfw.users.edit', $data->getKey())"/>

    @if($supportsSoftDeletes && method_exists($data, 'trashed') && $data->trashed())
        <x-mfw::restore-modal-link reference="{{ $data->getKey() }}"/>
    @else
        <x-mfw::delete-modal-link reference="{{ $data->getKey() }}" :title="__('mfw::mfw.archive_this')"/>
    @endif
</ul>

@if($supportsSoftDeletes && method_exists($data, 'trashed') && $data->trashed())
    <x-mfw::modal
        :route="route('mfw.users.restore', $data->getKey())"
        :question="__('mfw::mfw.restore') . ' - ' . (method_exists($data, 'names') ? $data->names() : ($data->email ?? $data->getKey())) . ' ?'"
        reference="restore_{{ $data->getKey() }}"
    />
@else
    <x-mfw::modal
        :route="route('mfw.users.destroy', $data->getKey())"
        :question="__('mfw::mfw.should_i_delete_user') . ' - ' . (method_exists($data, 'names') ? $data->names() : ($data->email ?? $data->getKey())) . ' ?'"
        reference="destroy_{{ $data->getKey() }}"
    />
@endif
