<div class="bloc-editable">
    @php
        if ($model->uses('meta')) {
            $meta_as_translatable = $model->getMetaAsTransltable();

        } else {
                $meta_as_translatable = array_keys($meta->fillables);
                array_shift($meta_as_translatable);
        }

            if($meta_as_translatable) {
                foreach($meta_as_translatable as $item) {
                    unset($meta->fillables[$item]);
                }
            }
    @endphp
    <fieldset>
        <legend class="toggle">Résumé / SEO</legend>
        <div class="toggable">
            <x-mfw::language-tabs id="tab_content"/>
            <div class="tab-content base">
                @foreach(config('mfw.translatable.locales') as $locale)
                    <div class="tab-pane fade {!! $locale == app()->getLocale() ? 'show active': null !!}" id="tab_meta_content_{{ $locale }}" role="tabpanel" aria-labelledby="tab_meta_link_content_{{ $locale }}">
                        <div class="row mb-4">
                            @foreach($meta->fillables as $key=>$value)
                                @switch($value['type'])
                                    @case('textarea')
                                        <div class="meta_{{ $key. ' '. ($value['class'] ?? 'col-12') }} mb-4{{ $model->visibility($key) }}">
                                            <x-mfw-input::textarea :name="$meta->translatableInput('meta['.$key.']')" class="{{ $value['class'] ?? '' }}" :value="$meta->translation($key, $locale)" :label="$value['label']"/>
                                        </div>
                                        @break
                                    @default
                                        <div class="meta_{{ $key . ' '. ($value['class'] ?? 'col-12') }} mb-4{{ $model->visibility($key) }}">
                                            <x-mfw-input::input :name="$meta->translatableInput('meta['.$key.']')" :value="$meta->translation($key, $locale)" :label="$value['label']"/>
                                        </div>
                                @endswitch
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </fieldset>
    @if ($model->uses('images') && $meta->id)
        <fieldset>
            <legend class="toggle">Image de présentation</legend>
            <div class="toggable">
                <div class="tab-content base">
                    <x-mediaclass::uploadable :model="$model" :settings="['group'=>'meta']" size="small"/>
                </div>
            </div>
        </fieldset>
    @endif

    @if ($meta->id)
        @includeIf('mfw.meta.'.$meta->type.'.custom_meta')
        @if ($meta->taxonomy)
            @includeIf('mfw.backend.'.$meta->type.'.custom_meta'.'._'.$meta->taxonomy)
        @endif
    @endif
        <fieldset>
            <legend class="toggle {{ (auth()->user()->hasRole('dev') or $meta->hasParams()) ? '' : 'd-none' }}">Paramètres</legend>
            <div class="toggable">
                @if ($model->model_configs)
                    @foreach($model->model_configs as $key => $values)
                        @switch($values['type'])
                            @case('checkbox')
                                <x-mfw-input::checkbox :value="$key" name="meta[configs][{{$key}}]" :label="$values['label']" :affected="$meta->configs[$key] ?? null"/>
                                @break
                        @endswitch
                    @endforeach
                @endif

                @if ($meta->uses('parent'))
                    <div>
                        <label class="form-label" for="meta_parent">Affecter à un parent</label>
                        {!! (new \MetaFramework\Inputable\Printers\Select(\MetaFramework\Models\Meta::where('type', $meta->type)->get(), 'meta[parent]', $meta->parent))() !!}
                    </div>
                @endif

                <div class="col-12 mb-4 {{ !auth()->user()->hasRole('dev') ? 'd-none' : '' }}">
                    {{-- @if ($meta->model()->instance->isVisible('taxonomy'))} --}}
                    <x-mfw-input::input name="meta[taxonomy]" value="{{ $meta->taxonomy }}" label="Taxonomie"/>

                    @role('dev')
                    <code>Visible en mode dev uniquement</code>
                    <x-mfw::notice class="mt-3" :message="'<span class=\'opacity-50\'>SubModel</span> : '. get_class($meta->subModel())" />
                    @endrole
                </div>

                @if ($meta->uses('template'))
                    <div>
                        <x-mfw-input::input label="Template" name="meta[template]" :value="$meta->template"/>
                    </div>
                @endif

                @if ($meta->uses('forms'))
                    <div class="mt-3">
                        <x-mfw-input::select label="Formulaire" name="meta[forms]" :values="\MetaFramework\Models\Forms::selectables()" :affected="$meta->form?->name"/>
                    </div>
                @endif

                {{--
                                    @if ($meta->type == 'blog')
                                        {!! \MetaFramework\Models\Meta\BlogCategories::form($meta) !!}
                                        {!! \MetaFramework\Models\Meta\BlogCategories::form($meta, 'tag') !!}
                                    @endif
                --}}
            </div>
        </fieldset>

</div>
