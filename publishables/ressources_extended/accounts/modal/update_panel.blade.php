@php
    use App\Enum\Civility;
    $isParticipant = (isset($isParticipant)) ? $isParticipant:false;
@endphp
<div class="modal fade"
     id="modal_update_panel"
     tabindex="-1"
     aria-labelledby="the_update_panel"
     aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-form" data-ajax="{{route('ajax')}}">

            <?php if ($isParticipant): ?>
            <input type="hidden" name="action" value="updateAccountProfilesByEventContacts">
            <?php else: ?>
            <input type="hidden" name="action" value="updateAccountProfiles">
            <?php endif; ?>

            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="the_update_panel">Modifier</h1>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="{{ __('ui.close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-6">

                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="mode"
                                       value="selection"
                                       id="update_target_selection"
                                       checked="">
                                <label class="form-check-label" for="update_target_selection">
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
                                       id="update_target_all">
                                <label class="form-check-label" for="update_target_all">
                                    Appliquer à tous les résultats
                                </label>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label for="fieldSelect" class="form-label">Élément à modifier</label>
                        <select class="form-select" name="key" id="fieldSelect">
                            <option value="">--- Sélectionnez ---</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="dynamicControl" class="form-label">Nouvelle valeur</label>
                        <div id="dynamicControlContainer">
                            <input type="text"
                                   name="value"
                                   class="form-control"
                                   id="dynamicControl">
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

      function redrawDataTable() {
        $('.dt').DataTable().ajax.reload();
      }

      const fieldData = {
        account_type: ['Type de compte', 'enum', {!! json_encode(\App\Enum\ClientType::translations()) !!}],
        base_id: ['Base', 'dico', 'base'],
        domain_id: ['Domaine', 'dico', 'domain'],
        title_id: ['Titre', 'dico', 'titles'],
        profession_id: ['Profession', 'dico_meta', 'professions'],
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

      $(document).ready(function() {

        //----------------------------------------
        // widget init
        //----------------------------------------
        initDynamicControlWidget({
          fieldData: fieldData,
          ajaxSelector: '#modal_update_panel .modal-form',
          defaultDatePickrOptions: {
            altInput: true,
            altFormat: '{{config('app.date_display_format')}}',
            time_24hr: true,
            dateFormat: "Y-m-d",
            locale: "{!! app()->getLocale() !!}",
          },
          defaultDatetimePickrOptions: {
            altInput: true,
            altFormat: '{{config('app.date_display_format')}} H:i:S',
            time_24hr: true,
            dateFormat: "Y-m-d H:i:S",
            locale: "{!! app()->getLocale() !!}",
          },
        });

        $('#modal_update_panel').handleMultipleSelectionModal({
          noSelectionMessage: 'Veuillez sélectionner au moins un contact',
          prevalidationHandler: function(oFormData) {
            if ('' === oFormData.key) {
              return 'Veuillez sélectionner l\'élément à modifier';
            }
          },
        });

      });


    </script>
@endpush

@pushonce('js')
    <script src="{{asset('js/bs-autocomplete.js')}}"></script>
    <script src="{{asset('js/dynamic-control-widget.js')}}"></script>
    <script src="{!! asset('vendor/mfw/flatpickr/flatpickr.js') !!}"></script>
    <script src="{!! asset('vendor/mfw/flatpickr/locale/'. app()->getLocale().'.js') !!}"></script>
    <script src="https://cdn.jsdelivr.net/gh/xcash/bootstrap-autocomplete@xcash-v300/dist/latest/bootstrap-autocomplete.min.js"></script>
    <script src="{{asset('js/handleMultipleSelectionModal.jquery.js')}}"></script>
@endpushonce




@push('css')
    <link rel="stylesheet" href="{!! asset('vendor/mfw/flatpickr/flatpickr.min.css') !!}" />
    <link rel="stylesheet"
          type="text/css"
          href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
@endpush
