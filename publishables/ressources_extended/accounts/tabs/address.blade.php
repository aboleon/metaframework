<div class="tab-pane fade"
     id="address-tabpane"
     role="tabpanel"
     aria-labelledby="address-tabpane-tab">
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
    <div class="mt-4">
        <h4 class="mt-4">Adresses</h4>
        <div class="row m-0">

            @if ($account->profile?->establishment)
                <div class="address-block col-md-4 mb-4 ps-0">
                    <div class="card">
                        <div class="card-body">
                            {!! \App\Printers\Account::address($account->profile?->establishment) !!}
                        </div>
                    </div>
                </div>
            @endif

            @forelse($account->address as $item)
                <div class="address-block col-md-4 mb-4 ps-0">
                    <div class="card">
                        <div class="card-body">
                            {!! \App\Printers\Account::address($item) !!}
                            <div class="mt-3 d-flex justify-content-between">
                                @php
                                    $editLinkRouteParams[1] = $item;
                                @endphp
                                <a class="fw-bold link-dark"
                                   href="{{ route('panel.accounts.addresses.edit', $editLinkRouteParams) }}">Modifier</a>
                                <a class="fw-bold link-danger"
                                   data-bs-toggle="modal"
                                   data-bs-target="#destroy_address_{{ $item->id }}"
                                   href="#">{{ __('ui.delete') }}</a>
                            </div>
                            @push('modals')
                                <x-mfw::modal :route="route('panel.accounts.addresses.destroy', [$account, $item])"
                                              reference="destroy_address_{{ $item->id }}" />
                            @endpush
                        </div>
                    </div>
                </div>
            @empty
                {!! wg_warning_notice("Aucune adresse saisie") !!}
            @endforelse
        </div>
        @if($account->id)
            <a href="{{route('panel.accounts.addresses.create', $addLinkRouteParams)}}"
               class="btn btn-sm rounded-1 btn-secondary">Ajouter une nouvelle adresse</a>
        @else
            <p class="text-dark fw-bold">Vous pourrez ajouter des adresses une fois le compte
                créé.</p>
        @endif
    </div>

    <div class="my-5">
        <h4 class="mt-3">Numéros de téléphone</h4>
        <div class="row m-0">
            @forelse($account->phones as $item)
                <div class="address-block col-md-4 mb-4 ps-0">
                    <div class="card">
                        <div class="card-body">
                            {!! \App\Printers\Account::phone($item) !!}

                            <div class="mt-3 d-flex justify-content-between">
                                @php
                                    $editLinkRouteParams[1] = $item;
                                @endphp
                                <a class="fw-bold link-dark"
                                   href="{{ route('panel.accounts.phone.edit', $editLinkRouteParams) }}">Modifier</a>
                                <a class="fw-bold link-danger"
                                   data-bs-toggle="modal"
                                   data-bs-target="#destroy_phone_{{ $item->id }}"
                                   href="#">{{ __('ui.delete') }}</a>
                            </div>
                            @push('modals')
                                <x-mfw::modal :route="route('panel.accounts.phone.destroy', [$account, $item])"
                                              reference="destroy_phone_{{ $item->id }}" />
                            @endpush
                        </div>
                    </div>
                </div>
            @empty
                {!! wg_warning_notice("Aucun numéro de téléphone n'est saisi") !!}
            @endforelse
        </div>

        @if($account->id)
            <a href="{{route('panel.accounts.phone.create', $addLinkRouteParams)}}"
               class="btn btn-sm rounded-1 btn-secondary">Ajouter un nouveau numéro</a>
        @else
            <p class="text-dark fw-bold">Vous pourrez ajouter des numéros de téléphone une fois le
                compte créé.</p>
        @endif
    </div>
    <div>
        <h4 class="mt-5">Adresses e-mail complémentaires</h4>
        <div class="row m-0">

            @forelse($account->mails as $item)
                <div class="address-block col-md-4 mb-4 ps-0">
                    <div class="card">
                        <div class="card-body">
                            <p>{{ $item->email }}</p>
                            <div class="mt-3 d-flex justify-content-between">
                                @php
                                    $editLinkRouteParams[1] = $item;
                                @endphp
                                <a class="fw-bold link-dark"
                                   href="{{ route('panel.accounts.mail.edit', $editLinkRouteParams) }}">Modifier</a>
                                <a class="fw-bold link-danger"
                                   data-bs-toggle="modal"
                                   data-bs-target="#destroy_mail_{{ $item->id }}"
                                   href="#">{{ __('ui.delete') }}</a>
                            </div>
                            @push('modals')
                                <x-mfw::modal :route="route('panel.accounts.mail.destroy', [$account, $item])"
                                              reference="destroy_mail_{{ $item->id }}" />
                            @endpush
                        </div>
                    </div>
                </div>
            @empty
                {!! wg_warning_notice("Aucune adresse complémentaire n'est saisie") !!}
            @endforelse
        </div>

        @if($account->id)
            <a href="{{ route('panel.accounts.mail.create', $addLinkRouteParams) }}"
               class="btn btn-sm rounded-1 btn-secondary">Ajouter une nouvelle adresse e-mail</a>
        @else
            <p class="text-dark fw-bold">Vous pourrez ajouter des adresses e-mail complémentares une
                fois le compte créé.</p>
        @endif
    </div>
</div>
