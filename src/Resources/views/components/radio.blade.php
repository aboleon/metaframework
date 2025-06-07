<div class="my-3 p-0">
    @if ($label)
        <label class="form-label d-block">{{ $label }}</label>
    @endif
    @forelse($values as $value => $title)
        <x-mfw::input-radio :affected="$affected"
                            :default="$default"
                            :value="$value"
                            :name="$name"
                            :label="str_starts_with($title, 'trans.') ? trans(str_replace('trans.','', $title)) : $title"
                            :params="$params"
        />
    @empty
        {{ __('mfw.no_data_provided') }}
    @endforelse
</div>
<x-mfw::validation-error :field="$validation_id"/>
