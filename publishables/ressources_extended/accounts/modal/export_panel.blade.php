@php
    if(false === isset($exportAction)) {
        $exportAction = "exportAccountProfiles";
    }
@endphp
@php use App\Actions\Account\ExportAccountProfilesAction; @endphp
<div class="modal fade"
     id="modal_export_panel"
     tabindex="-1"
     aria-labelledby="the_export_panel"
     aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-form" data-ajax="{{route('ajax')}}">
            <input type="hidden" name="action" value="{{ $exportAction }}">
            <input type="hidden" name="mode" value="selection">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="the_export_panel">Exporter</h1>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="{{ __('ui.close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Sélectionnez les champs à exporter:</p>

                    @php
                        $fieldsMapping = ExportAccountProfilesAction::$fieldsMapping;
                        $groupedFieldsMapping = [];

                        $prefixMappings = [
                            'establishment_' => 'Établissement',
                            'main_address_' => 'Adresse principale',
                            'main_phone' => 'Téléphone principal',
                        ];

                        foreach ($fieldsMapping as $fieldKey => $fieldLabel) {
                            $group = 'Informations de base';

                            foreach ($prefixMappings as $prefix => $prefixGroup) {
                                if (0 === strpos($fieldKey, $prefix)) {
                                     $group = $prefixGroup;
                                     break;
                                }
                            }

                            if (!isset($groupedFieldsMapping[$group])) {
                            $groupedFieldsMapping[$group] = [];
                            }
                            $groupedFieldsMapping[$group][$fieldKey] = $fieldLabel;
                        }
                    @endphp

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="exportSelectAll">
                        <label class="form-check-label small" for="exportSelectAll">Tout
                            sélectionner</label>
                    </div>
                    <hr>

                    @foreach($groupedFieldsMapping as $group => $fields)
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ $group }}</span>
                                <input class="form-check-input group-toggle"
                                       type="checkbox"
                                       id="{{ Str::slug($group) }}"
                                       data-group="{{ Str::slug($group) }}">
                            </div>
                            <hr>
                            @foreach($fields as $fieldKey => $fieldLabel)
                                <div class="form-check mb-2 field-item"
                                     data-group="{{ Str::slug($group) }}">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="{{ $fieldKey }}"
                                           name="exportFields[]"
                                           value="{{ $fieldKey }}">
                                    <label class="form-check-label small"
                                           for="{{ $fieldKey }}">{{ $fieldLabel }}</label>
                                </div>
                            @endforeach
                            <hr>
                        </div>
                    @endforeach


                </div>


                <div class="messages m-3"></div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('ui.close') }}
                    </button>
                    <button type="button" class="btn btn-primary submit-btn">
                        <div class="spinner-border spinner-border-sm spinner-element"
                             role="status"
                             style="display: none;">
                            <span class="visually-hidden">{{ __('front/ui.loading') }}</span>
                        </div>
                        Exporter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


@push('js')

    <script>
      $(document).ready(function() {

        //----------------------------------------
        // export
        //----------------------------------------
        $('#exportSelectAll').on('change', function() {
          var isChecked = $(this).prop('checked');
          $('input[name="exportFields[]"]').prop('checked', isChecked);
        });

        $('input[name="exportFields[]"]').on('change', function() {
          if ($('input[name="exportFields[]"]:checked').length !== $('input[name="exportFields[]"]').length) {
            $('#exportSelectAll').prop('checked', false);
          } else {
            $('#exportSelectAll').prop('checked', true);
          }
        });

        $('.group-toggle').on('change', function() {
          var isChecked = $(this).prop('checked');
          var group = $(this).data('group');
          $('.field-item[data-group="' + group + '"] input').prop('checked', isChecked);
        });

        $('input[name="exportFields[]"]').on('change', function() {
          var group = $(this).closest('.field-item').data('group');
          var allChecked = $('.field-item[data-group="' + group + '"] input:checked').length === $('.field-item[data-group="' + group + '"] input').length;
          $('#' + group).prop('checked', allChecked);
        });

        //----------------------------------------
        // modal
        //----------------------------------------
        $('#modal_export_panel').handleMultipleSelectionModal({
          noSelectionMessage: 'Veuillez sélectionner au moins un contact',
          prevalidationHandler: function(oFormData) {
            if (!oFormData.exportFields) {
              return 'Veuillez sélectionner au moins un champ à exporter';
            }
          },
          success: function(result) {
            if (result.hasOwnProperty('file')) {
              var blob = new Blob([new Uint8Array(atob(result.file).split('').map(function(c) {
                return c.charCodeAt(0);
              }))], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});

              var link = document.createElement('a');
              link.href = URL.createObjectURL(blob);
              link.download = result.filename;
              document.body.appendChild(link);
              link.click();
              document.body.removeChild(link);
            }
            return true;
          },
        });

      });
    </script>
@endpush



@pushonce('js')
    <script src="{{asset('js/handleMultipleSelectionModal.jquery.js')}}"></script>
@endpushonce
