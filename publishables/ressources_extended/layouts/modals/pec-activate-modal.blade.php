@push("modals")
    <div class="modal fade"
         id="pec-activate-modal"
         data-ajax="{{route('ajax')}}"
         tabindex="-1"
         aria-labelledby="pec-activate-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="pec-activate-modalLabel">Prise en charge</h1>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="{{ __('ui.close') }}"></button>
                </div>
                <div class="modal-body">
                    Avant de continuer, merci de bien vouloir régler une caution d'un montant
                    de {{ $pendindDeposit->total_net + $pendindDeposit->total_vat }} €.
                    <br>
                </div>
                <div class="messages"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        Annuler et continuer mes achats sans grant
                    </button>
                    <button type="button" class="btn btn-primary btn-sm action-load-pec-waiver">Payer la caution et
                        continuer avec un grant
                        <div class="ajax-spinner spinner-border spinner-border-sm" style="display: none" role="status">
                            <span class="visually-hidden">{{ __('front/ui.loading') }}</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push("js")
    <script>
        $(document).ready(function () {
            const jModal = $('#pec-activate-modal');
            jModal.find('.action-load-pec-waiver').on('click', function () {
                let action = "action=loadPecWaiverInCart&event_contact_id={{$eventContact->id}}";
                ajax(action, jModal, {
                    successHandler: function () {
                        jModal.modal('hide');
                        location.href = "{{route('front.event.cart.edit', [
                    'event' => $event->id,
                ])}}";
            },
          });
        });

      });
    </script>
@endpush
