@php use App\Accessors\ParticipationTypes;use App\Enum\ParticipantType; @endphp
<div class="modal fade"
     id="modal_add_eventcontact_panel"
     tabindex="-1"
     aria-labelledby="modal_add_eventcontact_panel_label"
     aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-form" data-ajax="{{route('ajax')}}">


            <div class="modal-content">
                <div class="modal-header d-flex align-items-end">
                    <h1 class="modal-title fs-5" id="modal_add_eventcontact_panel_label">
                        Ajouter / Créer</h1>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="{{ __('ui.close') }}"></button>
                </div>
                <div class="modal-body">

                    <div class="alert alert-danger select-participation-type-error"
                         style="display: none">
                        Veuillez sélectionner un utilisateur.
                        <br>
                        Si vous ne trouvez pas l'utilisateur,
                        cliquez sur Créer.
                    </div>

                    <div class="row align-items-center">
                        <div class="col-4">
                            <label class="form-label" for="maep_participation_type_group">Type de
                                participation</label>
                        </div>
                        <div class="col-8">
                            <x-select-participation-type
                                id="maep_participation_type_group"
                                class="form-select"
                                name="participation_type_id"
                                :event="$event"
                                :groupType="$groupType"

                            />
                        </div>
                    </div>


                    <div class="mt-4">
                        <select
                            name="user_id"
                            id="user_id_select">
                        </select>
                    </div>


                </div>

                <div class="messages m-3"></div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('ui.close') }}
                    </button>
                    <button type="button" class="btn btn-success btn-submit">Créer</button>
                    <button type="button" class="btn btn-default btn-add-participant">Ajouter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>




@pushonce('js')
    <script src="{!! asset('js/select2AjaxWrapper.js') !!}"></script>
    <script src="{!! asset('vendor/select2/js/select2.full.min.js') !!}"></script>
    <script src="{!! asset('vendor/select2/js/i18n/fr.js') !!}"></script>
@endpushonce

@pushonce('css')
    <link rel="stylesheet" href="{!! asset('vendor/select2/css/select2.min.css') !!}">
@endpushonce


@push('js')
    <script>

        const jModal = $('#modal_add_eventcontact_panel');
        const jForm = jModal.find('form');
        const jUserSelect = jForm.find('#user_id_select');
        const jTypeParticipationSelect = jForm.find('#maep_participation_type_group');

        $('#user_id_select').select2AjaxWrapper({
            placeholder: 'Sélectionner un utilisateur',
            language: 'fr',
            // necessary to allow input focus inside a modal
            dropdownParent: jModal,
            ajax: {
                url: "{{ route('ajax') }}",
                delay: 100,
                dataType: 'json',
                data: function (params) {
                    return {
                        q: params.term,
                        action: 'select2Accounts',
                    };
                },
            },
        });

        $('#user_id_select').on('select2:select', function (e) {
            jModal.find('.select-participation-type-error').hide();
        });

        jForm.find('.btn-add-participant').on('click', function () {
            let participantId = jUserSelect.val();
            if (!participantId) {
                jModal.find('.select-participation-type-error').show();
            } else {
                let formData = jForm.serialize();
                ajax('action=associateUserToEvent&' + formData + '&event_id={{$event->id}}', jForm, {
                    successHandler: function () {
                        redrawDataTable();
                        return true;
                    },
                });
            }

            return false;
        });

        jForm.find('.btn-submit').on('click', function () {

            let route = "{!! route('panel.accounts.create', [
            'event' => $event,
            'participation_type_id' => 'xxx',
            'role' => 'all',
        ]) !!}";

            let participantType = jTypeParticipationSelect.val();
            if ('' === participantType) {
                participantType = 0;
            }
            route = route.replace('xxx', participantType);
            window.location.href = route;
            return false;
        });


    </script>
@endpush
