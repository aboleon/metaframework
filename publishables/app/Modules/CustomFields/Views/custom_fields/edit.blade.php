<x-backend-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title }}
        </h2>
        <div class="d-flex align-items-center" id="topbar-actions">
            @if ($data->id && array_key_exists($data->model_type, config('custom_fields.routes')))
                <a href="{{ route(config('custom_fields.routes.'.$data->model_type), $data->model_id) }}"
                   class="btn btn-sm btn-warning"
                   style="color: black">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            @endif
        </div>
    </x-slot>
    @push('css')
        {!! csscrush_inline(app_path('Modules/CustomFields/style.css')) !!}
    @endpush

    <div class="shadow p-3 mb-5 bg-body-tertiary rounded">
        <form method="post" action="{{ $route }}">

            @csrf
            @if($data->id)
                @method('put')
            @else
                <input type="hidden" name="model_type" value="{{ $model }}"/>
                <input type="hidden" name="model_id" value="{{ $model_id }}"/>
            @endif
                <div id="configurator-options" class="d-flex justify-content-center">
                    @php
                        $button_types = config('custom_fields.modules');
                    @endphp
                    @foreach($button_types as $k => $button)
                        <button data-target="{{ $k }}" data-subtypes="{!! htmlspecialchars(json_encode($button['type']), ENT_QUOTES, 'UTF-8') !!}" class="btn-secondary btn btn-sm mx-2"><i class="fa-solid fa-plus"></i> {{ $button['label'] }}</button>
                    @endforeach
                </div>

            <div class="mfw-line-separator mb-5"></div>

            <div id="modules">

                @if ($data->modules->isNotEmpty())
                    @foreach($data->modules->sortBy('position') as $module)
                        <div class="module-bloc" data-key="{{ $module->key }}">
                            <input class="order" type="hidden" name="{{ $module->key }}[position]" value="{{ $module->position }}"/>
                            <h6 class="btn-sm btn {{ config('custom_fields.modules.'.$module->type.'.class') }}">{{ config('custom_fields.modules.'.$module->type.'.label') }}</h6>
                            <button type="button" class="not-draggable delete-module btn btn-sm btn-danger">Supprimer</button>
                            <input type="hidden" name="key[]" class="key" value="{{ $module->key }}"/>
                            <div class="header not-draggable ">
                                <div class="row mb-3">
                                    <div class="col-sm-8">
                                        <label class="form-label">Titre du bloc</label>
                                        <input class="form-control title-bloc" type="text" name="{{ $module->key }}[title]" value="{{ $module->title }}" required/>
                                    </div>
                                </div>
                                @php
                                @endphp
                                <div class="row">
                                    <div class="col-sm-8">
                                        <x-mfw::select name="{{ $module->key }}[subtype]" :values="$button_types[$module->type]['type']" :affected="$module->subtype" label="Type"/>
                                    </div>
                                </div>
                            </div>
                            <div class="not-draggable section">
                                @if($module->type == 'selection')
                                    <input type="hidden" name="{{ $module->key }}[type]" class="type" value="{{ $module->type }}"/>
                                    <b class="d-block my-3">Éléments</b>

                                    @foreach($module->data as $line)
                                        <div class="row mb-3">
                                            <div class="col-sm-8 line">
                                                <i class="fa fa-minus-square text-danger remove"></i>
                                                <x-mfw::input :value="$line->content" name="{{ $module->key }}[line][{{ $line->key }}]" />
                                            </div>
                                        </div>
                                    @endforeach
                                    <button type="button" class="btn-xs add btn-success btn">Ajouter</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <x-mfw::btn-save/>

        </form>

        <template id="configurator-inputs">
            <input type="hidden" name="key[]" class="key"/>
            <input type="hidden" name="new_id[position]" class="order"/>
            <div class="not-draggable header">
                <div class="row">
                    <div class="col-sm-8">
                        <label class="form-label">Titre du bloc</label>
                        <input class="form-control title-bloc" type="text" name="new_id[title]"/>
                    </div>
                </div>
                <div class="row my-3">
                    <div class="col-sm-8">
                        <label class="form-label">Type</label>
                        <select class="form-control form-select module-subtype" name="new_id[subtype]"></select>
                    </div>
                </div>
            </div>
        </template>

        <!-- BOXES -->
        <template class="selection">
            <input type="hidden" name="new_id[type]" class="type"/>
            <strong>Choix</strong>
            <div class="row mb-3">
                <div class="col-sm-8 line">
                    <i class="fa fa-minus-square text-danger remove"></i>
                    <input class="form-control" type="text"/>
                </div>
            </div>
            <button type="button" class="btn-xs add btn-success btn">Ajouter</button>
        </template>

    </div>
    @push('js')
        <script>
            var a = {
                modules: function () {
                    return $('#modules');
                },
                container: $('#configurator'),
                buttons: $('#configurator-options button'),
                buttonSave: $('#configurator-save'),
                sortables: function () {
                    this.modules().sortable({
                        axis: 'y',
                        cancel: '.not-draggable',
                        cursor: 'move',
                        stop: function (event, ui) {
                            a.sortModules();
                        },
                    });
                },
                sortModules: function () {
                    this.modules().find('input.order').each(function (index) {
                        $(this).val(index + 1);
                    });
                },
                addModule: function (selected) {

                    let selector = selected.attr('data-target'),
                        title = selected.text(),
                        cssclass = selected.attr('class'),
                        subtypes = selected.attr('data-subtypes'),
                        content = $('template.' + selector),
                        html = '<div class="module-bloc"><h6 class="' + cssclass + '">' + title + '</h6><button type="button" class="not-draggable delete-module btn btn-sm btn-danger">Supprimer</button>' + $($('#configurator-inputs')).html();
                    if (content.length) {
                        html = html.concat('<div class="not-draggable section">' + $(content).html() + '</div>');
                    }
                    html = html.concat('</div>');
                    this.modules().append(html);

                    let lastbloc = this.modules().find('.module-bloc').last();
                    a.attributeUpdater(lastbloc, selector);

                    let subtypeOptions = '',
                        parsedSubtypes = JSON.parse(subtypes);

                    for (const [key, value] of Object.entries(parsedSubtypes)) {
                        subtypeOptions = subtypeOptions.concat('<option value="' + key + '">' + value + '</option>');
                    }


                    lastbloc.find('select.module-subtype').append(subtypeOptions);
                    this.addRow();
                    this.delete();
                    this.sortModules();
                },
                attributeUpdater: function (target, selector) {
                    let new_id = guid(16);
                    a.updateFormTags(target, new_id, selector);
                    target.attr('data-key', new_id);
                },
                updateFormTags: function (target, new_id, selector) {
                    target.find('.key').val(new_id);
                    target.find('textarea, input, select').not('.key').each(function () {
                        if ($(this).attr('name') !== undefined) {
                            $(this).attr('name', $(this).attr('name').replace('new_id', new_id));
                        }
                        let input = target.find('.line input');
                        if (input.length) {
                            input.attr('name', new_id + '[line][' + guid(8) + ']');
                        }
                        target.find('input.type').val(selector);
                        if ($(this).attr('id') !== undefined) {
                            $(this).attr('id', $(this).attr('id') + '_' + new_id);
                        }
                    });
                },
                buttonActions: function () {
                    this.buttons.click(function (e) {
                        e.preventDefault();
                        a.addModule($(this));

                    });
                    this.buttonSave.find('button.cancel').click(function (e) {
                        e.preventDefault();
                        $(this).parent().addClass('d-none');
                        $('.box:visible').addClass('d-none');
                        a.buttons.prop('disabled', false);
                    });
                },
                addRow: function () {
                    $('button.add').off().click(function () {

                        $($(this).prev('.row').clone()).insertBefore(this);
                        $(this).prev('.row').find('input').val('');
                        a.removeRow();

                        let module = $(this).closest('.module-bloc'),
                            input = module.find('.line').last().find('input');

                        input.attr('name', module.attr('data-key') + '[line][' + guid(8) + ']');
                    });
                },
                removeRow: function () {
                    $('i.remove').off().on('click', function () {
                        if ($(this).parents('.module-bloc').find('.line').length > 1) {
                            $(this).closest('.row').remove();
                        }
                    });
                },
                delete: function () {
                    $('.delete-module').off().on('click', function (e) {
                        e.preventDefault();
                        $(this).closest('.module-bloc').remove();
                    });
                },
                init: function () {
                    this.addRow();
                    this.buttonActions();
                    this.removeRow();
                    this.delete();
                    this.sortModules();
                    setTimeout(function () {
                        a.sortables();
                    }, 1000);
                },
            };

            a.init();
        </script>

        <link rel="stylesheet" href="{{ asset('vendor/jquery-ui-1.13.0.custom/jquery-ui.css') }}"/>
        <script src="{{ asset('vendor/jquery-ui-1.13.0.custom/jquery-ui.min.js') }}"></script>
    @endpush
</x-backend-layout>
