@php
    $identifier = $data instanceof \App\Models\DictionnaryEntry ? 'dictionnaryentry' : 'dictionnary';
    $route_create = $identifier == 'dictionnaryentry' ? route('panel.dictionnary.entries.create', ['dictionnary'=> $dictionnary->id]) : route('panel.dictionnary.create');
    $route_destroy = null;
    if ($data->id) {
        $route_destroy = $identifier == 'dictionnaryentry' ? route('panel.dictionnaryentry.destroy', $data) : route('panel.dictionnary.destroy', $data);
    }
@endphp
<x-backend-layout>

    <x-slot name="header">
        <h2>
            {{ trans_choice('ui.dictionnary.label',2) }}
        </h2>

        <x-back.topbar.edit-combo
            :index-route="$route_index"
            :create-route="$route_create"
            :delete-route="$route_destroy"
            :item-name="fn($m) => $m->name"
            :model="$data"
        />
    </x-slot>

    <x-mfw::validation-errors/>

    <div class="shadow p-4 bg-body-tertiary rounded">

        <h2 class="legend">{!! $label ?? '' !!}</h2>
        <form method="post" action="{{ $route }}" id="wagaia-form">
            @csrf
            @if($data->id)
                @method('put')
            @endif

            @if(isset($subentry))
                <input type="hidden" name="subentry" value="{{ $subentry->id }}"/>
            @endif

            @php
                $custom_translatables = $data instanceof \App\Models\DictionnaryEntry && $subclass && $subclass->translatables();
                $custom_fillables = $data instanceof \App\Models\DictionnaryEntry && $subclass && $subclass->customData();
            @endphp

            <ul id="tab_translatable_tabs" class="nav nav-tabs admintabs" role="tablist">
                @foreach(config('mfw.translatable.locales') as $locale)
                    <li class="nav-item " role="presentation">
                        <button class="nav-link {!! $locale == app()->getLocale() ? 'active': null !!}"
                                id="tab_translatable_btn_{{ $locale }}"
                                data-bs-toggle="tab"
                                data-bs-target="#tab_translatable_{{ $locale }}"
                                type="button" role="tab"
                                aria-controls="tab_translatable_{{ $locale }}"
                                aria-selected="true">
                            <img src="{!! asset('vendor/flags/4x3/'.$locale.'.svg') !!}"
                                 alt="{{ trans('mfw-lang.'.$locale.'.label') }}" class="d-inline-block"/>
                            {!! trans('mfw-lang.'.$locale.'.label') !!}
                        </button>
                    </li>
                @endforeach
                    @if (isset($dictionnary) && $dictionnary->slug == 'service_family')
                    <x-mfw::tab tag="sage-tabpane" label="SAGE"/>
                @endif
            </ul>
            <div class="row my-4">
                <div class="col-xxl-8">
                    <div class="tab-content base">
                        @foreach(config('translatable.locales') as $locale)
                            <div class="tab-pane fade {!! $locale == app()->getLocale() ? 'show active': null !!}"
                                 id="tab_translatable_{{ $locale }}"
                                 role="tabpanel"
                                 aria-labelledby="tab_link_content_{{ $locale }}">
                                <fieldset>
                                    <div class="row mb-4">
                                        @foreach($data->fillables as $key=>$value)
                                            @switch($value['type'])
                                                @case('textarea')
                                                @case('textarea_extended')
                                                    <div class="col-12 mb-4">
                                                        <x-mfw::textarea name="{{$key}}[{{$locale}}]"
                                                                         :className="$value['type'] .' '.($value['class']??'') "
                                                                         value="{!! $data->translation($key, $locale) !!}"
                                                                         label="{{$value['label'] . ($value['required']? ' *':'')}}"/>
                                                    </div>
                                                    @break
                                                @default
                                                    <div class="{{ $value['class'] ?? 'col-12' }} mb-4">
                                                        <x-mfw::input name="{{$key}}[{{$locale}}]"
                                                                      value="{!! $data->translation($key, $locale) !!}"
                                                                      label="{{$value['label'] . ($value['required']? ' *':'')}}"/>
                                                    </div>

                                            @endswitch
                                        @endforeach

                                        @if ($custom_translatables)
                                            <x-mfw::custom-translatables :values="$subclass->translatables()"
                                                                         :model="$data"
                                                                         :locale="$locale"/>
                                        @endif

                                    </div>
                                </fieldset>
                            </div>
                        @endforeach

                        @if (isset($dictionnary) && $dictionnary->slug == 'service_family')
                            {!! \App\Helpers\Sage::renderTab($data) !!}
                        @endif
                    </div>

                    @if ($custom_fillables)
                        <x-mfw::custom-fillables :values="$subclass->customData()" :model="$data"/>
                    @endif

                    @if ($data instanceof \App\Models\Dictionnary)
                        <fieldset class="{{ !auth()->user()->hasRole('dev') ? 'd-none' : ''}}">
                            <legend>Paramètres</legend>
                            <div class="row">
                                <div class="col-6 col">
                                    <x-mfw::select name="type"
                                                   label="Type"
                                                   :values="\App\Enum\DictionnaryType::translations()"
                                                   :affected="$data->type ?: \App\Enum\DictionnaryType::default()"
                                                   :nullable="false"/>
                                </div>
                                <div class="col-6">
                                    <x-mfw::input name="slug" :value="$data->slug" label="Slug"/>
                                </div>
                            </div>
                        </fieldset>
                    @endif

                    @if ($data instanceof \App\Models\DictionnaryEntry && $subclass)
                        @if ($subclass->mediaSettings())
                            @foreach($subclass->mediaSettings() as $media)
                                <x-mediaclass::uploadable :model="$data"
                                                          :settings="$media"
                                                          description=""/>
                            @endforeach
                        @endif
                    @endif

                </div>
                <div class="col-xxl-4 pt-5">
                </div>
            </div>
        </form>
    </div>

    @include('lib.tinymce')

    @push('js')
        <script>
            activateEventManagerLeftMenuItem('dictionaries');
        </script>
    @endpush
</x-backend-layout>
