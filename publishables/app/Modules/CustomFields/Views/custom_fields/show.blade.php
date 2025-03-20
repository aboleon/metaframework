<div id="modules">
    @if ($data->modules->isNotEmpty())
        @foreach($data->modules->sortBy('position') as $module)
            <div class="module-bloc" data-key="{{ $module->key }}" data-required="{{ $module->required }}" data-type="{{ $module->type }}">
                <input type="hidden" name="custom_fields[key][]" class="key" value="{{ $module->key }}"/>
                <b class="d-block my-3">{{ $module->title }}</b>
                <div>
                    @if ($module->subtype != 'checkbox')
                        @php
                            $saved_value = $data->content->filter(fn($item) => $item->key == $module->key)->first()?->value;
                        @endphp
                    @endif
                    @switch($module->subtype)
                        @case('radio')
                            @foreach($module->data as $line)
                                <div class="row mb-3">
                                    <div class="col-sm-8 line">
                                        <x-mfw::input-radio :affected="$saved_value" :value="$line->key" name="custom_fields[{{ $module->key }}]" :label="$line->content"/>
                                    </div>
                                </div>
                            @endforeach
                            @break
                        @case('checkbox')
                        @php
                        $saved_values = $data->content->filter(fn($item) => $item->key == $module->key);
                        @endphp
                            @foreach($module->data as $line)
                                <div class="row mb-3">
                                    <div class="col-sm-8 line">

                                        <x-mfw::checkbox :affected="$saved_values->filter(fn($item) => $item->value == $line->key)->first()?->value" :value="$line->key" :name="'custom_fields.'. $module->key .'.'. $line->key" :label="$line->content"/>

                                    </div>
                                </div>
                            @endforeach
                            @break
                                        </div>
                        @case('select')
                            @php
                                $values = $module->data->pluck('content','key')->toArray();
                            @endphp
                            <x-mfw::select :values="$values" :name="'custom_fields.'.$module->key" :affected="$saved_value"/>
                            @break
                        @case('textarea')
                            <x-mfw::textarea name="custom_fields[{{ $module->key }}]" :value="$saved_value"/>
                            @break
                        @default
                            <x-mfw::input :name="'custom_fields.'.$module->key" :value="$saved_value"/>
                    @endswitch
                </div>
            </div>
        @endforeach
    @endif
</div>
@push('js')
    <script>
        $(function () {
            $(':radio').click(function () {
                $(this).closest('.module-bloc').find('.radio_id').val($(this).attr('data-linekey'));
            });
            $('form#customform button').off().click(function (e) {
                e.preventDefault();
                $('.module-errors').remove();
                $('.module-bloc').each(function (index) {
                    let errors = false;
                    if ($(this).attr('data-required') == 1) {
                        switch ($(this).attr('data-type')) {
                            case 'text':
                                if ($.trim($(this).find('textarea').val()).length < 5) {
                                    errors = true;
                                }
                                break;
                            case 'checkbox':
                            case 'radio':
                                if ($(this).find(':checked').length < 1) {
                                    errors = true;
                                }
                                break;
                        }
                        if (errors) {
                            $(this).append('<div class="module-errors alert alert-warning mt-3">Merci de fournir une réponse à la question</div>');
                        }
                    }
                });
                if ($('.module-errors').length < 1) {
                    $('form#customform').submit();
                }
            });
        });
    </script>
@endpush
