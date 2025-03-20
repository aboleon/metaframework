<div class="tab-pane fade" id="passport-tabpane" role="tabpanel" aria-labelledby="passport-tabpane-tab">
    <div class="mt-4">
        @php
            $addLinkRouteParams = [
                $account,
            ];
            $editLinkRouteParams = [
                $account,
                null, // item
            ];

            if(isset($redirect_to)){
                $addLinkRouteParams['redirect_to'] = $redirect_to;
                $editLinkRouteParams['redirect_to'] = $redirect_to;
            }

        @endphp
        <div class="row m-0">
            @forelse($account->documents as $item)
                <div class="address-block col-md-4 mb-4 ps-0">
                    <div class="card">
                        <div class="card-body">
                            {!! \App\Printers\Account::document($item) !!}
                            <div class="mt-3 d-flex justify-content-between">
                                @php
                                    $editLinkRouteParams[1] = $item;
                                @endphp
                                <a class="fw-bold link-dark" href="{{ route('panel.accounts.documents.edit', $editLinkRouteParams) }}">Modifier</a>
                                <a class="fw-bold link-danger" data-bs-toggle="modal" data-bs-target="#destroy_document{{ $item->id }}" href="#">{{ __('ui.delete') }}</a>
                            </div>
                            @push('modals')
                                <x-mfw::modal :route="route('panel.accounts.documents.destroy', [$account, $item])" reference="destroy_document{{ $item->id }}"/>
                            @endpush
                        </div>
                    </div>
                </div>
            @empty
                {!! wg_warning_notice("Aucune pièce saisie") !!}
            @endforelse
        </div>
        @if($account->id)

            <a href="{{route('panel.accounts.documents.create', $addLinkRouteParams)}}" class="btn btn-sm rounded-1 btn-secondary">Ajouter une nouvelle pièce</a>
        @else
            <p class="text-dark fw-bold">Vous pourrez ajouter des pièces d'identité une fois le compte créé.</p>
        @endif
    </div>

</div>
