<template id="template-dt-mass-delete">
    <div class="me-3 d-none" id="DT-container">
        <button id="datatable-delete-selected"
                class="btn btn-danger btn-sm"
                data-controller-path="{{ $controllerPath }}"
                data-model-path="{{ $modelPath }}"
                data-model="{{ $model }}"
                data-deleted-message="{{ $deletedMessage }}"
                data-name="{{ $name }}">Supprimer la sélection
        </button>
        <div class="modal fade"
             id="DT-DeleteModal"
             tabindex="-1"
             aria-labelledby="DT-DeleteModalLabel"
             aria-hidden="true">
            <form>
                @csrf
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="DT-DeleteModalLabel">Suppression de
                                lignes</h5>
                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="{{ __('ui.close') }}"></button>
                        </div>
                        <div class="modal-body"
                             data-ajax="{{ route('ajax') }}"
                             id="DT-DeleteModalBody">
                            {!! $question ?: $default_question !!}
                        </div>
                        <div class="modal-footer d-flex justify-content-between"
                             id="DT-DeleteModalFooter">
                            <button type="button"
                                    class="btn btn-secondary"
                                    data-bs-dismiss="modal"
                                    id="DT-DeleteModalCancel">Non, annuler
                            </button>
                            <button type="button" class="btn btn-danger" id="DT-DeleteModalSave">
                                <i class="fa-solid fa-trash"></i> Oui, supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
<template id="template-dt-mass-delete-messages">
    <div class="row">
        <div id="DT-messages" class="col" data-ajax="{{ route('ajax') }}"></div>
    </div>
</template>
@push('callbacks')
    <script>

      function DTManipulations() {
        let DTModal = new bootstrap.Modal(document.getElementById('DT-DeleteModal'));

        let DTSelectAll = $('#datatable-select-all'),
          DTC = $('#DT-container');

        $('.row-checkbox').off().on('click', function() {
          console.log('row-checkbox');
          $('.row-checkbox:checked').length ? DTC.removeClass('d-none') : DTC.addClass('d-none');
        });
        let DTDeleteBtn = $('#datatable-delete-selected');
        DTDeleteBtn.off();

        DTSelectAll.off().on('click', function() {
          let checked = $(this).is(':checked');
          checked ? DTC.removeClass('d-none') : DTC.addClass('d-none');
          $('.row-checkbox').prop('checked', checked);
        });

        // Handle the "Delete Selected" button click
        DTDeleteBtn.off().on('click', function() {
          let ids = [];

          $('.row-checkbox:checked').each(function() {
            ids.push($(this).val());
          });

          if (ids.length > 0) {
            DTModal.show();
            $('#DT-DeleteModalSave').off().on('click', function() {
              let paramString = 'action=datatableMassDelete&model=' + DTDeleteBtn.data('model') + '&name=' + DTDeleteBtn.data('name') + '&ids=' + ids + '&callback=DTRedraw';
              let controllerPath = DTDeleteBtn.data('controllerPath');
              if ('' !== controllerPath) {
                paramString += '&controller_path=' + controllerPath;
              }
              let modelPath = DTDeleteBtn.data('modelPath');
              if ('' !== modelPath) {
                paramString += '&model_path=' + modelPath;
              }
              let deletedMessage = DTDeleteBtn.data('deletedMessage');
              if ('' !== deletedMessage) {
                paramString += '&deleted_message=' + deletedMessage;
              }
              $.when(ajax(paramString, $('#DT-messages'))).then(DTModal.hide());

            });
          } else {
            alert('Aucune ligne n\'a été sélectionnée');
          }
        });
      }

      function DTRedraw() {
        $('.messages').not('#DT-messages .messages').html('');
        $('.dt').DataTable().ajax.reload();
        DTManipulations();
        DTclickableRow();
      }
    </script>
@endpush

@push('js')

    <script>

      setTimeout(function() {

        $('#{{ $normalized_model }}-table_wrapper .row:first > div:last-of-type').addClass('d-flex justify-content-end');
        $($('#template-dt-mass-delete').html()).insertBefore($('#{{ $normalized_model }}-table_filter'));
        $($('#template-dt-mass-delete-messages').html()).insertBefore($('#{{ $normalized_model }}-table_wrapper .dt-row'));

        DTManipulations();

      }, 1000);
    </script>
@endpush
