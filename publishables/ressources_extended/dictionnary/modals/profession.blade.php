<style>
    #profession-modal {
        max-height: 500px;
        height: auto;
    }

    #mfwDynamicModal .modal-footer {
        display: none !important;
    }
</style>
<div class="overflow-auto" id="profession-modal">
    <div class="d-flex justify-content-center">
        <span class="btn btn-sm btn-success" id="add-group"><i class="fa-solid fa-circle-plus me-2"></i>Créer un nouveau groupe</span>
    </div>

    @if($entries)
        <ul class="list-group list-group-flush">
            @foreach($entries as $key => $group)
                <li data-id="{{ $key }}" class="group list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>{{ $group['name'] }}</span>
                        <span class="fs-4 add-dynamic add-entry"><i class="fa-solid fa-circle-plus"></i></span>
                    </div>
                    <ul class="entries">
                        @if ($group['values'])
                            @foreach($group['values'] as $subkey => $item)
                                <li data-id="{{ $subkey }}">{{ $item }}</li>
                            @endforeach
                        @endif
                    </ul>
                </li>

            @endforeach
        </ul>
    @else
        Aucune entrée n'est présente dans ce dictionnaire
    @endif

</div>

<script>
    function appendDymanicMetaGroup(result) {
        if (!result.hasOwnProperty('error')) {
            $('#profession-modal ul.list-group').append('<li data-id="' + result.entry.id + '" class="group list-group-item py-3">' +
                '<div class="d-flex justify-content-between align-items-center">' +
                '<span>' + result.term + '</span>' +
                '<span class="fs-4 add-dynamic add-entry"><i class="fa-solid fa-circle-plus"></i></span>' +
                '</div>' +
                '<ul class="entries"></ul></li>');
            pm.addEntry();
            $('#profile_profession_id').append('<optgroup data-id="' + result.entry.id + '" label="' + result.term + '"></optgroup>');
            setTimeout(() => pm.newGroup().remove(), 2000);
        }
    }

    function appendDymanicMetaEntry(result) {
        if (!result.hasOwnProperty('error')) {
            $('li.group[data-id=' + result.optgroup + ']').find('.entries').append('<li data-id="' + result.entry.id + '">' + result.term + '</li>');
            $('#profile_profession_id optgroup[data-id=' + result.entry.parent + ']').append('<option value="' + result.entry.id + '">' + result.term + '</option>').change();
            $('#profile_profession_id :selected').prop('selected', false).change();
            $('#profile_profession_id').find('option[value=' + result.entry.id + ']').prop('selected', true).change();
            setTimeout(() => pm.newEntry().remove(), 2000);
        }
    }

    var pm = {
        c: function () {
            return $('#profession-modal');
        },
        newEntry: function () {
            return this.c().find('#new-p-entry');
        },
        newGroup: function () {
            return this.c().find('#new-p-group');
        },
        addEntry: function () {
            this.c().find('.add-entry').off().click(function () {
                pm.newEntry().remove();
                pm.newGroup().remove();
                $(pm.template()).insertAfter($(this).parent());
                pm.saveEntry();
            });
        },
        saveEntry: function () {
            $('#new-p-entry span').off().click(function () {
                ajax('action=createProfession&optgroup=' + $(this).closest('li').data('id') + '&' +
                    pm.newEntry().find('input').serialize(), $('#new-p-entry'));
            });
        },
        addGroup: function () {
            $('#add-group').click(function () {
                pm.newGroup().remove();
                pm.newEntry().remove();
                $(pm.templateGroup()).insertAfter($(this).parent());
                pm.saveGroup();
            });
        },
        saveGroup: function () {
            $('#save-p-group').off().click(function () {
                ajax('action=createProfession&' + pm.newGroup().find('input').serialize(), $('#new-p-group'));
            });
        },
        template: function () {
            return $('#professions-inputs').html();
        },
        templateGroup: function () {
            return $('#professions-group').html();
        },
        init: function () {
            this.addEntry();
            this.addGroup();
        },
    };
    pm.init();
</script>
<template id="professions-inputs">
    <div class="mt-1" id="new-p-entry" data-ajax="{{ route('ajax') }}">
        @foreach(config('mfw.translatable.locales') as $locale)
            <x-mfw::input name="dynamic_profession[{{ $locale }}]" class="mb-2" :label="__('lang.'.$locale.'.label')"/>
        @endforeach
        <span class="btn btn-warning btn-sm text-nowrap float-end mb-2"><i class="fa-solid fa-check me-1"></i>{{ __('ui.save') }}</span>
    </div>
</template>

<template id="professions-group">
    <div class="mt-3 pb-4" id="new-p-group" data-ajax="{{ route('ajax') }}">
        <div class="w-100 me-1">
            @foreach(config('mfw.translatable.locales') as $locale)
                <x-mfw::input name="dynamic_profession_group[{{ $locale }}]" class="mb-2" :label="__('lang.'.$locale.'.label')"/>
            @endforeach
        </div>
        <div class="d-flex justify-content-center">
            <span class="btn btn-warning btn-sm text-nowrap mt-2" id="save-p-group"><i class="fa-solid fa-check me-2"></i>{{ __('ui.save') }}</span>
        </div>
    </div>
</template>
