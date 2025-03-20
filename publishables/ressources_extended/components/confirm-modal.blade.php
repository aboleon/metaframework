@props([
    "id" => "confirmModal",
    "title" => "Attention",
    "text" => "Êtes-vous sûr(e) de vouloir effectuer cette action ?",
    "confirmBtnText" => "Confirmer",
])

<div class="modal fade"
     data-ajax="{{route("ajax")}}"
     id="{{$id}}"
     tabindex="-1"
     aria-labelledby="{{$id}}Label"
     aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 text-title" id="{{$id}}Label">{{$title}}</h1>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="{{ __('ui.close') }}"></button>
            </div>

            <div class="messages mt-3"></div>

            <div class="modal-body text-body">
                {{$text}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler
                </button>
                <button type="button" class="btn btn-primary action-confirm">
                    {{$confirmBtnText}}
                    <div style="display: none;"
                         class="spinner spinner-border spinner-border-sm"
                         role="status">
                        <span class="visually-hidden">{{ __('front/ui.loading') }}</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>
