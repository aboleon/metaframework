<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $data->id ? "Édition d'une" : "Nouvelle" }} entrée du Menu
        </h2>
    </x-slot>
    @php
        $error = $errors->any();
    @endphp
    <div class="py-4">

        <div class="max-w-7xl text-center mb-4">
            <a class="btn btn-sm btn-info"
               href="{{ route('panel.nav.index') }}">Retour à l'index</a>
        </div>

        <div class="max-w-7xl sm:px-6 lg:px-8">
            <div class="shadow p-3 mb-5 bg-body-tertiary rounded p-5">

                <x-mfw::response-messages/>
                <x-mfw::validation-errors/>

                @php
                    $error = $errors->any();
                @endphp


                <form method="post" action="{{ $route }}" enctype="multipart/form-data">


                    @csrf
                    @if($data->id)
                        @method('put')
                    @endif
                    <x-mfw::language-tabs/>
                    <div class="tab-content base">
                        @foreach(config('translatable.locales') as $locale)
                            <div class="tab-pane fade {!! $locale == app()->getLocale() ? 'show active': null !!}" id="tab_content_{{ $locale }}" role="tabpanel" aria-labelledby="tab_link_content_{{ $locale }}">
                                <fieldset>
                                    <div class="row mb-4 p-0">
                                        @foreach($data->fillables as $key=>$value)
                                            <div class="{{ $value['class'] ?? 'col-12' }} mb-4">
                                                <label for="{{$key .'_'.$locale}}" class="form-label">{{$value['label']}}</label>
                                                <input id="{{ $key .'_'.$locale }}" type="text" name="{{ $data->translatableInput($key) }}" value="{{ $data->translation($key, $locale) }}" class="form-control" {{ $key == 'url' ? ($data->id && $data->type =='custom' ? '' : 'disabled') : '' }}/>
                                            </div>
                                        @endforeach
                                    </div>
                                </fieldset>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-md-5">
                            <h4>Zone d'affectation</h4>
                            <x-mfw::select :values="$data->zones" name="zone" :affected="$data->zone" :nullable="false"/>
                        </div>
                        <div class="col-md-6 offset-md-1">
                            <h4>Type de lien</h4>
                            <div class="d-flex mb-5">
                                <div class="form-check form-check-inline d-flex">
                                    <input class="form-check-input" type="radio" name="nav-type" id="nav-type-linkable" value="linkable" {{ $data->id ? ($data->type == 'linkable' ? 'checked' : '') : 'checked' }}>
                                    <label class="form-check-label" for="nav-type-linkable">
                                        Un contenu parmi ceux enregistrés
                                    </label>
                                </div>
                                <div class="form-check form-check-inline d-flex">
                                    <input class="form-check-input" type="radio" name="nav-type" id="nav-type-custom" value="custom" {{ $data->id ? ($data->type == 'custom' ? 'checked' : '') : '' }}>
                                    <label class="form-check-label" for="nav-type-custom">
                                        Un lien sur mesure
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="row mt-4" id="linkables">
                        <div class="col-md-3 col-sm-4">
                            <h4>Pages spéciales</h4>
                            @foreach($selectables['custom'] as $value)
                                <div>
                                    <div class="form-check form-check-inline d-flex">
                                        <input class="form-check-input" type="radio" value="{{ $value['type'] }}" name="nav_entry" id="custom_{{ $loop->iteration }}"{{ $data->type == $value['type'] ? 'checked' :'' }}>
                                        <label class="form-check-label" for="custom_{{ $loop->iteration }}">
                                            {{ $value['title'] }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @foreach($selectables['database'] as $key => $collection)
                            <div class="col-md-3">
                                <h4>{{ __('meta.'.$key.'.label') }}</h4>
                                @foreach($collection as $value)
                                    <div class="mb-1 d-flex align-align-items-center">
                                        <div class="form-check form-check-inline d-flex">
                                            <input class="form-check-input" type="radio" value="{{ $value['id'] }}" name="nav_entry" id="{{ $key }}_{{ $loop->iteration }}" {{ $data->meta_id == $value['id'] ? 'checked' :'' }}/>
                                            <label class="form-check-label pb-2 mb-1" for="{{ $key }}_{{ $loop->iteration }}" style="border-bottom: 1px dashed #ccc">
                                                {{ $value->translation('title') }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    @if ($parent)
                        <div class="alert alert-info">
                            Cette entrée {{ (str_contains(request()->route()->getName(), 'edit') ? 'est enregistrée' : 'sera ajoutée') }} comme sous-menu de
                            <b>{{ $parent->title }}</b>
                        </div>
                        <input type="hidden" name="parent" value="{{ $parent->id }}">
                    @endif


                    <div class="mt-5 main-save">
                        <x-mfw::btn-save/>
                    </div>
                </form>

            </div>
        </div>
    </div>

    @push('js')
        <script>
            $(function () {
                $('#linkables :radio').off().click(function() {
                    $('#title_fr').val($.trim($(this).parent().find('label').text()));
                });
                $('#nav-type-custom').click(function () {
                    $('input[id^="url"]').prop('disabled', false);
                    $('#linkables :radio').prop('disabled', true);
                });
                $('#nav-type-linkable').click(function () {
                    $('input#url').prop('disabled', true);
                    $('#linkables :radio').prop('disabled', false);
                });
            });
        </script>
    @endpush
    @include('lib.tinymce')
</x-backend-layout>
