@push('js')
    <script>

      let modalOnSuccess = null;

      $(document).ready(function() {
        $('#mfwDynamicModal').off().on('show.bs.modal', function(event) {
          let button = $(event.relatedTarget),
            modalContentUrl = button.attr('data-modal-content-url'),
            modalOnSuccess = button.attr('data-modal-on-success'),
            body = $('#mfwDynamicModalBody');

          $.ajax({
            url: modalContentUrl,
            type: 'GET',
            success: function(response) {
              body.html(response.view);
              $('#mfwDynamicModalLabel').html(response.title);
              if (response.hasOwnProperty('action')) {
                let save = $('#mfwDynamicModalSave');
                if (response.hasOwnProperty('btn_cancel')) {
                  $('#mfwDynamicModalCancel').text(response.btn_cancel);
                }
                if (response.hasOwnProperty('btn_save')) {
                  save.text(response.btn_save);
                }
                if (response.action !== 'default') {
                  $('#mfwDynamicModalFooter').removeClass('d-none');
                }
                save.off().click(function(e) {
                  e.preventDefault();
                  ajax('action=' + response.action + '&' + $('#mfwDynamicModal').find('input,select,textarea').serialize(), body, {
                    successHandler: function(r) {
                      if (modalOnSuccess) {
                        if ('reload' === modalOnSuccess) {
                          setTimeout(function() {
                            window.location.reload();
                          }, 500);
                        }
                        else{
                            if (typeof window[modalOnSuccess] === 'function') {
                                window[modalOnSuccess](r);
                            }
                        }
                      }
                      return true;
                    },
                  });

                });
                if (response.hasOwnProperty('params')) {
                  for (let key in response.params) {
                    body.append('<input type="hidden" name="' + key + '" value="' + response.params[key] + '"/>');
                  }
                }
              }
            },
            error: function() {
              alert('Error loading modal content.');
            },
          });
        }).on('hide.bs.modal', function() {
          $('#mfwDynamicModalBody').html('');
          $('#mfwDynamicModalFooter').addClass('d-none');
          if ($('.gmapsbar').length) {
            delete google.maps;
            $.getScript("https://maps.googleapis.com/maps/api/js?key={{ config('mfw.google_places_api_key') }}&libraries=places&callback=initialize");
            $('.pac-container').remove();
          }
        });
      });
    </script>
    <div class="modal fade"
         id="mfwDynamicModal"
         tabindex="-1"
         aria-labelledby="mfwDynamicModalLabel"
         aria-hidden="true">
        <form>
            @csrf
            <div class="modal-dialog" style="width: 800px">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mfwDynamicModalLabel">Dynamic Modal</h5>
                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="{{ __('ui.close') }}"></button>
                    </div>
                    <div class="modal-body"
                         data-ajax="{{ route('ajax') }}"
                         id="mfwDynamicModalBody"></div>
                    <div class="modal-footer d-flex justify-content-between d-none"
                         id="mfwDynamicModalFooter">
                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal"
                                id="mfwDynamicModalCancel">{{ __('ui.cancel') }}</button>
                        <button type="submit" class="btn btn-warning" id="mfwDynamicModalSave">
                            <i class="fa-solid fa-check"></i> {{ __('ui.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endpush
