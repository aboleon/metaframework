<x-backend-layout>
    <x-slot name="header">
        @include('accounts.shared.subedit_header')
    </x-slot>


    @php
        $error = $errors->any();
    @endphp

    <div class="shadow p-3 mb-5 bg-body-tertiary rounded">
        <x-mfw::validation-banner />
        <x-mfw::response-messages />
        <div class="row m-3">
            <div class="col">
                <form method="post" action="{{ $route }}">
                    @if (isset($method))
                        @method($method)
                    @endif
                    @csrf

                    @if($redirect_to)
                        <input type="hidden" name="custom_redirect" value="{{ $redirect_to }}">
                    @endif


                    <fieldset class="position-relative">
                        <legend class="d-flex justify-content-between align-items-end">
                            <span>
                                {{ trans_choice('account.fidelity_card', 1) }}<span class="text-secondary"> | {{ $account->names() }}</span>
                           </span>
                            @php
                                $url = $redirect_to ?? route('panel.accounts.edit', $account);
                            @endphp
                            <x-mfw::notice message="<a href='{{ $url }}#cards-tabpane'>{{ trans_choice('account.fidelity_card', 2) }}</a>" />
                        </legend>
                        <div class="my-3">
                            <x-mfw::input name="cards[name]"
                                          :value="$error ? old('cards.name') : $data->name"
                                          :label="__('ui.document_title')" />
                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <x-mfw::input name="cards[serial]"
                                              :value="$error ? old('cards.serial') : $data->serial"
                                              :label="__('ui.number')" />
                            </div>

                            <div class="col-md-6">
                                <x-mfw::datepicker :label="__('ui.expires_at')"
                                                   name="cards[expires_at]"
                                                   :value="$error ? old('cards.expires_at') : $data->expires_at?->format('d/m/Y')" />
                            </div>
                        </div>
                    </fieldset>
                    <div class="mt-5">
                        <x-mfw::btn-save />
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-backend-layout>
