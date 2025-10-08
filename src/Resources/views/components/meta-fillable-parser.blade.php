<div class="{{ $value['class'] ?? 'col-12' }} mb-4{{ $model->visibility($key) }}">
    @switch($value['type'] ?? '')
        @case('textarea')
            <x-mfw-input::textarea :name="$model->translatableInput($inputkey)" class="{{ $value['class'] ?? '' }}" :value="$content" :label="$value['label'] ?? ''"/>
            @break
        @case('component')
            @if (auth()->user()->hasRole('dev'))
                <code class="d-block pb-2">
                    {!! 'mfw.backend.'.$model->type.'.component_'.$subkey !!}
                    {{ !view()->exists('mfw.backend.'.$model->type.'.component_'.$subkey) ? ' - A créer' : '' }}
                </code>
            @endif
            @includeIf('panel.'.$model->type.'.component_'.$subkey)
            @break
        @case('number')
            <x-mfw-input::input type="number" :name="$model->translatableInput($inputkey)" :value="$content" :label="$value['label'] ?? ''" :params="$value['params'] ?? []"/>
            @break
        @case('media')
            <x-mediaclass::uploadable :model="$model" :settings="array_merge($value, ['subgroup' => $uuid])"/>
            @break;
        @case('repeatable')
        @case('clonable')
            @for($i=0;$i<$value['count'];++$i)
                <div class="row">
                    <div class="col-md-1 col-sm-2 repeatable pe-0">
                        <div>
                            <span class="i-counter">{{$i+1}}</span>
                        </div>
                    </div>
                    <div class="col-md-11 col-sm-10">
                        <div class="row">
                            @foreach($value['schema'] as $rep_key => $rep_val)
                                <x-mfw::meta-fillable-parser
                                        :model="$model"
                                        :key="$key"
                                        :subkey="$rep_key"
                                        :value="$rep_val"
                                        :content="is_string($content) ? $content : $content[$rep_key][$i]"
                                        :inputkey="$inputkey.'['.$rep_key.']['.$i.']'"
                                        :uuid="$uuid"/>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endfor
            @break
        @default
            <x-mfw-input::input :name="$model->translatableInput($inputkey)" :value="$content" :label="$value['label'] ?? ''" :params="$value['params'] ?? []"/>
    @endswitch
</div>
