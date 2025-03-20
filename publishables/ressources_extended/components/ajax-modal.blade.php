<span
        data-bs-toggle="tooltip"
        @if(isset($tooltip))
            data-bs-title="{{ $tooltip }}"
        @endif
>
    <a href="#"
       class="{{ $class ?? "btn btn-sm btn-danger" }}"
       data-bs-toggle="modal"
       data-bs-target="#basic-modal"
    >
        {{ $text ?? "" }}
        {!! $icon ?? '<i class="' . ($iconClass??"fas fa-trash") . '"></i>'  !!}


    </a>
</span>
@pushonce('js')
    <div class="modal fade" id="basic-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{$title ?? "Attention"}}</h5>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="{{ __('mfw.close') }}"></button>
                </div>
                <div class="modal-body">{{$body ?? "Êtes-vous sûr(e) de vouloir effectuer cette action ?"}}</div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button"
                            class="btn btn-secondary btn-cancel"
                            data-bs-dismiss="modal">{{ $textCancel ?? "Annuler"  }}</button>
                    <button type="button"
                            data-bs-dismiss="modal"
                            class="btn btn-primary btn-confirm">{{ $textConfirm ?? "Confirmer" }}</button>
                </div>
            </div>
        </div>
    </div>
    <script>

      $(document).ready(function() {
        let jModal = $('#basic-modal');
        let jTrigger = null;

        jModal[0].addEventListener('show.bs.modal', function(e) {
          jTrigger = $(e.relatedTarget);
        });

        jModal.find('.btn-confirm').on('click', function(e) {
            @if(isset($onConfirm))
                    {{ $onConfirm }}(e, jTrigger);
            @endif
        });

      });
    </script>

@endpushonce