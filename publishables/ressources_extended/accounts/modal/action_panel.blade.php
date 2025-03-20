@php
    use App\Accessors\GroupAccessor;
    use App\Enum\Civility;

    $isEdit = false;
    $ids = [];

    if(isset($account)){
        $isEdit = true;
        $ids[] = $account->id;
    }


    $isParticipant = isset($isParticipant) && $isParticipant;
    $associateUsersToEvent = "associateUsersToEvent";
    $updateAccountProfile = "updateAccountProfiles";
    if($isParticipant){
        $associateUsersToEvent .= "ByEventContact";
        $updateAccountProfile .= "ByEventContacts";
    }

@endphp
<div class="modal fade"
     id="modal_actions_panel"
     tabindex="-1"
     aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-form" data-ajax="{{route('ajax')}}">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-end">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Actions</h1>
                    <div class="spinner-element ms-3" style="display: none;">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">{{ __('front/ui.loading') }}</span>
                            </div>
                        </div>
                    </div>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="{{ __('ui.close') }}"></button>
                </div>
                <div class="modal-body">

                    @if(!$isEdit)
                        @if (isset($event_id))
                            <input type="hidden" name="event_id" value="{{ $event_id }}"/>
                        @endif
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="mode"
                                           value="selection"
                                           id="target_selection"
                                           checked>
                                    <label class="form-check-label" for="target_selection">
                                        Appliquer à la sélection
                                    </label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="mode"
                                           value="all"
                                           id="target_all">
                                    <label class="form-check-label" for="target_all">
                                        Appliquer à tous les résultats
                                    </label>
                                </div>
                            </div>
                        </div>
                        <hr>
                    @endif
                    <div class="action-container mb-3">
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="action"
                                           value="{{ $associateUsersToEvent }}"
                                           id="flexRadioDefault1"
                                           checked>
                                    <label class="form-check-label" for="flexRadioDefault1">
                                        Affecter à un événement
                                    </label>
                                </div>
                            </div>
                            <div class="col-6">
                                <select class="form-select mb-3"
                                        name="associateUsersToEvent[event_id]">
                                    <option selected value="">Choix événement</option>
                                    @foreach(App\Accessors\EventAccessor::eventsArray() as $id => $label)
                                        <option value="{{$id}}">{{$label}}</option>
                                    @endforeach
                                </select>
                                <div id="modal_select_participation_types">
                                    <x-select-participation-type
                                            name="associateUsersToEvent[participation_type_id]"
                                            class="form-select"
                                            :selected="null"
                                    />
                                </div>

                            </div>
                        </div>
                        @if ($isParticipant)
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input"
                                               type="radio"
                                               name="action"
                                               value="associateEventContactsToEventGroup"
                                               id="associateEventContactsToEventGroup"
                                        >
                                        <label class="form-check-label d-inline"
                                               for="associateEventContactsToEventGroup">
                                            Affecter à un groupe
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <select class="form-select mb-3"
                                            name="event_group_id">
                                        <option selected value="">Veuillez choisir</option>
                                        @foreach(\App\Accessors\EventManager\EventGroups::getEventGroupsSelectableByEvent($event) as $id => $label)
                                            <option value="{{$id}}">{{$label}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="action"
                                           value="{{ $updateAccountProfile }}"
                                           id="updateAccountProfiles"
                                    >
                                    <label class="form-check-label d-inline"
                                           for="updateAccountProfiles">
                                        Modifier
                                    </label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label for="fieldSelect" class="form-label">Élément à
                                        modifier</label>
                                    <select class="form-select" name="key" id="fieldSelect">
                                        <option value="">--- Sélectionnez ---</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="dynamicControl" class="form-label">Nouvelle
                                        valeur</label>
                                    <div id="dynamicControlContainer">
                                        <input type="text"
                                               name="value"
                                               class="form-control"
                                               id="dynamicControl">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="messages m-3"></div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('ui.close') }}
                    </button>
                    <button type="button" class="btn btn-primary submit-btn">Exécuter</button>
                </div>
            </div>
        </form>
    </div>
</div>


@push('js')
    <script>
        $(document).ready(function () {

            $('#modal_actions_panel').handleMultipleSelectionModal({
                ids: {{ Js::from($ids) }},
                noSelectionMessage: 'Veuillez sélectionner au moins un contact',
                prevalidationHandler: function (oFormData) {
                    switch (oFormData.action) {
                        case 'associateUsersToEvent':
                            if (oFormData['associateUsersToEvent[event_id]'] === '') {
                                return 'Veuillez sélectionner un événement';
                            }
                            break;
                        case 'associateUsersToGroupByEventContact':
                            if (oFormData['associateUsersToGroupByEventContact[group_id]'] === '') {
                                return 'Veuillez sélectionner un groupe';
                            }
                            break;
                    }
                },
            });
        });
    </script>

@endpush

@pushonce('js')
    <script src="{{asset('js/handleMultipleSelectionModal.jquery.js')}}"></script>
    <script src="{{asset('js/bs-autocomplete.js')}}"></script>
    <script src="{{asset('js/dynamic-control-widget.js')}}"></script>
    <script src="{!! asset('vendor/mfw/flatpickr/flatpickr.js') !!}"></script>
    <script src="{!! asset('vendor/mfw/flatpickr/locale/'. app()->getLocale().'.js') !!}"></script>
    <script
            src="https://cdn.jsdelivr.net/gh/xcash/bootstrap-autocomplete@xcash-v300/dist/latest/bootstrap-autocomplete.min.js"></script>
    <script src="{{asset('js/handleMultipleSelectionModal.jquery.js')}}"></script>
@endpushonce

@push('js')

    <script>
        //----------------------------------------
        // update widget
        //----------------------------------------

        function redrawDataTable() {
            let jDt = $('.dt');
            if (jDt.length) {
                jDt.DataTable().ajax.reload();
            }

        }

        const fieldData = {
            account_type: ['Type de compte', 'enum', {!! json_encode(\App\Enum\ClientType::translations()) !!}],
            base_id: ['Base', 'dico', 'base'],
            domain_id: ['Domaine', 'dico', 'domain'],
            title_id: ['Titre', 'dico', 'titles'],
            profession_id: ['Profession', 'dico_meta', 'professions'],
            @if(isset($event_id))
            participation_type_id: ['Type de participation', 'custom', 'getParticipationTypes'],
            @endif
            language_id: ['Language', 'dico', 'language'],
            savant_society_id: ['Société savante', 'dico', 'savant_societies'],
            civ: ['Civilité', 'enum', {!! json_encode(\App\Enum\Civility::toArray()) !!}],
            birth: ['Date de naissance', 'date'],
            cotisation_year: ['Année de cotisation', 'year', '1900', '{{date('Y')}}'],
            blacklisted: ['Blackliste', 'nullable_datetime', 'Non blacklisté', 'Date de blacklistage'],
            created_by: ['Créé par', 'search', 'searchUsers'],
            blacklist_comment: ['Commentaire blackliste', 'text'],
            notes: ['Notes', 'text'],
            function: ['Fonction'],
            passport_first_name: ['Prénom passeport'],
            passport_last_name: ['Nom passeport'],
            rpps: ['Rpps'],
            establishment_id: ['Établissement', 'fk', 'getEstablishments'],
            company_name: ['Nom de la société'],
        };

        $(document).ready(function () {

            //----------------------------------------
            // widget init
            //----------------------------------------
            initDynamicControlWidget({
                fieldData: fieldData,
                ajaxSelector: '#modal_actions_panel .modal-form',
                defaultDatePickrOptions: {
                    altInput: true,
                    altFormat: '{{config('app.date_display_format')}}',
                    time_24hr: true,
                    dateFormat: 'Y-m-d',
                    locale: "{!! app()->getLocale() !!}",
                },
                defaultDatetimePickrOptions: {
                    altInput: true,
                    altFormat: '{{config('app.date_display_format')}} H:i:S',
                    time_24hr: true,
                    dateFormat: 'Y-m-d H:i:S',
                    locale: "{!! app()->getLocale() !!}",
                },
            });

            $('#modal_actions_panel').handleMultipleSelectionModal({
                noSelectionMessage: 'Veuillez sélectionner au moins un contact',
                prevalidationHandler: function (oFormData) {
                    if ('' === oFormData.key) {
                        return 'Veuillez sélectionner l\'élément à modifier';
                    }
                },
            });

        });


    </script>
@endpush
@push('css')
    <link rel="stylesheet" href="{!! asset('vendor/mfw/flatpickr/flatpickr.min.css') !!}"/>
    <link rel="stylesheet"
          type="text/css"
          href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
@endpush
